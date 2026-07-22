<?php

namespace App\Http\Controllers\V2;

use App\Events\NotifyUser;
use app\Library\AppHelper;
use app\Library\SecurityHelper;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\PinHistory;
use App\Models\PinPrintRequest;
use App\Models\TelecomProvider;
use App\Models\Ticket;
use App\Models\TicketConversation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class PinHistoryController extends Controller
{
    private $logTitle;
    private $decipher;

    public function __construct()
    {
        parent::__construct();

        $this->logTitle = 'Pin History V2';
        $this->decipher = new SecurityHelper();
    }

    public function index(Request $request)
    {
        $today = Carbon::now()->format('Y-m-d');

        return view('v2.app.pin-history.index', [
            'page_title' => trans('v2_history.pin_history.page_title'),
            'page_data' => 'Used Pins History',
            'providers' => TelecomProvider::select('id', 'name', 'face_value')->get(),
            'from_date' => $request->input('from_date', $request->input('from', $today)),
            'to_date' => $request->input('to_date', $request->input('to', $today)),
        ]);
    }

    public function data(Request $request)
    {
        $query = $this->pinHistoryQuery();

        $this->applyDateFilter($query, $request);
        $this->applyProviderFilter($query, $request);
        $this->applySearchFilter($query, $request);

        return DataTables::of($query)
            ->addColumn('description', function ($pin) {
                $description = (string) $pin->card_desc;

                return '<span class="v2-history-description" title="' . e($description) . '">' . e(AppHelper::doTrim_text($description, 42, true)) . '</span>';
            })
            ->addColumn('status', function ($pin) {
                $pinRequest = $this->pinRequestFor($pin->id);

                if (!$pinRequest) {
                    return '<span class="v2-history-status v2-history-status-neutral">' . e($this->label('none')) . '</span>';
                }

                if ((int) $pinRequest->status === 1) {
                    return '<span class="v2-history-status v2-history-status-success">' . e($this->label('approved')) . '</span>';
                }

                return '<span class="v2-history-status v2-history-status-info">' . e($this->label('requested')) . '</span>';
            })
            ->addColumn('action', function ($pin) {
                return '<div class="v2-history-action-cell">' . implode('', $this->rowActions($pin)) . '</div>';
            })
            ->rawColumns(['action', 'status', 'description'])
            ->make(true);
    }

    public function createPrintRequest(Request $request)
    {
        $checkRequest = PinPrintRequest::where('from_user', auth()->user()->id)
            ->where('pin_id', $request->pin_id)
            ->first();

        if ($checkRequest) {
            return response()->json((object) [
                'data' => [
                    'status' => '403',
                    'message' => trans('service.requests_exists'),
                ],
            ], 200, ['Content-Type' => 'application/json']);
        }

        PinPrintRequest::insert([
            'from_user' => $request->user()->id,
            'to_user' => $request->user()->parent_id,
            'pin_id' => $request->pin_id,
            'requested_at' => date('Y-m-d H:i:s'),
        ]);

        return response()->json((object) [
            'data' => [
                'status' => '200',
                'message' => trans('service.requests_send'),
            ],
        ], 200, ['Content-Type' => 'application/json']);
    }

    public function print(Request $request, $pin_id)
    {
        $decId = $this->decipher->decrypt($pin_id);
        $checkRequest = PinPrintRequest::where('from_user', $request->user()->id)
            ->where('status', 1)
            ->where('pin_id', $decId)
            ->first();

        if (collect($checkRequest)->count() <= 0) {
            AppHelper::logger('warning', $this->logTitle, 'Unable to print pin id ' . $decId, $request->all());

            return redirect('cc-pin-history-v2')
                ->with('message', trans('myservice.unable_to_print'))
                ->with('message_type', 'warning');
        }

        $pinInfo = $this->pinCardQuery($decId)->first();

        if (!$pinInfo) {
            abort(404);
        }

        return view('myservice.calling-cards.history.print_page', [
            'page_title' => 'Print ' . $pinInfo->name,
            'card' => $pinInfo,
            'card_name' => $pinInfo->name,
            'card_id' => $pinInfo->id,
            'provider' => $this->providerForCard($pinInfo->cc_id),
        ]);
    }

    public function contact(Request $request, $pin_id)
    {
        if (!$request->ajax()) {
            return redirect()->route('cc-pin-history.v2', ['contact' => $pin_id]);
        }

        $decId = $this->decipher->decrypt($pin_id);
        $pinInfo = $this->pinCardQuery($decId)->first();

        if (!$pinInfo) {
            abort(404);
        }

        return view('v2.app.pin-history.contact', [
            'card' => $pinInfo,
            'card_name' => $pinInfo->name,
            'card_id' => $pinInfo->id,
            'pin_id' => $pin_id,
            'provider' => $this->providerForCard($pinInfo->cc_id),
            'contactActionUrl' => url('cc-pin-history-v2/contact'),
        ]);
    }

    public function sendContact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pin_id' => 'required',
            'type' => 'required',
            'message' => 'required',
        ]);

        if ($validator->fails()) {
            AppHelper::logger('warning', $this->logTitle, 'Validation failed', $request->all());
            $html = AppHelper::create_error_bag($validator);

            return redirect()
                ->back()
                ->with('message', $html)
                ->with('message_type', 'warning');
        }

        $decPinId = $this->decipher->decrypt($request->pin_id);
        $ticket = Ticket::where('from_user', auth()->user()->id)
            ->where('to_user', auth()->user()->parent_id)
            ->where('pin_id', $decPinId)
            ->first();

        if ($ticket) {
            $encTicketId = $this->decipher->encrypt($ticket->id);
            AppHelper::logger('info', $this->logTitle, 'Ticket ID ' . $ticket->id . ' already exists');

            return redirect('ticket/conversation/' . $encTicketId);
        }

        try {
            DB::beginTransaction();

            $forwardId = null;
            if ($request->fwdStatus === 'true') {
                $forwardId = optional(Ticket::where('to_user', auth()->user()->id)
                    ->where('pin_id', $decPinId)
                    ->first())->id;
            }

            $ticketId = Ticket::insertGetId([
                'from_user' => auth()->user()->id,
                'to_user' => auth()->user()->parent_id,
                'type' => $request->type,
                'status' => 0,
                'fwd_id' => $forwardId,
                'pin_id' => $decPinId,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => auth()->user()->id,
            ]);

            TicketConversation::insert([
                'ticket_id' => $ticketId,
                'user_id' => auth()->user()->id,
                'message' => $request->message,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => auth()->user()->id,
            ]);

            AppHelper::logger('success', $this->logTitle, 'Ticket created successfully for user ' . auth()->user()->username);

            $notificationId = Notification::insertGetId([
                'date' => date('Y-m-d H:i:s'),
                'user_id' => auth()->user()->parent_id,
                'type' => 'enquiry',
                'title' => auth()->user()->username . ' ' . trans('common.send_enquiry'),
                'message' => $request->message,
                'url' => secure_url('ticket/conversation/' . $this->decipher->encrypt($ticketId)),
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => auth()->user()->id,
            ]);

            event(new NotifyUser(Notification::find($notificationId)));

            DB::commit();

            return redirect()
                ->back()
                ->with('message', trans('myservice.ticket_created'))
                ->with('message_type', 'success');
        } catch (\Exception $exception) {
            DB::rollBack();
            AppHelper::logger('warning', $this->logTitle, 'Exception while create ticket => ' . $exception->getMessage());
            Log::warning($this->logTitle . ' Exception => ' . $exception->getMessage());

            return redirect()
                ->back()
                ->with('message', trans('myservice.ticket_exception'))
                ->with('message_type', 'warning');
        }
    }

    private function pinHistoryQuery()
    {
        return PinHistory::join('calling_cards', 'calling_cards.id', 'pin_histories.cc_id')
            ->join('telecom_providers', 'telecom_providers.id', 'calling_cards.telecom_provider_id')
            ->where('used_by', auth()->user()->id)
            ->select([
                'pin_histories.id',
                'pin_histories.name',
                'pin_histories.pin',
                'pin_histories.serial',
                'pin_histories.date',
                'calling_cards.telecom_provider_id',
                'calling_cards.description as card_desc',
            ]);
    }

    private function applyDateFilter($query, Request $request)
    {
        if (empty($request->input('from_date')) && empty($request->input('to_date'))) {
            $todayDate = date('Y-m-d');

            switch (DEFAULT_RECORD_METHOD) {
                case 1:
                    $query->whereBetween('pin_histories.date', [$todayDate . ' 00:00:00', $todayDate . ' 23:59:59']);
                    break;
                case 2:
                    $query->whereMonth('pin_histories.date', date('m'));
                    break;
                case 3:
                    $query->whereBetween('pin_histories.date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    break;
            }

            return;
        }

        $fromDate = $request->input('from_date') . ' 00:00:00';
        $toDate = $request->input('to_date') . ' 23:59:59';
        $query->whereBetween('pin_histories.date', [$fromDate, $toDate]);
    }

    private function applyProviderFilter($query, Request $request)
    {
        if (!empty($request->input('telecom_provider_id'))) {
            $query->whereIn('calling_cards.telecom_provider_id', (array) $request->input('telecom_provider_id'));
        }
    }

    private function applySearchFilter($query, Request $request)
    {
        if (empty($request->input('query'))) {
            return;
        }

        $search = $request->input('query');

        $query->where(function ($q) use ($search) {
            $q->where('pin_histories.name', 'like', "%{$search}%")
                ->orWhere('pin_histories.pin', 'like', "%{$search}%")
                ->orWhere('pin_histories.serial', 'like', "%{$search}%")
                ->orWhere('calling_cards.description', 'like', "%{$search}%")
                ->orWhere('telecom_providers.name', 'like', "%{$search}%");
        });
    }

    private function rowActions($pin)
    {
        $actions = [];
        $pinRequest = $this->pinRequestFor($pin->id);
        $encryptedPin = $this->decipher->encrypt($pin->id);

        if ((int) auth()->user()->pin_print_again === 1) {
            if ($pinRequest && (int) $pinRequest->status === 1) {
                $actions[] = '<a class="v2-history-action-btn v2-history-action-primary" href="' . e(url('cc-pin-history-v2/print/' . $encryptedPin)) . '" target="_blank" rel="noopener"><i class="fa fa-print" aria-hidden="true"></i><span>' . e($this->label('print_pin')) . '</span></a>';
            } elseif (!$pinRequest) {
                $actions[] = '<button type="button" class="v2-history-action-btn v2-history-action-primary" data-v2-pin-print-request data-pin-id="' . e($pin->id) . '"><i class="fa fa-paper-plane" aria-hidden="true"></i><span>' . e($this->label('send_request')) . '</span></button>';
            }
        }

        $actions[] = '<button type="button" class="v2-history-action-btn v2-history-action-soft" data-v2-pin-enquiry data-url="' . e(url('cc-pin-history-v2/contact/' . $encryptedPin)) . '" data-title="' . e($this->enquiryTitle($pin->name)) . '"><i class="fa fa-comments" aria-hidden="true"></i><span>' . e($this->label('enquire_now')) . '</span></button>';

        return $actions;
    }

    private function pinRequestFor($pinId)
    {
        return PinPrintRequest::where('pin_id', $pinId)
            ->where('from_user', auth()->user()->id)
            ->where('to_user', auth()->user()->parent_id)
            ->first();
    }

    private function pinCardQuery($pinId)
    {
        return PinHistory::join('calling_cards', 'calling_cards.id', 'pin_histories.cc_id')
            ->where('pin_histories.id', $pinId)
            ->where('pin_histories.used_by', auth()->user()->id)
            ->select([
                'calling_cards.id as cc_id',
                'calling_cards.name',
                'calling_cards.description',
                'calling_cards.validity',
                'calling_cards.access_number',
                'calling_cards.face_value',
                'calling_cards.comment_1',
                'calling_cards.comment_2',
                'pin_histories.id as ccp_id',
                'pin_histories.pin',
                'pin_histories.serial',
                'pin_histories.date as updated_at',
            ]);
    }

    private function providerForCard($cardId)
    {
        return TelecomProvider::join('calling_cards', 'calling_cards.telecom_provider_id', 'telecom_providers.id')
            ->where('calling_cards.id', $cardId)
            ->select('telecom_providers.*')
            ->first();
    }

    private function enquiryTitle($cardName)
    {
        return (app()->getLocale() === 'fr' ? 'Demande pour ' : 'Enquiry for ') . $cardName;
    }

    private function label($key)
    {
        $locale = app()->getLocale() === 'fr' ? 'fr' : 'en';
        $labels = [
            'approved' => ['en' => 'Approved', 'fr' => 'Approuve'],
            'requested' => ['en' => 'Requested', 'fr' => 'Demande'],
            'none' => ['en' => 'None', 'fr' => 'Aucun'],
            'print_pin' => ['en' => 'Print PIN', 'fr' => 'Imprimer PIN'],
            'send_request' => ['en' => 'Request print', 'fr' => 'Demander impression'],
            'enquire_now' => ['en' => 'Enquire', 'fr' => 'Demande'],
        ];

        return isset($labels[$key][$locale]) ? $labels[$key][$locale] : $key;
    }
}
