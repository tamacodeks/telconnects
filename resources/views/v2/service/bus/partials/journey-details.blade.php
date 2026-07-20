@php
    $journeyDetails = is_array($journeyDetails ?? null) ? $journeyDetails : [];
    $travelDate = trim((string) ($journeyDetails['travel_date'] ?? ''));
    $formattedTravelDate = $travelDate !== '' && strtotime($travelDate) !== false ? date('D, d M Y', strtotime($travelDate)) : '';
    $fromName = trim((string) ($journeyDetails['from_name'] ?? ''));
    $toName = trim((string) ($journeyDetails['to_name'] ?? ''));
    $departure = trim((string) ($journeyDetails['departure'] ?? ''));
    $arrival = trim((string) ($journeyDetails['arrival'] ?? ''));
    $operator = trim((string) ($journeyDetails['bus_type'] ?? ''));
    $durationHour = (int) ($journeyDetails['duration_hour'] ?? 0);
    $durationMinutes = (int) ($journeyDetails['duration_minutes'] ?? 0);
    $durationParts = [];
    if ($durationHour > 0) {
        $durationParts[] = $durationHour . 'h';
    }
    if ($durationMinutes > 0) {
        $durationParts[] = $durationMinutes . 'm';
    }
    $durationLabel = implode(' ', $durationParts);
    $amenities = is_array($journeyDetails['amenities'] ?? null) ? $journeyDetails['amenities'] : [];
    $legs = is_array($journeyDetails['legs'] ?? null) ? $journeyDetails['legs'] : [];
    $stops = is_array($journeyDetails['stops'] ?? null) ? $journeyDetails['stops'] : [];
@endphp

@if (!empty($journeyDetails))
    <div class="bus-v2-journey-details-card">
        <div class="bus-v2-journey-details-head">
            <span class="bus-v2-badge-soft">{{ __('bus.checkout.journey_details') }}</span>
            @if ($formattedTravelDate)
                <strong>{{ $formattedTravelDate }}</strong>
            @endif
        </div>

        <ul class="bus-v2-summary-listing bus-v2-summary-listing--compact">
            @if ($formattedTravelDate)
                <li><i class="fas fa-calendar-day"></i> {{ __('bus.results.journey_date') }}: {{ $formattedTravelDate }}</li>
            @endif
            @if ($operator !== '')
                <li><i class="fas fa-bus"></i> {{ __('bus.results.carrier') }}: {{ $operator }}</li>
            @endif
            @if ($durationLabel !== '')
                <li><i class="fas fa-clock"></i> {{ __('bus.checkout.duration') }}: {{ $durationLabel }}</li>
            @endif
            @if (!empty($legs))
                <li><i class="fas fa-route"></i> {{ trans_choice('bus.results.segment_label', count($legs), ['count' => count($legs)]) }}</li>
            @elseif (!empty($stops))
                <li><i class="fas fa-location-dot"></i> {{ trans_choice('bus.results.show_stops', count($stops), ['count' => count($stops)]) }}</li>
            @else
                <li><i class="fas fa-circle-check"></i> {{ __('bus.results.direct_trip') }}</li>
            @endif
        </ul>

        @if (!empty($amenities))
            <div class="bus-v2-journey-amenities">
                <h4>{{ __('bus.checkout.amenities') }}</h4>
                <div class="bus-v2-journey-amenity-list">
                    @foreach ($amenities as $amenity)
                        <span class="bus-v2-journey-amenity">
                            @if (!empty($amenity['icon']))
                                <i class="{{ $amenity['icon'] }}"></i>
                            @endif
                            <span>{{ $amenity['label'] ?? '' }}</span>
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        @if (!empty($legs))
            <div class="bus-v2-journey-lines">
                <h4>{{ __('bus.results.route') }}</h4>
                @foreach ($legs as $leg)
                    @php
                        $routeLabel = trim((string) ($leg['from_name'] ?? ''));
                        $routeTarget = trim((string) ($leg['to_name'] ?? ''));
                        $timeLabel = trim(implode(' - ', array_filter([
                            trim((string) ($leg['departure'] ?? '')),
                            trim((string) ($leg['arrival'] ?? '')),
                        ])));
                        $metaLabel = trim(implode(' | ', array_filter([
                            trim((string) ($leg['service_name'] ?? '')),
                            trim((string) ($leg['bus_type'] ?? '')),
                        ])));
                    @endphp
                    <div class="bus-v2-journey-line-item">
                        <div class="bus-v2-journey-line-route">
                            <strong>{{ trim($routeLabel . ($routeLabel !== '' && $routeTarget !== '' ? ' -> ' : '') . $routeTarget) }}</strong>
                            @if ($timeLabel !== '')
                                <span>{{ $timeLabel }}</span>
                            @endif
                            @if ($metaLabel !== '')
                                <small>{{ $metaLabel }}</small>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @elseif ($fromName !== '' || $toName !== '' || !empty($stops))
            <div class="bus-v2-journey-lines">
                <h4>{{ __('bus.results.stops_label') }}</h4>

                @if ($fromName !== '' || $departure !== '')
                    <div class="bus-v2-journey-stop-item is-terminal">
                        <div class="bus-v2-journey-stop-copy">
                            <strong>{{ $fromName !== '' ? $fromName : __('bus.results.departure') }}</strong>
                            <span>{{ __('bus.results.departure') }}</span>
                        </div>
                        @if ($departure !== '')
                            <div class="bus-v2-journey-stop-time">{{ $departure }}</div>
                        @endif
                    </div>
                @endif

                @foreach ($stops as $stop)
                    @php
                        $stopTime = trim((string) ($stop['arrival_time'] ?? ($stop['time'] ?? '')));
                        $stopDuration = trim((string) ($stop['duration'] ?? ''));
                    @endphp
                    <div class="bus-v2-journey-stop-item">
                        <div class="bus-v2-journey-stop-copy">
                            <strong>{{ $stop['name'] ?? '' }}</strong>
                            @if ($stopDuration !== '')
                                <span>{{ $stopDuration }}</span>
                            @endif
                        </div>
                        @if ($stopTime !== '')
                            <div class="bus-v2-journey-stop-time">{{ $stopTime }}</div>
                        @endif
                    </div>
                @endforeach

                @if ($toName !== '' || $arrival !== '')
                    <div class="bus-v2-journey-stop-item is-terminal">
                        <div class="bus-v2-journey-stop-copy">
                            <strong>{{ $toName !== '' ? $toName : __('bus.results.arrival') }}</strong>
                            <span>{{ __('bus.results.arrival') }}</span>
                        </div>
                        @if ($arrival !== '')
                            <div class="bus-v2-journey-stop-time">{{ $arrival }}</div>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>
@endif
