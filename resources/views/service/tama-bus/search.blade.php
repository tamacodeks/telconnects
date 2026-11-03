
@if (!session('bla_booking') && !session('booking'))
    <div class="container-fluid" id="bookContainer">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="panel panel-default booking-panel">
                <div class="panel-body">
                    <!-- Add a Logo at the top -->
                    <div class="row justify-content-center mb-4">
                        <div class="col-md-4 text-center">
                            <img src="{{ secure_asset('images/logo-bus.png') }}" alt="Logo" class="img-fluid booking-logo">
                        </div>
                    </div>
                    <div class="booking-fields">
                        <form id="frmBusBook" class="form-horizontal" action="{{ secure_url('bus/search') }}" method="POST" onsubmit="return validateForm()">
                            @csrf
                            <div class="row">
                                <!-- City From -->
                                <div class="col-md-3">
                                    <div class="inputWithIcon">
                                        <input type="text" class="booking-input" placeholder="{{ trans('service.lyon') }}"
                                               name="cityFrom" id="cityFrom"
                                               value="{{ session('bus_data.from_name', '') }}" required>
                                        <input type="hidden" name="cityFromHid" id="cityFromHid" value="{{ session('bus_data.from_id', '') }}">
                                        <input type="hidden" name="geolatfrom" id="geolatfrom" value="{{ session('bus_data.geolatfrom', '') }}">
                                        <input type="hidden" name="geolonfrom" id="geolonfrom" value="{{ session('bus_data.geolonfrom', '') }}">
                                        <i class="fas fa-map-marker-alt fa-lg fa-fw icon"></i>
                                        <span class="error-message text-danger" id="errorCityFrom"></span>
                                    </div>
                                </div>

                                <!-- City To -->
                                <div class="col-md-3">
                                    <div class="inputWithIcon">
                                        <input type="text" class="booking-input" placeholder="{{ trans('service.paris') }}"
                                               name="cityTo" id="cityTo"
                                               value="{{ session('bus_data.to_name', '') }}" required>
                                        <input type="hidden" name="cityToHid" id="cityToHid" value="{{ session('bus_data.to_id', '') }}">
                                        <input type="hidden" name="geolatto" id="geolatto" value="{{ session('bus_data.geolatto', '') }}">
                                        <input type="hidden" name="geolonto" id="geolonto" value="{{ session('bus_data.geolonto', '') }}">
                                        <i class="fas fa-map-marker-alt fa-lg fa-fw icon"></i>
                                        <span class="error-message text-danger" id="errorCityTo"></span>
                                    </div>
                                </div>

                                <!-- Departure Date -->
                                <div class="col-md-3">
                                    <div class="inputWithIcon">
                                        <input type="text" class="date booking-input"
                                               name="departureDate" id="departureDate"
                                               placeholder="Departure" value="{{ session('bus_data.departure', date('Y-m-d')) }}" readonly required>
                                        <i class="far fa-calendar-alt fa-lg fa-fw icon"></i>
                                        <span class="error-message text-danger" id="errorDepartureDate"></span>
                                    </div>
                                </div>

                                <!-- Passengers -->
                                <div class="col-md-3">
                                    <div class="inputWithIcon">
                                        <div class="popover-markup">
                                            <div class="trigger form-group-icon-left">
                                                <input type="text" name="passengers" id="passengers" placeholder="{{ trans('service.passengers') }}"
                                                       class="booking-input" readonly required
                                                       value="{{ session('bus_data.passengers', '') }}">
                                                <i class="far fa-user fa-lg fa-fw icon"></i>
                                                <span class="error-message text-danger" id="errorPassengers"></span>
                                            </div>
                                            <div class="content hide">
                                                <!-- Adults -->
                                                <div class="form-group row">
                                                    <label class="control-label col-md-8"><strong>{{ trans('service.adult') }}</strong><br>
                                                        <span class="text-muted sub-text">{{ trans('service.adult_age') }}</span></label>
                                                    <div class="input-group number-spinner col-md-4">
                                                        <span class="input-group-btn">
                                                            <a class="btn btn-default" data-choser="adult" data-dir="dwn">
                                                                <span class="glyphicon glyphicon-minus"></span>
                                                            </a>
                                                        </span>
                                                        <input type="text" name="adult" id="adult"
                                                               class="form-control text-center" max="9" min="0"
                                                               value="{{ session('bus_data.adult', '') }}">
                                                        <span class="input-group-btn">
                                                            <a class="btn btn-default" data-choser="adult" data-dir="up">
                                                                <span class="glyphicon glyphicon-plus"></span>
                                                            </a>
                                                        </span>
                                                    </div>
                                                </div>
                                                <!-- Children -->
                                                <div class="form-group row">
                                                    <label class="control-label col-md-8"><strong>{{ trans('service.children') }}</strong><br>
                                                        <span class="text-muted sub-text">{{ trans('service.children_age') }}</span></label>
                                                    <div class="input-group number-spinner col-md-4">
                                                        <span class="input-group-btn">
                                                            <a class="btn btn-default" data-choser="child" data-dir="dwn">
                                                                <span class="glyphicon glyphicon-minus"></span>
                                                            </a>
                                                        </span>
                                                        <input type="text" name="child" id="child"
                                                               class="form-control text-center" max="9" min="0"
                                                               value="{{ session('bus_data.children', '') }}">
                                                        <span class="input-group-btn">
                                                            <a class="btn btn-default" data-choser="child" data-dir="up">
                                                                <span class="glyphicon glyphicon-plus"></span>
                                                            </a>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Search Button -->
                            <div class="text-right mt-3">
                                <button type="submit" class="btn btn-primary animated-button" id="frmBusBookbtn">{{ trans('service.search') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
<div class="container-fluid hide" id="detectAnime">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="form-group">
                        <div class="col-md-12">
                            <img src="{{ asset('images/bus-truning.gif') }}" class="center-block img-responsive">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
