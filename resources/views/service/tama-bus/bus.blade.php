{{--Seacrh Buses--}}


@if (session('bus_results'))
        <div class="container-fluid bus_details" id="bookContainer">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-body">
                                <form id="sortForm" class="form-horizontal" action="{{ secure_url('flix-bus/search') }}" method="POST">
                                    @csrf
                                <div class="row">
                                    <input type="hidden" name="cityFrom" id="cityFrom" value="{{ session('bus_data.from_name', '') }}">
                                    <input type="hidden" name="cityTo" id="cityTo" value="{{ session('bus_data.to_name', '') }}">
                                    <input type="hidden" name="geolatfrom" id="geolatfrom" value="{{ session('bus_data.geolatfrom', '') }}">
                                    <input type="hidden" name="geolonfrom" id="geolonfrom" value="{{ session('bus_data.geolonfrom', '') }}">
                                    <input type="hidden" name="cityFromHid" id="cityFromHid" value="{{ session('bus_data.from_id', '') }}">
                                    <input type="hidden" name="cityToHid" id="cityToHid" value="{{ session('bus_data.to_id', '') }}">
                                    <input type="hidden" name="geolatto" id="geolatto" value="{{ session('bus_data.geolatto', '') }}">
                                    <input type="hidden" name="geolonto" id="geolonto" value="{{ session('bus_data.geolonto', '') }}">
                                    <input type="hidden" name="departureDate" id="departureDate"  value="{{ session('bus_data.departure', '') }}">
                                    <input type="hidden" name="passengers" id="passengers" value="{{ session('bus_data.passengers', '') }}">
                                    <input type="hidden" name="adult" id="adult" value="{{ session('bus_data.adult', '') }}">
                                    <input type="hidden" name="child" id="child" value="{{ session('bus_data.child', '') }}">
                                    <div class="col-md-4 text-left">
                                        <select class="form-control" name="sort_by" id="sort_by">
                                            <option value="">{{ trans('service.range_filters') }}</option>
                                            <option value="1" {{ session('bus_data.sort_by') == '1' ? 'selected' : '' }}>
                                                {{ trans('service.departure_ear') }}
                                            </option>
                                            <option value="2" {{ session('bus_data.sort_by') == '2' ? 'selected' : '' }}>
                                                {{ trans('service.price_low') }}
                                            </option>
                                            <option value="3" {{ session('bus_data.sort_by') == '3' ? 'selected' : '' }}>
                                                {{ trans('service.durations_short') }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <select class="form-control" name="sort_by_bus" id="sort_by_bus">
                                            <option value="">{{ trans('service.bus_filters') }}</option>
                                            <option value="4" {{ session('bus_data.sort_by_bus') == '4' ? 'selected' : '' }}>{{ trans('service.flixbus') }}</option>
                                            <option value="5" {{ session('bus_data.sort_by_bus') == '5' ? 'selected' : '' }}>{{ trans('service.blabus') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <p>{{ trans('service.total_buses') }}: {{ session('total_bus') }}</p>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

{{--Busses Fetch--}}
<div class="container-fluid bus_details" id="bookContainer">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="col-md-1"></div>
                <div class="col-md-10">
                    @if (session('bus_results'))
                        @foreach (session('bus_results') as $bus)
                            {{--<div class="pan col-md-12 mb-4">--}}
                                <div class="panel panel-default send-tama-panel products animated-panel">
                                    <div class="panel-body">
                                        <div id="header">
                                            <div class="row align-items-center justify-content-between">
                                                <div class="col-md-4 text-center">
                                                    <label>{{ trans('service.from') }}</label>
                                                    <h5 class="product-name fixed-height">{{ $bus['from_name'] }}</h5>
                                                    <h4 class="departure-arrival-time">{{ $bus['departure'] }}</h4>
                                                </div>
                                                    <div class="col-md-1 text-center arrow-icon">
                                                        <i class="fas fa-arrow-right"></i>
                                                    </div>
                                                    <div class="col-md-4 text-center">
                                                        <label>{{ trans('service.duration') }}</label>
                                                        <h4 class="departure-arrival-time">{{ $bus['duration_hour'] }} {{ trans('service.hrs') }} : {{ $bus['duration_minutes'] }}  {{ trans('service.mins') }}</h4>
                                                    </div>
                                                    <div class="col-md-1 text-center arrow-icon">
                                                        <i class="fas fa-arrow-right"></i>
                                                    </div>
                                                <div class="col-md-4 text-center">
                                                    <label>{{ trans('service.to') }}</label>
                                                    <h5 class="product-name fixed-height">{{ $bus['to_name'] }}</h5>
                                                    <h4 class="departure-arrival-time">{{ $bus['arrival'] }}</h4>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="panel-footer">
                                        <div class="row">
                                            @if (number_format($bus['total_price'], 2) == '0.00')
                                                <div class="col-md-12">
                                                    <p class="text-danger">{{ trans('service.message') }}</p>
                                                </div>
                                            @else
                                                <div class="col-md-3">
                                                    <h5 style="display: inline;">{{ trans('service.available_seats') }} - </h5>
                                                    <span class="label label-danger" style="background-color: green; color: white; display: inline-block;">{{ $bus['available_seats'] }}</span>
                                                </div>

                                                <div class="col-md-3">
                                                    <h4><span class="fa fa-euro-sign"> {{ number_format($bus['total_price'], 2) }}</span> </h4>
                                                </div>
                                                <div class="col-md-3">
                                                    @if (!empty($bus['bus_type']) && (strpos($bus['bus_type'], 'BlaBlaCar') !== false || $bus['bus_type'] == 'Mixed'))
                                                        <div class="row justify-content-center mb-4">
                                                            <div class="col-md-4 text-center">
                                                                <img src="{{ secure_asset('images/search-bla.png') }}" alt="Logo" class="img-fluid booking-logo">
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="row justify-content-center mb-4">
                                                            <div class="col-md-4 text-center">
                                                                <img src="{{ secure_asset('images/search-flix.png') }}" alt="Logo" class="img-fluid booking-logo">
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="col-md-3">
                                                    @if (!empty($bus['bus_type']) && (strpos($bus['bus_type'], 'BlaBlaCar') !== false || $bus['bus_type'] == 'Mixed'))
                                                        <form action="{{ secure_url('flix-bus/create_reservation_blabus') }}" method="POST" id="formblabus">
                                                            @csrf
                                                            <input type="hidden" name="from_id" value="{{ $bus['from_id'] }}">
                                                            <input type="hidden" name="from_name" value="{{ $bus['from_name'] }}">
                                                            <input type="hidden" name="to_id" value="{{ $bus['to_id'] }}">
                                                            <input type="hidden" name="to_name" value="{{ $bus['to_name'] }}">
                                                            <input type="hidden" name="bus_type" value="{{ $bus['bus_type'] }}">
                                                            <input type="hidden" name="adult" value="{{ $bus['adult'] }}">
                                                            <input type="hidden" name="children" value="{{ $bus['children'] }}">
                                                            <input type="hidden" name="bikes" value="{{ $bus['bikes'] }}">
                                                            <input type="hidden" name="currency" value="{{ $bus['currency'] }}">
                                                            <input type="hidden" name="departure" value="{{ $bus['departure'] }}">
                                                            <input type="hidden" name="arrival" value="{{ $bus['arrival'] }}">
                                                            <input type="hidden" name="total_selected_seats" value="{{ $bus['total_selected_seats'] }}">
                                                            <input type="hidden" name="total_price" value="{{ $bus['total_price'] }}">
                                                            <input type="hidden" name="available_seats" value="{{ $bus['available_seats'] }}">
                                                            <input type="hidden" name="discount" value="{{ $bus['discount'] }}">
                                                            <input type="hidden" name="duration_hour" value="{{ $bus['duration_hour'] }}">
                                                            <input type="hidden" name="duration_minutes" value="{{ $bus['duration_minutes'] }}">
                                                            <input type="hidden" name="ref_id" value="{{ $bus['ref_id'] }}">
                                                            <input type="hidden" name="passenger_id" value="{{ $bus['passenger_id'] }}">
                                                            <input type="hidden" name="validity_end_date" value="{{ $bus['validity_end_date'] }}">
                                                            <input type="hidden" name="product_code" value="{{ $bus['product_code'] }}">
                                                            @foreach ($bus['legs'] as $index => $leg)
                                                                <input type="hidden" name="legs[{{ $index }}][from_id]" value="{{ $leg['from_id'] }}">
                                                                <input type="hidden" name="legs[{{ $index }}][from_name]" value="{{ $leg['from_name'] }}">
                                                                <input type="hidden" name="legs[{{ $index }}][to_id]" value="{{ $leg['to_id'] }}">
                                                                <input type="hidden" name="legs[{{ $index }}][to_name]" value="{{ $leg['to_name'] }}">
                                                                <input type="hidden" name="legs[{{ $index }}][departure]" value="{{ $leg['departure'] }}">
                                                                <input type="hidden" name="legs[{{ $index }}][arrival]" value="{{ $leg['arrival'] }}">
                                                                <input type="hidden" name="legs[{{ $index }}][bus_id]" value="{{ $leg['bus_id'] }}">
                                                                <input type="hidden" name="legs[{{ $index }}][service_name]" value="{{ $leg['service_name'] }}">
                                                                <input type="hidden" name="legs[{{ $index }}][bus_uid]" value="{{ $leg['bus_uid'] }}">
                                                                <input type="hidden" name="legs[{{ $index }}][bus_type]" value="{{ $leg['bus_type'] }}">
                                                                <input type="hidden" name="legs[{{ $index }}][tariff_code]" value="{{ $leg['traffic_code'] }}">
                                                            @endforeach

                                                            <button type="submit" style="background-color: #f1715d;" class="btn btn-success animated-button" id="blabus">
                                                                {{ trans('service.reserve') }} {{ $bus['total_selected_seats'] }} {{ trans('service.seat') }}
                                                            </button>
                                                        </form>

                                                    @else
                                                        <form action="{{ secure_url('flix-bus/create_reservations_bus') }}" method="POST" id="formflixbus">
                                                            @csrf
                                                            <input type="hidden" name="trip_uid" value="{{ $bus['bus_uid'] }}">
                                                            <input type="hidden" name="adult" value="{{ $bus['adult'] }}">
                                                            <input type="hidden" name="children" value="{{ $bus['children'] }}">
                                                            <input type="hidden" name="bikes" value="{{ $bus['bikes'] }}">
                                                            <input type="hidden" name="currency" value="{{ $bus['currency'] }}">
                                                            <input type="hidden" name="total_price" value="{{ $bus['total_price'] }}">
                                                            <button type="submit" style="background-color: #73d700;" class="btn btn-success animated-button" id="flixbus">
                                                                {{ trans('service.reserve') }} {{ $bus['total_selected_seats'] }} {{ trans('service.seat') }}
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        {{--<br>--}}
                                        {{--<div class="row">--}}
                                            {{----}}
                                        {{--</div>--}}
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                            {{--</div>--}}
                        @endforeach
                    @endif
                </div>
                <div class="col-md-1"></div>
            </div>
        </div>
    </div>
</div>
{{--Error--}}
@if ($errors->any())
    <div class="container-fluid bus_details" id="bookContainer">
        <div class="row justify-content-center">
            <div class="col-md-2"></div>
                <div class="col-md-8"> <!-- Adjusted to center the content better -->
                    <div class="panel panel-default text-center"> <!-- Added text-center to panel -->
                        <div class="panel-body">
                            <img src="{{ secure_asset('images/no-bus.jpg') }}" class="nobus img-fluid mx-auto d-block">
                            <div class="alert alert-danger mt-3">
                                <ul class="list-unstyled text-center"> <!-- Ensure text is centered -->
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <div class="col-md-2"></div>
        </div>
    </div>
@endif
