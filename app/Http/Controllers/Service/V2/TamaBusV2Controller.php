<?php

namespace App\Http\Controllers\Service\V2;

use app\Library\AppHelper;
use app\Library\ServiceHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Service\V2\BlaCheckoutRequest;
use App\Http\Requests\Service\V2\FlixCheckoutRequest;
use App\Models\AppCommission;
use App\Models\Country;
use App\Models\Order;
use App\Support\BusV2ApiClient;
use App\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TamaBusV2Controller extends Controller
{
    private $service_id = 9;
    private $client;
    private $busV2Api;
    private $countriesCache;

    public function __construct()
    {
        parent::__construct();

        $this->middleware(function ($request, $next) {
            if (API_TOKEN == '' || API_END_POINT == '') {
                AppHelper::logger('warning', 'API SETTINGS ERROR', 'Missing API Token or API end point url', request()->all(), true);
                return redirect()->back()
                    ->with('message', trans('common.access_violation'))
                    ->with('message_type', 'warning');
            }

            if (AppHelper::user_access($this->service_id, auth()->user()->id) == 0) {
                AppHelper::logger('warning', 'Access Violation', auth()->user()->username . ' trying to access Flixbus service', request()->all(), true);
                return redirect()->back()
                    ->with('message', trans('common.access_violation'))
                    ->with('message_type', 'warning');
            }

            if (\app\Library\AppHelper::skip_service_as_menu('flix-bus') == false) {
                AppHelper::logger('warning', 'Access Violation', auth()->user()->username . ' trying to access Flixbus service but parent of this user does not have a access', request()->all());
                return redirect('dashboard')
                    ->with('message', trans('common.access_violation'))
                    ->with('message_type', 'warning');
            }

            $this->client = new Client([
                'base_uri' => API_END_POINT,
                'timeout' => 120,
            ]);
            $this->busV2Api = new BusV2ApiClient($this->client, API_TOKEN);

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        try {
            if ($this->requestBoolean($request, 'restart')) {
                $this->clearState();
            }

            $this->ensureBookingSessionInitialized();

            return view($this->busView('index'), array_merge(
                $this->stateData(),
                [
                    'page_title' => __('bus.page_title'),
                    'cities' => $this->fetchCities(),
                    'countries' => $this->getCountries(),
                    'busV2Design' => $this->busDesign(),
                ]
            ));
        } catch (\Exception $e) {
            AppHelper::logger('warning', 'Flix bus API HTTP Exception', $e->getMessage(), $e, true);
            return redirect('dashboard')
                ->with('message', trans('common.access_violation'))
                ->with('message_type', 'warning');
        }
    }

    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trip_type' => 'nullable|in:one_way,round_trip',
            'cityFrom' => 'required|string',
            'cityFromHid' => 'required',
            'cityTo' => 'required|string|different:cityFrom',
            'cityToHid' => 'required|different:cityFromHid',
            'departureDate' => 'required|date_format:Y-m-d',
            'returnDate' => 'nullable|date_format:Y-m-d|after_or_equal:departureDate',
            'adult' => 'required|integer|min:1|max:9',
            'child' => 'nullable|integer|min:0|max:9',
        ], $this->searchValidationMessages(), $this->validationAttributes());
        $validator->sometimes('returnDate', 'required', function ($input) {
            return ($input->trip_type ?? 'one_way') === 'round_trip';
        });

        if ($validator->fails()) {
            Log::warning('Bus V2 search validation failed', [
                'errors' => $validator->errors()->toArray(),
                'input_keys' => array_keys($request->all()),
                'ajax' => $this->wantsAjax($request),
            ]);

            $this->clearState();

            return $this->validationFailure($request, $validator, __('bus.messages.complete_search_fields'));
        }

        $adultCount = max(1, (int) $request->input('adult', 1));
        $childCount = max(0, (int) $request->input('child', 0));
        $tripType = $request->input('trip_type', 'one_way');

        $params = [
            'search_by' => 'cities',
            'from' => $request->cityFromHid,
            'to' => $request->cityToHid,
            'geolatfrom' => $request->geolatfrom ?? '',
            'geolonfrom' => $request->geolonfrom ?? '',
            'geolatto' => $request->geolatto ?? '',
            'geolonto' => $request->geolonto ?? '',
            'departure_date' => $request->departureDate,
            'd_date' => $request->departureDate,
            'adult' => $adultCount,
            'children' => $childCount,
            'bikes' => $request->bikes ?? '0',
            'currency' => 'EUR',
            'cityFrom' => $request->cityFrom,
            'cityTo' => $request->cityTo,
            'passengers' => $this->buildPassengerSummary($adultCount, $childCount),
            'sort_by' => $request->sort_by ?? '',
            'sort_by_bus' => $request->sort_by_bus ?? '',
        ];

        $busData = [
            'trip_type' => $tripType,
            'from_id' => $request->cityFromHid,
            'from_name' => $request->cityFrom,
            'geolatfrom' => $request->geolatfrom ?? '',
            'geolonfrom' => $request->geolonfrom ?? '',
            'geolatto' => $request->geolatto ?? '',
            'geolonto' => $request->geolonto ?? '',
            'to_id' => $request->cityToHid,
            'to_name' => $request->cityTo,
            'departure' => $request->departureDate,
            'return_date' => $tripType === 'round_trip' ? $request->returnDate : '',
            'passengers' => $this->buildPassengerSummary($adultCount, $childCount),
            'adult' => (string) $adultCount,
            'children' => (string) $childCount,
            'sort_by' => $request->sort_by ?? '',
            'sort_by_bus' => $request->sort_by_bus ?? '',
        ];

        if ($this->requestBoolean($request, 'bus_v2_sort_only') && $this->searchSessionMatches($busData)) {
            $storedResults = $this->sortStoredResults($tripType, $request->sort_by, $request->sort_by_bus);

            if ($storedResults !== null) {
                session()->put([
                    'bus_v2_data' => $busData,
                    'bus_v2_results' => $storedResults['results'],
                    'bus_v2_total' => $storedResults['total'],
                ]);

                if ($storedResults['total'] === 0) {
                    return $this->processingFailure($request, __('bus.messages.no_filtered_buses'));
                }

                return $this->stateSuccess(
                    $request,
                    trans_choice('bus.messages.options_loaded', $storedResults['total'], ['count' => $storedResults['total']])
                );
            }
        }

        if ($tripType === 'round_trip') {
            $returnDate = $this->normalizeDateInput($request->returnDate, 'Y-m-d');
            $params['returnDate'] = $returnDate;
            $params['return_departure_date'] = $returnDate;
            $params['return_date'] = $returnDate;
            $params['returnDepartureDate'] = $returnDate;
            $params['return_d_date'] = $returnDate;
        }

        try {
            $response = $this->busV2Api->search($params, $tripType === 'round_trip');
        } catch (\Exception $e) {
            Log::error('Bus V2 search exception', ['message' => $e->getMessage()]);
            $this->clearSearchFailureState($busData);
            return $this->processingFailure($request, __('bus.messages.fetch_error'), 500);
        }

        if ($response->getStatusCode() !== 200) {
            $this->clearSearchFailureState($busData);
            return $this->processingFailure($request, __('bus.messages.fetch_failed'), 500);
        }

        $results = json_decode($response->getBody()->getContents(), true);

        if ($tripType === 'round_trip') {
            $outboundResults = $this->sortResults($results['outbound'] ?? [], $request->sort_by, $request->sort_by_bus);
            $inboundSource = $results['inbound'] ?? ($results['return'] ?? []);
            $inboundResults = $this->sortResults($inboundSource, $request->sort_by, $request->sort_by_bus);
            $totalResults = count($outboundResults) + count($inboundResults);

            if ($totalResults === 0) {
                $this->clearSearchFailureState($busData);

                return $this->processingFailure($request, __('bus.messages.no_buses'));
            }

            $sortedResults = [
                'outbound' => $outboundResults,
                'inbound' => $inboundResults,
            ];
            $rawResults = [
                'outbound' => $results['outbound'] ?? [],
                'inbound' => $inboundSource,
            ];
        } else {
            if (empty($results)) {
                $this->clearSearchFailureState($busData);

                return $this->processingFailure($request, __('bus.messages.no_buses'));
            }

            $sortedResults = $this->sortResults($results, $request->sort_by, $request->sort_by_bus);
            if (empty($sortedResults)) {
                $this->clearState();
                session()->put('bus_v2_data', $busData);

                return $this->processingFailure($request, __('bus.messages.no_filtered_buses'));
            }

            $totalResults = count($sortedResults);
            $rawResults = $results;
        }

        session()->put([
            'bus_v2_data' => $busData,
            'bus_v2_raw_results' => $rawResults,
            'bus_v2_results' => $sortedResults,
            'bus_v2_total' => $totalResults,
        ]);
        $this->clearBookingState();

        return $this->stateSuccess(
            $request,
            trans_choice('bus.messages.options_loaded', $totalResults, ['count' => $totalResults])
        );
    }

    public function createFlixReservation(Request $request)
    {
        $isRoundTrip = ($request->input('trip_type') === 'round_trip')
            || ($request->filled('outbound_trip') && $request->filled('return_trip'));

        if ($isRoundTrip) {
            $validator = Validator::make($request->all(), [
                'outbound_trip' => 'required|string',
                'return_trip' => 'required|string',
            ], $this->defaultValidationMessages(), $this->validationAttributes());

            if ($validator->fails()) {
                return $this->validationFailure($request, $validator, __('bus.messages.select_flix_trip'), false);
            }

            $outboundTrip = $this->decodeTripSelection($request->input('outbound_trip'));
            $returnTrip = $this->decodeTripSelection($request->input('return_trip'));

            Log::info('Bus V2 Flix RT decode', [
                'outbound_raw_len' => strlen((string) $request->input('outbound_trip')),
                'return_raw_len'   => strlen((string) $request->input('return_trip')),
                'outbound_bus_uid' => $outboundTrip['bus_uid'] ?? 'MISSING',
                'return_bus_uid'   => $returnTrip['bus_uid'] ?? 'MISSING',
                'outbound_keys'    => $outboundTrip ? array_keys($outboundTrip) : [],
            ]);

            if (!$outboundTrip || !$returnTrip) {
                return $this->processingFailure($request, __('bus.messages.select_flix_trip'), 422, false);
            }

            try {
                $response = $this->busV2Api->createFlixReservation([
                    'outbound_trip_uid' => $outboundTrip['bus_uid'] ?? '',
                    'return_trip_uid' => $returnTrip['bus_uid'] ?? '',
                    'adult' => $outboundTrip['adult'] ?? '0',
                    'children' => $outboundTrip['children'] ?? '0',
                    'bikes' => $outboundTrip['bikes'] ?? '0',
                    'currency' => $outboundTrip['currency'] ?? 'EUR',
                ], true);
            } catch (RequestException $e) {
                $message = $this->extractApiExceptionMessage($e, __('bus.messages.reservation_error'));
                Log::error('Bus V2 Flix round-trip reservation exception', ['message' => $e->getMessage(), 'api_message' => $message]);
                return $this->processingFailure($request, $message, $this->exceptionStatusCode($e));
            } catch (\Exception $e) {
                Log::error('Bus V2 Flix round-trip reservation exception', ['message' => $e->getMessage()]);
                return $this->processingFailure($request, __('bus.messages.reservation_error'), 500);
            }

            if ($response->getStatusCode() !== 200) {
                return $this->processingFailure($request, __('bus.messages.reservation_failed'), 500);
            }

            $bookingPayload = $this->decodeApiResponseBody($response);
            if (!$this->hasFlixReservationPayload($bookingPayload)) {
                return $this->processingFailure(
                    $request,
                    $this->extractApiPayloadMessage($bookingPayload, __('bus.messages.reservation_failed')),
                    422
                );
            }

            $bookingPayload = $this->attachJourneyDetailsToFlixBookingPayload($bookingPayload, [
                $this->buildJourneyDetailsFromTrip($outboundTrip),
                $this->buildJourneyDetailsFromTrip($returnTrip),
            ]);
            $bookingPayload = $this->attachUiPassengersToFlixBookingPayload(
                $bookingPayload,
                (int) ($outboundTrip['adult'] ?? 0),
                (int) ($outboundTrip['children'] ?? 0)
            );

            session()->put([
                'bus_v2_booking' => $bookingPayload,
                'bus_v2_price' => round(((float) ($outboundTrip['total_price'] ?? 0)) + ((float) ($returnTrip['total_price'] ?? 0)), 2),
            ]);
            $this->startBookingSession();
            session()->forget(['bus_v2_bla_booking', 'bus_v2_total_price_with_margin']);

            return $this->stateSuccess($request, __('bus.messages.reservation_created'));
        }

        $validator = Validator::make($request->all(), [
            'trip_uid' => 'required',
            'currency' => 'required',
            'total_price' => 'required',
        ], $this->defaultValidationMessages(), $this->validationAttributes());

        if ($validator->fails()) {
            return $this->validationFailure($request, $validator, __('bus.messages.select_flix_trip'), false);
        }

        try {
            $response = $this->busV2Api->createFlixReservation([
                'trip_uid' => $request->trip_uid,
                'adult' => $request->input('adult', '0'),
                'children' => $request->input('children', '0'),
                'bikes' => $request->input('bikes', '0'),
                'currency' => $request->currency,
            ]);
        } catch (RequestException $e) {
            $message = $this->extractApiExceptionMessage($e, __('bus.messages.reservation_error'));
            Log::error('Bus V2 Flix reservation exception', ['message' => $e->getMessage(), 'api_message' => $message]);
            return $this->processingFailure($request, $message, $this->exceptionStatusCode($e));
        } catch (\Exception $e) {
            Log::error('Bus V2 Flix reservation exception', ['message' => $e->getMessage()]);
            return $this->processingFailure($request, __('bus.messages.reservation_error'), 500);
        }

        if ($response->getStatusCode() !== 200) {
            return $this->processingFailure($request, __('bus.messages.reservation_failed'), 500);
        }

        $bookingPayload = $this->decodeApiResponseBody($response);
        if (!$this->hasFlixReservationPayload($bookingPayload)) {
            return $this->processingFailure(
                $request,
                $this->extractApiPayloadMessage($bookingPayload, __('bus.messages.reservation_failed')),
                422
            );
        }

        $journeyDetails = $this->decodeJourneyDetailsPayload($request->input('journey_details'));
        if (empty($journeyDetails)) {
            $journeyDetails = $this->buildJourneyDetailsFromTrip($request->all());
        }
        $bookingPayload = $this->attachJourneyDetailsToFlixBookingPayload($bookingPayload, [$journeyDetails]);
        $bookingPayload = $this->attachUiPassengersToFlixBookingPayload(
            $bookingPayload,
            (int) $request->input('adult', 0),
            (int) $request->input('children', 0)
        );

        session()->put([
            'bus_v2_booking' => $bookingPayload,
            'bus_v2_price' => $request->total_price,
        ]);
        $this->startBookingSession();
        session()->forget(['bus_v2_bla_booking', 'bus_v2_total_price_with_margin']);

        return $this->stateSuccess($request, __('bus.messages.reservation_created'));
    }

    public function createBlaReservation(Request $request)
    {
        $isRoundTrip = ($request->input('trip_type') === 'round_trip')
            || ($request->filled('outbound_trip') && $request->filled('return_trip'));

        if ($isRoundTrip) {
            $validator = Validator::make($request->all(), [
                'outbound_trip' => 'required|string',
                'return_trip' => 'required|string',
            ], $this->defaultValidationMessages(), $this->validationAttributes());

            if ($validator->fails()) {
                return $this->validationFailure($request, $validator, __('bus.messages.select_bla_trip'), false);
            }

            $outboundTrip = $this->decodeTripSelection($request->input('outbound_trip'));
            $returnTrip = $this->decodeTripSelection($request->input('return_trip'));

            if (!$outboundTrip || !$returnTrip) {
                return $this->processingFailure($request, __('bus.messages.select_bla_trip'), 422, false);
            }

            $segments = $this->buildBlaSegmentsFromTrips([
                ['trip' => $outboundTrip, 'direction' => 'outbound'],
                ['trip' => $returnTrip, 'direction' => 'inbound'],
            ]);
            $passengers = $this->buildBlaPassengersAndAttachSegments(
                $segments,
                (int) ($outboundTrip['adult'] ?? 0),
                (int) ($outboundTrip['children'] ?? 0)
            );
            $totalPrice = round(((float) ($outboundTrip['total_price'] ?? 0)) + ((float) ($returnTrip['total_price'] ?? 0)), 2);

            try {
                $response = $this->busV2Api->createBlaReservation([
                    'segments' => $segments,
                    'passengers' => $passengers,
                ], true);
            } catch (RequestException $e) {
                $message = $this->extractApiExceptionMessage($e, __('bus.messages.reservation_error'));
                Log::error('Bus V2 Bla round-trip reservation exception', ['message' => $e->getMessage(), 'api_message' => $message]);
                return $this->processingFailure($request, $message, $this->exceptionStatusCode($e));
            } catch (\Exception $e) {
                Log::error('Bus V2 Bla round-trip reservation exception', ['message' => $e->getMessage()]);
                return $this->processingFailure($request, __('bus.messages.reservation_error'), 500);
            }

            if ($response->getStatusCode() !== 200) {
                return $this->processingFailure($request, __('bus.messages.reservation_failed'), 500);
            }

            $journeyDetails = array_values(array_filter([
                $this->buildJourneyDetailsFromTrip($outboundTrip),
                $this->buildJourneyDetailsFromTrip($returnTrip),
            ]));
            $blaPayload = (function () use ($response) {
                $payload = json_decode((string) $response->getBody(), true);
                return $payload['data'] ?? $payload;
            })();
            $blaPayload = $this->attachUiPassengersToBlaBookingPayload(
                is_array($blaPayload) ? $blaPayload : [],
                (int) ($outboundTrip['adult'] ?? 0),
                (int) ($outboundTrip['children'] ?? 0)
            );

            session()->put([
                'bus_v2_bla_booking' => [
                    'data' => $blaPayload,
                    'journey_details' => count($journeyDetails) === 1 ? ($journeyDetails[0] ?? []) : $journeyDetails,
                ],
                'bus_v2_total_price_with_margin' => $totalPrice,
            ]);
            $this->startBookingSession();
            session()->forget(['bus_v2_booking', 'bus_v2_price']);

            return $this->stateSuccess($request, __('bus.messages.reservation_created'));
        }

        $validator = Validator::make($request->all(), [
            'legs' => 'required|array|min:1',
            'validity_end_date' => 'required',
            'adult' => 'required|integer|min:0|max:9',
            'children' => 'nullable|integer|min:0|max:9',
            'total_price' => 'required',
        ], $this->defaultValidationMessages(), $this->validationAttributes());

        if ($validator->fails()) {
            return $this->validationFailure($request, $validator, __('bus.messages.select_bla_trip'), false);
        }

        $segments = $this->buildBlaSegmentsFromTrips([
            [
                'trip' => [
                    'legs' => $request->legs,
                    'validity_end_date' => $request->validity_end_date,
                ],
                'direction' => 'outbound',
            ],
        ]);
        $passengers = $this->buildBlaPassengersAndAttachSegments(
            $segments,
            (int) $request->adult,
            (int) $request->children
        );

        try {
            $response = $this->busV2Api->createBlaReservation([
                'segments' => $segments,
                'passengers' => $passengers,
            ]);
        } catch (RequestException $e) {
            $message = $this->extractApiExceptionMessage($e, __('bus.messages.reservation_error'));
            Log::error('Bus V2 Bla reservation exception', ['message' => $e->getMessage(), 'api_message' => $message]);
            return $this->processingFailure($request, $message, $this->exceptionStatusCode($e));
        } catch (\Exception $e) {
            Log::error('Bus V2 Bla reservation exception', ['message' => $e->getMessage()]);
            return $this->processingFailure($request, __('bus.messages.reservation_error'), 500);
        }

        if ($response->getStatusCode() !== 200) {
            return $this->processingFailure($request, __('bus.messages.reservation_failed'), 500);
        }

        $journeyDetails = $this->decodeJourneyDetailsPayload($request->input('journey_details'));
        if (empty($journeyDetails)) {
            $journeyDetails = $this->buildJourneyDetailsFromTrip(array_merge($request->all(), [
                'legs' => $request->legs,
                'validity_end_date' => $request->validity_end_date,
            ]));
        }
        $blaPayload = (function () use ($response) {
            $payload = json_decode((string) $response->getBody(), true);
            return $payload['data'] ?? $payload;
        })();
        $blaPayload = $this->attachUiPassengersToBlaBookingPayload(
            is_array($blaPayload) ? $blaPayload : [],
            (int) $request->adult,
            (int) $request->children
        );

        session()->put([
            'bus_v2_bla_booking' => [
                'data' => $blaPayload,
                'journey_details' => $journeyDetails,
            ],
            'bus_v2_total_price_with_margin' => $request->total_price,
        ]);
        $this->startBookingSession();
        session()->forget(['bus_v2_booking', 'bus_v2_price']);

        return $this->stateSuccess($request, __('bus.messages.reservation_created'));
    }

    public function confirmFlix(FlixCheckoutRequest $request)
    {
        if ($expiredResponse = $this->ensureBookingSessionActive($request)) {
            return $expiredResponse;
        }

        $params = [
            'reservation_token' => $request->reservation_token,
            'with_donation' => true,
            'donation_partner' => 'atmosfair',
            'reservation_id' => $request->reservation_id,
            'email' => $request->email[0],
            'departure_time' => $request->departure_time,
            'from_name' => $request->from_name,
            'to_name' => $request->to_name,
            'total_price' => $request->total_price,
            'price' => $request->price,
        ];

        $flixPassengers = $this->expandFlixCheckoutPassengers($this->collectFlixCheckoutPassengers($request));

        for ($i = 0; $i < count($flixPassengers); $i++) {
            $passenger = $flixPassengers[$i];

            $params["passengers[$i][firstname]"] = $passenger['firstname'];
            $params["passengers[$i][passenger_no]"] = $i;
            $params["passengers[$i][lastname]"] = $passenger['lastname'];
            $params["passengers[$i][phone]"] = $passenger['phone_number'];
            $params["passengers[$i][birthdate]"] = $this->normalizeDateInput($passenger['birthdate'], 'd.m.Y');
            $params["passengers[$i][type]"] = $passenger['product_type'];
            $params["passengers[$i][product_type]"] = $passenger['product_type'];
            $params["passengers[$i][reference_id]"] = $passenger['reference_id'];
            $params["passengers[$i][identification_type]"] = $passenger['identification_type'];
            $params["passengers[$i][identification_number]"] = $passenger['identification_number'];
            $params["passengers[$i][gender]"] = $passenger['gender'];
            $params["passengers[$i][citizenship]"] = $passenger['citizenship'];
            $params["passengers[$i][identification_issuing_country]"] = $passenger['identification_issuing_country'];
            $params["passengers[$i][identification_expiry_date]"] = $this->normalizeDateInput($passenger['identification_expiry_date'], 'd.m.Y');
            $params["passengers[$i][visa_permit_type]"] = $passenger['visa_permit_type'];
        }

        return $this->confirmAndPersist(
            $request,
            $params,
            str_replace(',', '', $request->input('price')),
            'flixbus',
            $request->phone_number ? preg_replace('/\D/', '', $request->phone_number[0]) : '+917904721979',
            function () use ($params) {
                return $this->busV2Api->confirmFlix($params);
            },
            function ($result, $fallbackTxnId, $userInfo, $euroAmount) {
                $ticket = $result['print_ticket'];
                $ticketInstruction = $ticket[0] ?? '';
                $ticketLink = $ticket[1] ?? '';

                return [
                    'txn_ref' => isset($result['txn_ref']) ? $result['txn_ref'] : $fallbackTxnId,
                    'order_desc' => $userInfo->username . ' Flix bus for ' . $euroAmount . '. Print Ticket Link is ' . $ticketInstruction,
                    'instruction' => $ticketInstruction,
                    'link' => $ticketLink,
                    'provider' => 'FlixBus',
                    'ticket_url' => $this->ticketDownloadUrl($ticketInstruction, $ticketLink),
                ];
            }
        );
    }

    public function confirmBla(BlaCheckoutRequest $request)
    {
        if ($expiredResponse = $this->ensureBookingSessionActive($request)) {
            return $expiredResponse;
        }

        $params = [
            'booking_number' => $request->booking_number,
            'booking_id' => $request->booking_id,
            'sales_channel_code' => $request->sales_channel_code,
            'departure_time' => $request->departure_time,
            'arrival_time' => $request->arrival_time,
            'from_name' => $request->from_name,
            'to_name' => $request->to_name,
            'price' => $request->price,
            'currency' => $request->currency,
            'total_vat' => $request->total_vat,
            'total_price_paid' => $request->total_price_paid,
            'total_price_to_be_paid' => $request->total_price_to_be_paid,
        ];

        $passengers = [];
        $blaPassengers = $this->expandBlaCheckoutPassengers($this->collectBlaCheckoutPassengers($request));

        for ($i = 0; $i < count($blaPassengers); $i++) {
            $passenger = $blaPassengers[$i];

            $passengers[] = [
                'id' => $passenger['passenger_id'],
                'type' => $passenger['passenger_type'],
                'disability_type' => $passenger['passenger_disability_type'],
                'ref_id' => $passenger['passenger_ref_id'],
                'uuid' => $passenger['passenger_uuid'],
                'first_name' => $passenger['firstname'],
                'last_name' => $passenger['lastname'],
                'birthdate' => $this->normalizeDateInput($passenger['birthdate'], 'Y-m-d'),
                'gender' => $passenger['gender'],
                'email' => $passenger['email'],
                'phone' => $passenger['phone_number'],
                'citizenship' => $passenger['citizenship'],
                'identification_number' => $passenger['identification_number'],
                'identification_expiry_date' => $this->normalizeDateInput($passenger['identification_expiry_date'], 'd.m.Y'),
                'visa_permit_type' => $passenger['visa_permit_type'],
                'identification_issuing_country' => $passenger['identification_issuing_country'],
                'identification_type' => $passenger['identification_type'],
            ];
        }
        $params['passengers'] = $passengers;

        $segments = [];
        for ($i = 0; $i < count($request->segment_id); $i++) {
            $segments[] = [
                'id' => $request->segment_id[$i],
                'departure_station' => $request->segment_departure_station[$i],
                'arrival_station' => $request->segment_arrival_station[$i],
                'service_name' => $request->segment_service_name[$i],
                'departure_time' => $request->segment_departure_time[$i],
                'arrival_time' => $request->segment_arrival_time[$i],
            ];
        }
        $params['segments'] = $segments;

        return $this->confirmAndPersist(
            $request,
            $params,
            str_replace(',', '', number_format($request->input('total_price'), 2)),
            'blabla',
            $request->phone_number ? preg_replace('/\D/', '', $request->phone_number[0]) : '+917904721979',
            function () use ($params) {
                return $this->busV2Api->confirmBla($params);
            },
            function ($result, $fallbackTxnId, $userInfo, $euroAmount) {
                $data = $result['data'] ?? [];
                $bookingNumber = $data['booking_number'] ?? $fallbackTxnId;
                $ticketInstruction = $data['booking_id'] ?? $bookingNumber;
                $ticketLink = $data['ticket_url'] ?? '';

                return [
                    'txn_ref' => $bookingNumber,
                    'order_desc' => $userInfo->username . ' Flix bus for ' . $euroAmount . ' Print Ticket Link is ' . $ticketInstruction,
                    'instruction' => $ticketInstruction,
                    'link' => $ticketLink,
                    'provider' => 'BlaBlaBus',
                    'ticket_url' => $this->ticketDownloadUrl($ticketInstruction, $ticketLink),
                ];
            }
        );
    }

    public function reset(Request $request)
    {
        $preserveSearch = $this->requestBoolean($request, 'preserve_search');

        if ($preserveSearch) {
            $this->clearBookingState();
        } else {
            $this->clearState();
        }

        if ($this->wantsAjax($request)) {
            return response()->json([
                'success' => true,
                'message' => $preserveSearch ? __('bus.messages.booking_restarted') : __('bus.messages.search_reset'),
                'html' => $this->renderStateHtml(),
            ]);
        }

        return redirect()->route('bus.v2');
    }

    public function fetchTripStops(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trip' => 'required',
        ], $this->defaultValidationMessages(), $this->validationAttributes());

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => __('bus.validation.invalid_selection'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $tripPayload = $request->input('trip');
        if (is_array($tripPayload)) {
            $tripPayload = json_encode($tripPayload);
        }

        try {
            $response = $this->busV2Api->tripStops($tripPayload);
        } catch (RequestException $e) {
            $message = $this->extractApiExceptionMessage($e, __('bus.messages.fetch_error'));

            return response()->json([
                'success' => false,
                'message' => $message,
            ], $this->exceptionStatusCode($e));
        } catch (\Exception $e) {
            Log::error('Bus V2 trip stops exception', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => __('bus.messages.fetch_error'),
            ], 500);
        }

        $payload = $this->decodeApiResponseBody($response);
        $data = $payload['data'] ?? $payload;

        return response()->json([
            'success' => true,
            'data' => is_array($data) ? $data : [],
        ]);
    }

    private function confirmAndPersist(Request $request, array $payload, $euroAmount, $mobileOperator, $mobileNumber, callable $apiCall, callable $resultMapper)
    {
        $userInfo = User::find(auth()->user()->id);
        $context = $this->buildOrderContext($userInfo, $euroAmount);

        if ($failure = $this->guardOrderLimits($request, $userInfo, $euroAmount, $context)) {
            return $failure;
        }

        try {
            \DB::beginTransaction();

            $fallbackTxnId = TRANSACTION_PREFIX . ServiceHelper::genTransID(5);
            $response = $apiCall();
            $result = json_decode((string) $response->getBody(), true);
            $meta = $resultMapper($result, $fallbackTxnId, $userInfo, $euroAmount);

            $orderId = $this->persistOrder(
                $userInfo,
                $context,
                $euroAmount,
                $meta['txn_ref'],
                $meta['order_desc'],
                $mobileOperator,
                $mobileNumber,
                $meta['instruction'],
                $meta['link']
            );

            \DB::commit();
            $this->clearState();
            AppHelper::logger('success', 'Flix Bus Order #' . $orderId, $meta['order_desc']);

            if ($this->wantsAjax($request)) {
                return response()->json([
                    'success' => true,
                    'message' => __('bus.messages.ticket_issued', ['provider' => $meta['provider']]),
                    'html' => $this->renderSuccessHtml($meta['provider'], $orderId, $meta['ticket_url']),
                    'redirect_url' => secure_url('transactions'),
                ]);
            }

            return redirect('transactions')
                ->with('message', __('bus.messages.ticket_issued', ['provider' => $meta['provider']]))
                ->with('message_type', 'success');
        } catch (\Exception $e) {
            \DB::rollback();
            $exceptionId = 'TTEX' . AppHelper::Numeric(5);
            AppHelper::logger('warning', 'Flix Bus Exception ' . $exceptionId, $e->getMessage(), [
                'File' => $e->getFile(),
                'Line' => $e->getLine(),
                'Code' => $e->getCode(),
            ]);

            return $this->processingFailure($request, __('bus.messages.ticket_issue_failed', ['id' => $exceptionId]), 500);
        }
    }

    private function buildOrderContext(User $userInfo, $euroAmount)
    {
        $userServiceCommission = ServiceHelper::get_service_commission($userInfo->id, $this->service_id);
        $currentBalance = AppHelper::getBalance($userInfo->id, $userInfo->currency, false);
        $orderAmount = ServiceHelper::calculate_commission($euroAmount, $userServiceCommission);

        return [
            'user_service_commission' => $userServiceCommission,
            'current_balance' => $currentBalance,
            'order_amount' => $orderAmount,
            'user_credit_limit' => AppHelper::get_credit_limit($userInfo->id),
            'sale_margin' => ServiceHelper::calculate_sale_margin($euroAmount, $orderAmount),
            'after_order_balance' => number_format((float) $currentBalance - $orderAmount, 2, '.', ''),
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }

    private function guardOrderLimits(Request $request, User $userInfo, $euroAmount, array $context)
    {
        $checkLimit = AppHelper::get_daily_limit(auth()->user()->id, auth()->user()->currency, true);
        if ($checkLimit != null && ServiceHelper::limit_check($userInfo->id, $euroAmount)) {
            $rBal = \app\Library\AppHelper::get_remaning_limit_balance(auth()->user()->id);
            $dailyLimit = \app\Library\AppHelper::get_daily_limit(auth()->user()->id);
            $getBalance = \app\Library\AppHelper::getBalance(auth()->user()->id, auth()->user()->currency, false);
            $blinkLimit = str_replace('-', '', $rBal);
            $managerId = auth()->user()->parent_id;
            \app\Library\AppHelper::sendMail($rBal, $dailyLimit, $getBalance, $blinkLimit, $managerId, auth()->user()->username);

            AppHelper::logger('warning', 'Daily Limit Exceed', $userInfo->username . 'Daily limit exceed to confirm Flix bus order', $request->all());
            return $this->processingFailure($request, trans('common.contact_manager'));
        }

        if (ServiceHelper::parent_rule_check($userInfo->parent_id, $euroAmount, $this->service_id)) {
            AppHelper::logger('warning', 'Parent Rule Failed', $userInfo->username . ' parent does not have enough balance or credit limit to confirm Flix bus order', $request->all());
            return $this->processingFailure($request, trans('common.parent_rule_failed'));
        }

        if (
            $context['current_balance'] < $context['order_amount'] &&
            ServiceHelper::check_with_credit_limit($context['order_amount'], $context['current_balance'], $context['user_credit_limit']) == false
        ) {
            AppHelper::logger('warning', 'Flix Bus Balance Error', $userInfo->username . ' does not have enough balance or credit limit to confirm Flix Bus order', $request->all());
            return $this->processingFailure($request, trans('common.msg_order_failed_due_bal'));
        }

        return null;
    }

    private function persistOrder(User $userInfo, array $context, $euroAmount, $txnRef, $orderDesc, $mobileOperator, $mobileNumber, $instruction, $link)
    {
        $transId = ServiceHelper::sync_transaction(
            $userInfo->id,
            $context['created_at'],
            'debit',
            $context['order_amount'],
            $context['current_balance'],
            $context['after_order_balance'],
            $orderDesc
        );

        $orderId = ServiceHelper::save_order(
            '',
            $context['created_at'],
            $userInfo->id,
            $this->service_id,
            '7',
            $transId,
            $txnRef,
            $orderDesc,
            $userInfo->currency,
            $euroAmount,
            $context['sale_margin'],
            $context['order_amount'],
            $context['order_amount'],
            $context['order_amount'],
            null,
            0,
            0
        );

        $orderItemId = ServiceHelper::save_orders_items(
            $orderId,
            $mobileNumber,
            $euroAmount,
            $mobileOperator,
            $instruction,
            $link,
            $context['created_at'],
            $userInfo->id
        );

        Order::where('id', $orderId)->update(['order_item_id' => $orderItemId]);

        $parentUser = User::find($userInfo->parent_id);
        if (!empty($userInfo->parent_id) && $parentUser && $parentUser->group_id != 2) {
            $parentUserCommission = ServiceHelper::get_service_commission($parentUser->id, $this->service_id);
            $parentCurrentBalance = AppHelper::getBalance($parentUser->id, $parentUser->currency, false);
            $buyingPriceParent = ServiceHelper::calculate_commission($euroAmount, $parentUserCommission);
            $parentAfterOrderBalance = number_format((float) $parentCurrentBalance - $buyingPriceParent, 2, '.', '');
            $parentSaleMargin = ServiceHelper::calculate_sale_margin($context['order_amount'], $buyingPriceParent);
            $parentTransId = ServiceHelper::sync_transaction($parentUser->id, $context['created_at'], 'debit', $buyingPriceParent, $parentCurrentBalance, $parentAfterOrderBalance, $orderDesc);

            ServiceHelper::save_order($orderDesc, $context['created_at'], $userInfo->id, $this->service_id, '7', $parentTransId, $txnRef, $orderDesc, $userInfo->currency, $euroAmount, $parentSaleMargin, $context['order_amount'], $buyingPriceParent, $context['order_amount'], $orderItemId, 1, 0);

            $appCommission = optional(AppCommission::where('service_id', $this->service_id)->first())->commission;
            $buyingPriceApp = ServiceHelper::calculate_commission($euroAmount, $appCommission);
            $appSaleMargin = ServiceHelper::calculate_sale_margin($buyingPriceParent, $buyingPriceApp);

            ServiceHelper::save_order($orderDesc, $context['created_at'], $parentUser->id, $this->service_id, '7', $transId, $txnRef, $orderDesc, $userInfo->currency, $euroAmount, $appSaleMargin, $buyingPriceParent, $buyingPriceApp, $buyingPriceParent, $orderItemId, 1, 0);
        } else {
            $appCommission = optional(AppCommission::where('service_id', $this->service_id)->first())->commission;
            $appActualCommission = $appCommission - $context['user_service_commission'];
            $buyingPriceApp = ServiceHelper::calculate_commission($euroAmount, $appCommission);
            $orderAmountApp = ServiceHelper::calculate_commission($euroAmount, $appActualCommission);
            $appSaleMargin = ServiceHelper::calculate_sale_margin($euroAmount, $orderAmountApp);

            ServiceHelper::save_order($orderDesc, $context['created_at'], $parentUser ? $parentUser->id : $userInfo->id, $this->service_id, '7', $transId, $txnRef, '', $userInfo->currency, $euroAmount, $appSaleMargin, $context['order_amount'], $buyingPriceApp, $context['order_amount'], $orderItemId, 1, 0);
        }

        return $orderId;
    }

    private function fetchCities()
    {
        $response = $this->busV2Api->cities();

        if ($response->getStatusCode() !== 200) {
            return [];
        }

        $citiesAndStations = json_decode((string) $response->getBody(), true);
        return $citiesAndStations['data'] ?? [];
    }

    private function sortResults($results, $sortBy, $sortByBus)
    {
        $collection = collect($results);

        if ((string) $sortByBus === '4') {
            $collection = $collection->filter(function ($item) {
                $busType = $item['bus_type'] ?? '';
                return stripos($busType, 'blabla') === false && stripos($busType, 'comuto') === false && $busType !== 'Mixed';
            });
        } elseif ((string) $sortByBus === '5') {
            $collection = $collection->filter(function ($item) {
                $busType = $item['bus_type'] ?? '';
                return stripos($busType, 'blabla') !== false || stripos($busType, 'comuto') !== false || $busType === 'Mixed';
            });
        }

        if ((string) $sortBy === '2') {
            $sorted = $collection->sortBy('total_price');
        } elseif ((string) $sortBy === '3') {
            $sorted = $collection->sortBy(function ($item) {
                return ((int) ($item['duration_hour'] ?? 0) * 60) + (int) ($item['duration_minutes'] ?? 0);
            });
        } else {
            $sorted = $collection->sortBy('departure');
        }

        return $sorted->values()->toArray();
    }

    private function searchSessionMatches(array $busData)
    {
        $storedSearch = session('bus_v2_data', []);

        if (!is_array($storedSearch) || empty($storedSearch)) {
            return false;
        }

        foreach (['trip_type', 'from_id', 'to_id', 'departure', 'return_date', 'adult', 'children'] as $key) {
            if ((string) ($storedSearch[$key] ?? '') !== (string) ($busData[$key] ?? '')) {
                return false;
            }
        }

        return true;
    }

    private function sortStoredResults($tripType, $sortBy, $sortByBus)
    {
        $rawResults = session('bus_v2_raw_results');

        if (!is_array($rawResults) || empty($rawResults)) {
            $rawResults = session('bus_v2_results', []);
        }

        if (!is_array($rawResults) || empty($rawResults)) {
            return null;
        }

        if ($tripType === 'round_trip') {
            $outboundResults = $this->sortResults($rawResults['outbound'] ?? [], $sortBy, $sortByBus);
            $inboundResults = $this->sortResults($rawResults['inbound'] ?? ($rawResults['return'] ?? []), $sortBy, $sortByBus);

            return [
                'results' => [
                    'outbound' => $outboundResults,
                    'inbound' => $inboundResults,
                ],
                'total' => count($outboundResults) + count($inboundResults),
            ];
        }

        $sortedResults = $this->sortResults($rawResults, $sortBy, $sortByBus);

        return [
            'results' => $sortedResults,
            'total' => count($sortedResults),
        ];
    }

    private function stateData()
    {
        $bookingExpiresAt = $this->bookingExpiresAt();
        $bookingExpired = $this->isBookingSessionExpired();
        $searchData = session('bus_v2_data', []);
        $adultCount = (int) ($searchData['adult'] ?? 0);
        $childCount = (int) ($searchData['children'] ?? 0);
        $flixBooking = session('bus_v2_booking');
        $blaBooking = session('bus_v2_bla_booking');

        if (is_array($flixBooking)) {
            $flixBooking = $this->attachUiPassengersToFlixBookingPayload($flixBooking, $adultCount, $childCount);
        }

        if (is_array($blaBooking) && isset($blaBooking['data']) && is_array($blaBooking['data'])) {
            $blaBooking['data'] = $this->attachUiPassengersToBlaBookingPayload(
                $this->normalizeBlaBookingPayload($blaBooking['data']),
                $adultCount,
                $childCount
            );
        }

        return [
            'searchData' => $searchData,
            'results' => session('bus_v2_results', []),
            'totalResults' => session('bus_v2_total', 0),
            'flixBooking' => $flixBooking,
            'flixPrice' => session('bus_v2_price'),
            'blaBooking' => $blaBooking,
            'blaPrice' => session('bus_v2_total_price_with_margin'),
            'bookingExpiresAt' => $bookingExpiresAt,
            'bookingExpired' => $bookingExpired,
        ];
    }

    private function getCountries()
    {
        if ($this->countriesCache === null) {
            $this->countriesCache = Country::orderBy('name')->get(['name', 'iso', 'iso3']);
        }

        return $this->countriesCache;
    }

    private function validationFailure(Request $request, $validator, $message, $preserveInput = true)
    {
        if ($this->wantsAjax($request)) {
            if ($preserveInput) {
                session()->flashInput($request->input());
            }

            $route = $request->route();
            $status = $route && $route->getName() === 'bus.v2.search' ? 200 : 422;

            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => $validator->errors(),
                'html' => $preserveInput ? $this->renderStateHtml() : null,
            ], $status);
        }

        $redirect = redirect()->route('bus.v2')->withErrors($validator);
        if ($preserveInput) {
            $redirect->withInput();
        }

        return $redirect;
    }

    private function processingFailure(Request $request, $message, $status = 422, $preserveInput = true)
    {
        if ($this->wantsAjax($request)) {
            if ($preserveInput) {
                session()->flashInput($request->input());
            }

            return response()->json([
                'success' => false,
                'message' => $message,
                'html' => $this->renderStateHtml(),
            ], $status);
        }

        $redirect = redirect()->route('bus.v2')
            ->with('message', $message)
            ->with('message_type', 'warning');

        if ($preserveInput) {
            $redirect->withInput();
        }

        return $redirect;
    }

    private function stateSuccess(Request $request, $message)
    {
        if ($this->wantsAjax($request)) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'html' => $this->renderStateHtml(),
            ]);
        }

        return redirect()->route('bus.v2');
    }

    private function renderStateHtml()
    {
        $this->ensureBookingSessionInitialized();

        return view($this->busPartial('state'), array_merge(
            $this->stateData(),
            [
                'countries' => $this->getCountries(),
                'busV2Design' => $this->busDesign(),
            ]
        ))->render();
    }

    private function renderSuccessHtml($provider, $orderId, $ticketUrl)
    {
        return view($this->busPartial('success'), [
            'provider' => $provider,
            'orderId' => $orderId,
            'ticketUrl' => $ticketUrl,
            'transactionsUrl' => secure_url('transactions'),
        ])->render();
    }

    private function ticketDownloadUrl($instruction, $link)
    {
        $instruction = trim((string) $instruction);
        $link = trim((string) $link);

        if ($instruction === '' || $link === '') {
            return secure_url('transactions');
        }

        if (filter_var($link, FILTER_VALIDATE_URL)) {
            return $link;
        }

        return secure_url('flix-bus/download') . '?' . http_build_query([
            'instruction' => $instruction,
            'link' => $link,
        ]);
    }

    private function buildPassengerSummary($adult, $child)
    {
        $parts = [trans_choice('bus.passenger.adult', $adult, ['count' => $adult])];

        if ($child > 0) {
            $parts[] = trans_choice('bus.passenger.child', $child, ['count' => $child]);
        }

        return implode(', ', $parts);
    }

    private function decodeJourneyDetailsPayload($value)
    {
        $encoded = trim((string) $value);
        if ($encoded === '') {
            return [];
        }

        $decoded = base64_decode($encoded, true);
        if ($decoded === false) {
            $decoded = $encoded;
        }

        $journeyDetails = json_decode($decoded, true);

        return is_array($journeyDetails) ? $journeyDetails : [];
    }

    private function buildJourneyDetailsFromTrip(array $trip)
    {
        $journeyDetails = [
            'from_name' => $trip['from_name'] ?? '',
            'to_name' => $trip['to_name'] ?? '',
            'departure' => $trip['departure'] ?? '',
            'arrival' => $trip['arrival'] ?? '',
            'bus_type' => $trip['bus_type'] ?? '',
            'duration_hour' => $trip['duration_hour'] ?? 0,
            'duration_minutes' => $trip['duration_minutes'] ?? 0,
            'travel_date' => $trip['travel_date'] ?? '',
            'amenities' => is_array($trip['amenities'] ?? null) ? $trip['amenities'] : [],
            'stops' => is_array($trip['stops'] ?? null) ? $trip['stops'] : [],
            'legs' => is_array($trip['legs'] ?? null) ? $trip['legs'] : [],
        ];

        return array_filter($journeyDetails, function ($value) {
            return !(is_array($value) && empty($value)) && $value !== null;
        });
    }

    private function attachJourneyDetailsToFlixBookingPayload(array $payload, array $journeyDetailsList)
    {
        $journeyDetailsList = array_values(array_filter($journeyDetailsList, function ($item) {
            return is_array($item) && !empty($item);
        }));

        if (empty($journeyDetailsList)) {
            return $payload;
        }

        $journeyPayload = count($journeyDetailsList) === 1 ? $journeyDetailsList[0] : $journeyDetailsList;
        $tripSummaries = array_values(array_filter(array_map(function ($journeyDetails) {
            $summary = [
                'from_name' => $journeyDetails['from_name'] ?? null,
                'to_name' => $journeyDetails['to_name'] ?? null,
                'departure_time' => $this->composeJourneyDepartureDateTime($journeyDetails),
            ];

            return array_filter($summary, function ($value) {
                return $value !== null && $value !== '';
            });
        }, $journeyDetailsList)));

        if (isset($payload[0]) && is_array($payload[0])) {
            $payload[0]['journey_details'] = $journeyPayload;
            if (empty($payload[0]['trips']) && !empty($tripSummaries)) {
                $payload[0]['trips'] = $tripSummaries;
            }

            return $payload;
        }

        $payload['journey_details'] = $journeyPayload;
        if (empty($payload['trips']) && !empty($tripSummaries)) {
            $payload['trips'] = $tripSummaries;
        }

        return $payload;
    }

    private function composeJourneyDepartureDateTime(array $journeyDetails)
    {
        $travelDate = trim((string) ($journeyDetails['travel_date'] ?? ''));
        $departure = trim((string) ($journeyDetails['departure'] ?? ''));

        if ($travelDate === '' || $departure === '') {
            return null;
        }

        $dateTime = $travelDate . ' ' . $departure;
        $timestamp = strtotime($dateTime);

        return $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
    }

    private function decodeTripSelection($value)
    {
        $encoded = trim((string) $value);
        if ($encoded === '') {
            return null;
        }

        $decoded = base64_decode($encoded, true);
        if ($decoded === false) {
            $decoded = $encoded;
        }

        $trip = json_decode($decoded, true);

        return is_array($trip) ? $trip : null;
    }

    private function decodeApiResponseBody($response)
    {
        if (!$response) {
            return [];
        }

        $payload = json_decode((string) $response->getBody(), true);

        return is_array($payload) ? $payload : [];
    }

    private function hasFlixReservationPayload(array $payload)
    {
        if (isset($payload[0]) && is_array($payload[0])) {
            $payload = $payload[0];
        }

        return !empty($payload['reservation_token']) && !empty($payload['reservation_id']) && isset($payload['passenger_details']);
    }

    private function attachUiPassengersToFlixBookingPayload(array $payload, $adultCount, $childCount)
    {
        if (isset($payload[0]) && is_array($payload[0])) {
            $payload[0]['ui_passenger_details'] = $this->selectUiPassengers(
                array_values($payload[0]['passenger_details'] ?? []),
                $adultCount,
                $childCount
            );

            return $payload;
        }

        $payload['ui_passenger_details'] = $this->selectUiPassengers(
            array_values($payload['passenger_details'] ?? []),
            $adultCount,
            $childCount
        );

        return $payload;
    }

    private function attachUiPassengersToBlaBookingPayload(array $payload, $adultCount, $childCount)
    {
        $payload = $this->normalizeBlaBookingPayload($payload);

        if (isset($payload['booking']) && is_array($payload['booking'])) {
            $payload['booking']['ui_passengers'] = $this->selectUiPassengers(
                array_values($payload['booking']['passengers'] ?? []),
                $adultCount,
                $childCount
            );
        }

        return $payload;
    }

    private function normalizeBlaBookingPayload(array $payload)
    {
        if (isset($payload['booking']) && is_array($payload['booking'])) {
            $booking = $payload['booking'];

            if (isset($booking['data']['booking']) && is_array($booking['data']['booking'])) {
                $payload['booking'] = $booking['data']['booking'];
            } elseif (isset($booking['data']['data']['booking']) && is_array($booking['data']['data']['booking'])) {
                $payload['booking'] = $booking['data']['data']['booking'];
            } elseif (isset($booking['booking']) && is_array($booking['booking'])) {
                $payload['booking'] = $booking['booking'];
            }

            return $payload;
        }

        if (isset($payload['data']['booking']) && is_array($payload['data']['booking'])) {
            return ['booking' => $payload['data']['booking']];
        }

        if (isset($payload['data']['data']['booking']) && is_array($payload['data']['data']['booking'])) {
            return ['booking' => $payload['data']['data']['booking']];
        }

        if (!empty($payload['booking_number']) || !empty($payload['outbound_booking_tariff_segments']) || !empty($payload['inbound_booking_tariff_segments'])) {
            return ['booking' => $payload];
        }

        return $payload;
    }

    private function selectUiPassengers(array $passengers, $adultCount, $childCount)
    {
        $passengers = array_values($passengers);
        $adultCount = max(0, (int) $adultCount);
        $childCount = max(0, (int) $childCount);
        $targetTotal = $adultCount + $childCount;

        if (empty($passengers) || $targetTotal < 1 || count($passengers) <= $targetTotal) {
            return $passengers;
        }

        $selected = [];
        $used = [];

        foreach ([['adult', $adultCount], ['child', $childCount]] as $group) {
            $needed = $group[1];

            foreach ($passengers as $index => $passenger) {
                if ($needed <= 0) {
                    break;
                }

                if (isset($used[$index]) || $this->passengerTypeKey($passenger['type'] ?? ($passenger['product_type'] ?? '')) !== $group[0]) {
                    continue;
                }

                $selected[] = $passenger;
                $used[$index] = true;
                $needed--;
            }
        }

        foreach ($passengers as $index => $passenger) {
            if (count($selected) >= $targetTotal) {
                break;
            }

            if (!isset($used[$index])) {
                $selected[] = $passenger;
                $used[$index] = true;
            }
        }

        return $selected;
    }

    private function passengerTypeKey($type)
    {
        $type = strtolower(trim((string) $type));

        return in_array($type, ['a', 'adult', 'adt'], true) ? 'adult' : 'child';
    }

    private function currentFlixBookingPassengerDetails()
    {
        $booking = session('bus_v2_booking', []);

        if (isset($booking[0]) && is_array($booking[0])) {
            $booking = $booking[0];
        }

        return array_values(is_array($booking) ? ($booking['passenger_details'] ?? []) : []);
    }

    private function currentBlaBookingPassengers()
    {
        $booking = session('bus_v2_bla_booking', []);
        $data = is_array($booking) ? $this->normalizeBlaBookingPayload($booking['data'] ?? []) : [];
        $reservation = $data['booking'] ?? [];

        return array_values(is_array($reservation) ? ($reservation['passengers'] ?? []) : []);
    }

    private function requestArrayValue(Request $request, $key, $index, $default = '')
    {
        $values = $request->input($key, []);

        if (!is_array($values)) {
            return $default;
        }

        return array_key_exists($index, $values) ? $values[$index] : $default;
    }

    private function collectFlixCheckoutPassengers(FlixCheckoutRequest $request)
    {
        $passengers = [];
        $count = count((array) $request->input('firstname', []));

        for ($i = 0; $i < $count; $i++) {
            $passengers[] = [
                'firstname' => $this->requestArrayValue($request, 'firstname', $i),
                'lastname' => $this->requestArrayValue($request, 'lastname', $i),
                'phone_number' => $this->requestArrayValue($request, 'phone_number', $i),
                'birthdate' => $this->requestArrayValue($request, 'birthdate', $i),
                'product_type' => $this->requestArrayValue($request, 'product_type', $i, 'adult'),
                'reference_id' => $this->requestArrayValue($request, 'reference_id', $i),
                'identification_type' => $this->requestArrayValue($request, 'identification_type', $i, 'international_passport'),
                'identification_number' => $this->requestArrayValue($request, 'identification_number', $i),
                'gender' => $this->requestArrayValue($request, 'gender', $i),
                'citizenship' => $this->requestArrayValue($request, 'citizenship', $i),
                'identification_issuing_country' => $this->requestArrayValue($request, 'identification_issuing_country', $i),
                'identification_expiry_date' => $this->requestArrayValue($request, 'identification_expiry_date', $i),
                'visa_permit_type' => $this->requestArrayValue($request, 'visa_permit_type', $i),
            ];
        }

        return $passengers;
    }

    private function collectBlaCheckoutPassengers(BlaCheckoutRequest $request)
    {
        $passengers = [];
        $count = count((array) $request->input('firstname', []));

        for ($i = 0; $i < $count; $i++) {
            $passengers[] = [
                'passenger_id' => $this->requestArrayValue($request, 'passenger_id', $i),
                'passenger_type' => $this->requestArrayValue($request, 'passenger_type', $i, 'A'),
                'passenger_disability_type' => $this->requestArrayValue($request, 'passenger_disability_type', $i, 'NH'),
                'passenger_ref_id' => $this->requestArrayValue($request, 'passenger_ref_id', $i),
                'passenger_uuid' => $this->requestArrayValue($request, 'passenger_uuid', $i),
                'firstname' => $this->requestArrayValue($request, 'firstname', $i),
                'lastname' => $this->requestArrayValue($request, 'lastname', $i),
                'birthdate' => $this->requestArrayValue($request, 'birthdate', $i),
                'gender' => $this->requestArrayValue($request, 'gender', $i),
                'email' => $this->requestArrayValue($request, 'email', $i),
                'phone_number' => $this->requestArrayValue($request, 'phone_number', $i),
                'citizenship' => $this->requestArrayValue($request, 'citizenship', $i),
                'identification_number' => $this->requestArrayValue($request, 'identification_number', $i),
                'identification_expiry_date' => $this->requestArrayValue($request, 'identification_expiry_date', $i),
                'visa_permit_type' => $this->requestArrayValue($request, 'visa_permit_type', $i),
                'identification_issuing_country' => $this->requestArrayValue($request, 'identification_issuing_country', $i),
                'identification_type' => $this->requestArrayValue($request, 'identification_type', $i, 'international_passport'),
            ];
        }

        return $passengers;
    }

    private function expandCheckoutPassengersForBooking(array $submittedPassengers, array $bookingPassengers)
    {
        $submittedPassengers = array_values($submittedPassengers);
        $bookingPassengers = array_values($bookingPassengers);

        if (empty($submittedPassengers) || empty($bookingPassengers) || count($bookingPassengers) <= count($submittedPassengers)) {
            return $submittedPassengers;
        }

        $byType = [];
        foreach ($submittedPassengers as $passenger) {
            $type = $this->passengerTypeKey($passenger['product_type'] ?? ($passenger['passenger_type'] ?? ''));
            $byType[$type][] = $passenger;
        }

        $expanded = [];
        $typeCursor = [];

        foreach ($bookingPassengers as $index => $bookingPassenger) {
            $type = $this->passengerTypeKey($bookingPassenger['type'] ?? ($bookingPassenger['product_type'] ?? ''));
            $pool = !empty($byType[$type]) ? $byType[$type] : $submittedPassengers;
            $cursor = $typeCursor[$type] ?? 0;
            $passenger = $pool[$cursor % count($pool)];
            $passenger['_booking_passenger'] = $bookingPassenger;
            $expanded[] = $passenger;
            $typeCursor[$type] = $cursor + 1;
        }

        return $expanded;
    }

    private function expandFlixCheckoutPassengers(array $submittedPassengers)
    {
        $passengers = $this->expandCheckoutPassengersForBooking(
            $submittedPassengers,
            $this->currentFlixBookingPassengerDetails()
        );

        foreach ($passengers as &$passenger) {
            $bookingPassenger = $passenger['_booking_passenger'] ?? [];

            if (!empty($bookingPassenger)) {
                $passenger['reference_id'] = $bookingPassenger['reference_id'] ?? $passenger['reference_id'];
                $passenger['product_type'] = $bookingPassenger['product_type'] ?? ($bookingPassenger['type'] ?? $passenger['product_type']);
            }

            unset($passenger['_booking_passenger']);
        }
        unset($passenger);

        return $passengers;
    }

    private function expandBlaCheckoutPassengers(array $submittedPassengers)
    {
        $passengers = $this->expandCheckoutPassengersForBooking(
            $submittedPassengers,
            $this->currentBlaBookingPassengers()
        );

        foreach ($passengers as &$passenger) {
            $bookingPassenger = $passenger['_booking_passenger'] ?? [];

            if (!empty($bookingPassenger)) {
                $passenger['passenger_id'] = $bookingPassenger['id'] ?? $passenger['passenger_id'];
                $passenger['passenger_type'] = $bookingPassenger['type'] ?? $passenger['passenger_type'];
                $passenger['passenger_disability_type'] = $bookingPassenger['disability_type'] ?? $passenger['passenger_disability_type'];
                $passenger['passenger_ref_id'] = $bookingPassenger['ref_id'] ?? $passenger['passenger_ref_id'];
                $passenger['passenger_uuid'] = $bookingPassenger['uuid'] ?? $passenger['passenger_uuid'];
            }

            unset($passenger['_booking_passenger']);
        }
        unset($passenger);

        return $passengers;
    }

    private function extractApiExceptionMessage(RequestException $exception, $fallback)
    {
        if (!$exception->hasResponse()) {
            return $fallback;
        }

        $payload = json_decode((string) $exception->getResponse()->getBody(), true);

        return $this->extractApiPayloadMessage($payload, $fallback);
    }

    private function exceptionStatusCode(RequestException $exception)
    {
        return $exception->hasResponse() ? $exception->getResponse()->getStatusCode() : 500;
    }

    private function extractApiPayloadMessage($payload, $fallback)
    {
        if (is_string($payload)) {
            $decoded = json_decode($payload, true);
            if (is_array($decoded)) {
                return $this->extractApiPayloadMessage($decoded, $fallback);
            }

            return trim($payload) !== '' ? $payload : $fallback;
        }

        if (!is_array($payload) || empty($payload)) {
            return $fallback;
        }

        foreach ([
            $payload['error']['message'] ?? null,
            $payload['errors'][0]['message'] ?? null,
            $payload['message'] ?? null,
        ] as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                $decoded = json_decode($candidate, true);
                if (is_array($decoded)) {
                    $nested = $this->extractApiPayloadMessage($decoded, '');
                    if ($nested !== '') {
                        return $nested;
                    }
                }

                return $candidate;
            }
        }

        return $fallback;
    }

    private function buildBlaSegmentsFromTrips(array $selectedTrips)
    {
        $segments = [];
        $clientRef = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);

        foreach ($selectedTrips as $selectedTrip) {
            $trip = $selectedTrip['trip'] ?? [];
            $direction = $selectedTrip['direction'] ?? 'outbound';
            $legs = $trip['legs'] ?? [];
            $validityEndDate = $trip['validity_end_date'] ?? '';

            foreach ($legs as $leg) {
                $segments[] = [
                    'client_ref' => $clientRef,
                    'origin' => $leg['from_id'] ?? '',
                    'destination' => $leg['to_id'] ?? '',
                    'direction' => $direction,
                    'service_name' => $leg['service_name'] ?? '',
                    'service_identifier' => $leg['bus_id'] ?? '',
                    'start_validity_date' => $validityEndDate,
                    'items' => [],
                    '_tariff_code' => $leg['traffic_code'] ?? ($leg['tariff_code'] ?? ''),
                ];
            }
        }

        return $segments;
    }

    private function buildBlaPassengersAndAttachSegments(array &$segments, $adultCount, $childCount)
    {
        $passengerCounter = 1;
        $passengers = [];

        foreach ([['count' => (int) $adultCount, 'type' => 'A'], ['count' => (int) $childCount, 'type' => 'Y']] as $group) {
            for ($i = 0; $i < $group['count']; $i++) {
                $passengerId = 'passenger_' . $passengerCounter++;

                foreach ($segments as &$segment) {
                    $segment['items'][] = [
                        'passenger_id' => $passengerId,
                        'tariff_code' => $segment['_tariff_code'] ?? '',
                    ];
                }
                unset($segment);

                $passengers[] = [
                    'type' => $group['type'],
                    'disability_type' => 'NH',
                    'id' => $passengerId,
                ];
            }
        }

        foreach ($segments as &$segment) {
            unset($segment['_tariff_code']);
        }
        unset($segment);

        return $passengers;
    }

    private function defaultValidationMessages()
    {
        return [
            'required' => __('bus.validation.required_field'),
            'email' => __('bus.validation.valid_email'),
            'date_format' => __('bus.validation.invalid_date'),
            'after_or_equal' => __('bus.validation.return_after_departure'),
            'different' => __('bus.validation.different_destination'),
            'array' => __('bus.validation.invalid_selection'),
            'in' => __('bus.validation.invalid_selection'),
            'integer' => __('bus.validation.invalid_passenger_count'),
            'min.numeric' => __('bus.validation.invalid_passenger_count'),
            'max.numeric' => __('bus.validation.invalid_passenger_count'),
        ];
    }

    private function searchValidationMessages()
    {
        return array_merge($this->defaultValidationMessages(), [
            'cityFrom.required' => __('bus.validation.origin_required'),
            'cityFrom.string' => __('bus.validation.origin_required'),
            'cityFromHid.required' => __('bus.validation.origin_required'),
            'cityTo.required' => __('bus.validation.destination_required'),
            'cityTo.string' => __('bus.validation.destination_required'),
            'cityToHid.required' => __('bus.validation.destination_required'),
            'cityTo.different' => __('bus.validation.different_destination'),
            'cityToHid.different' => __('bus.validation.different_destination'),
            'departureDate.required' => __('bus.validation.departure_required'),
            'departureDate.date_format' => __('bus.validation.departure_required'),
            'returnDate.required' => __('bus.validation.return_required'),
            'returnDate.date_format' => __('bus.validation.return_required'),
            'returnDate.after_or_equal' => __('bus.validation.return_after_departure'),
            'adult.required' => __('bus.validation.passenger_required'),
            'adult.min' => __('bus.validation.passenger_required'),
        ]);
    }

    private function validationAttributes()
    {
        return [
            'trip_type' => __('bus.attributes.trip_type'),
            'cityFrom' => __('bus.attributes.origin'),
            'cityFromHid' => __('bus.attributes.origin'),
            'cityTo' => __('bus.attributes.destination'),
            'cityToHid' => __('bus.attributes.destination'),
            'departureDate' => __('bus.attributes.departure_date'),
            'returnDate' => __('bus.attributes.return_date'),
            'adult' => __('bus.attributes.adults'),
            'child' => __('bus.attributes.children'),
            'children' => __('bus.attributes.children'),
            'trip_uid' => __('bus.attributes.trip'),
            'outbound_trip' => __('bus.attributes.trip'),
            'return_trip' => __('bus.attributes.trip'),
            'legs' => __('bus.attributes.route'),
            'validity_end_date' => __('bus.attributes.trip'),
            'price' => __('bus.attributes.total_price'),
            'total_price' => __('bus.attributes.total_price'),
            'currency' => __('bus.attributes.currency'),
            'reservation_token' => __('bus.attributes.booking'),
            'reservation_id' => __('bus.attributes.booking'),
            'booking_number' => __('bus.attributes.booking'),
            'booking_id' => __('bus.attributes.booking'),
            'sales_channel_code' => __('bus.attributes.sales_channel'),
            'passenger_id.*' => __('bus.attributes.passenger'),
            'segment_id.*' => __('bus.attributes.segment'),
            'firstname.*' => __('bus.attributes.first_name'),
            'lastname.*' => __('bus.attributes.last_name'),
            'birthdate.*' => __('bus.attributes.date_of_birth'),
            'email.*' => __('bus.attributes.email'),
            'phone_number.*' => __('bus.attributes.phone_number'),
            'gender.*' => __('bus.attributes.gender'),
            'citizenship.*' => __('bus.attributes.citizenship'),
            'identification_number.*' => __('bus.attributes.passport_number'),
            'identification_expiry_date.*' => __('bus.attributes.passport_expiry'),
            'visa_permit_type.*' => __('bus.attributes.visa_or_permit'),
            'identification_type.*' => __('bus.attributes.identification_type'),
            'identification_issuing_country.*' => __('bus.attributes.issuing_country'),
        ];
    }

    private function normalizeDateInput($value, $outputFormat)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return $value;
        }

        foreach (['Y-m-d', 'd.m.Y', 'd.m.y'] as $format) {
            $date = \DateTime::createFromFormat($format, $value);
            if ($date && $date->format($format) === $value) {
                return $date->format($outputFormat);
            }
        }

        $timestamp = strtotime($value);
        if ($timestamp !== false) {
            return date($outputFormat, $timestamp);
        }

        return $value;
    }

    private function wantsAjax(Request $request)
    {
        return $request->ajax() || $request->expectsJson() || $request->wantsJson();
    }

    private function startBookingSession()
    {
        $startedAt = now();
        $expiresAt = $startedAt->copy()->addMinutes(10)->timestamp;

        session()->put([
            'bus_v2_booking_started_at' => $startedAt->timestamp,
            'bus_v2_booking_expires_at' => $expiresAt,
        ]);

        $this->cacheBookingExpiry($expiresAt);
    }

    private function ensureBookingSessionInitialized()
    {
        if (!$this->hasBookingState()) {
            return;
        }

        $expiresAt = (int) session('bus_v2_booking_expires_at', 0);
        if ($expiresAt > 0) {
            $this->cacheBookingExpiry($expiresAt);
            return;
        }

        $cachedExpiresAt = (int) Cache::get($this->bookingCacheKey(), 0);
        if ($cachedExpiresAt > 0) {
            session()->put([
                'bus_v2_booking_started_at' => max(0, $cachedExpiresAt - (10 * 60)),
                'bus_v2_booking_expires_at' => $cachedExpiresAt,
            ]);
            return;
        }

        $this->startBookingSession();
    }

    private function hasBookingState()
    {
        return session()->has('bus_v2_booking') || session()->has('bus_v2_bla_booking');
    }

    private function isBookingSessionExpired()
    {
        $expiresAt = $this->bookingExpiresAt();

        return $expiresAt > 0 && now()->timestamp >= $expiresAt;
    }

    private function ensureBookingSessionActive(Request $request)
    {
        $this->ensureBookingSessionInitialized();

        if (!$this->isBookingSessionExpired()) {
            return null;
        }

        $this->clearBookingState();

        if ($this->wantsAjax($request)) {
            return response()->json([
                'success' => false,
                'expired' => true,
                'message' => __('bus.messages.session_expired'),
                'html' => $this->renderStateHtml(),
            ], 440);
        }

        return redirect()->route('bus.v2')
            ->with('message', __('bus.messages.session_expired'))
            ->with('message_type', 'warning');
    }

    private function clearState()
    {
        Cache::forget($this->bookingCacheKey());

        session()->forget([
            'bus_v2_data',
            'bus_v2_raw_results',
            'bus_v2_results',
            'bus_v2_total',
            'bus_v2_booking',
            'bus_v2_price',
            'bus_v2_bla_booking',
            'bus_v2_total_price_with_margin',
            'bus_v2_booking_started_at',
            'bus_v2_booking_expires_at',
        ]);
    }

    private function clearBookingState()
    {
        Cache::forget($this->bookingCacheKey());

        session()->forget([
            'bus_v2_booking',
            'bus_v2_price',
            'bus_v2_bla_booking',
            'bus_v2_total_price_with_margin',
            'bus_v2_booking_started_at',
            'bus_v2_booking_expires_at',
        ]);
    }

    private function clearSearchFailureState(array $busData = [])
    {
        $this->clearState();

        if (!empty($busData)) {
            session()->put([
                'bus_v2_data' => $busData,
                'bus_v2_raw_results' => [],
                'bus_v2_results' => [],
                'bus_v2_total' => 0,
            ]);
        }
    }

    private function bookingExpiresAt()
    {
        $expiresAt = (int) session('bus_v2_booking_expires_at', 0);
        if ($expiresAt > 0 || !$this->hasBookingState()) {
            return $expiresAt;
        }

        $cachedExpiresAt = (int) Cache::get($this->bookingCacheKey(), 0);
        if ($cachedExpiresAt > 0) {
            session()->put([
                'bus_v2_booking_started_at' => max(0, $cachedExpiresAt - (10 * 60)),
                'bus_v2_booking_expires_at' => $cachedExpiresAt,
            ]);
        }

        return $cachedExpiresAt;
    }

    private function cacheBookingExpiry($expiresAt)
    {
        if ((int) $expiresAt <= 0) {
            return;
        }

        Cache::put($this->bookingCacheKey(), (int) $expiresAt, now()->addMinutes(30));
    }

    private function bookingCacheKey()
    {
        return 'bus_v2_booking_expiry:' . (auth()->id() ?: 'guest') . ':' . session()->getId();
    }

    private function requestBoolean(Request $request, $key)
    {
        return in_array(strtolower((string) $request->input($key)), ['1', 'true', 'on', 'yes'], true);
    }

    private function busDesign()
    {
        $design = defined('BUS_V2_DESIGN') ? BUS_V2_DESIGN : 'standard';

        if (!defined('BUS_V2_DESIGN') && defined('BUS_V2_THEME') && BUS_V2_THEME === 'compact') {
            $design = 'desk';
        }

        return in_array($design, ['standard', 'desk'], true) ? $design : 'standard';
    }

    private function busView($view)
    {
        if ($this->busDesign() === 'desk') {
            return 'v2.service.bus.designs.desk.' . $view;
        }

        return 'v2.service.bus.' . $view;
    }

    private function busPartial($partial)
    {
        if ($this->busDesign() === 'desk') {
            return 'v2.service.bus.designs.desk.partials.' . $partial;
        }

        return 'v2.service.bus.partials.' . $partial;
    }
}
