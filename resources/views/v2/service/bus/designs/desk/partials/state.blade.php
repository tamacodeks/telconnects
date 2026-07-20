@php
    $tripType = $searchData['trip_type'] ?? 'one_way';
    $hasRoundTripResults = $tripType === 'round_trip' && is_array($results) && array_key_exists('outbound', $results) && array_key_exists('inbound', $results);
    $hasResults = $hasRoundTripResults
        ? (!empty($results['outbound']) || !empty($results['inbound']))
        : !empty($results);
    $hasFlixBooking = is_array($flixBooking)
        && (
            !empty($flixBooking['reservation_token'])
            || !empty($flixBooking[0]['reservation_token'])
        );
    $blaReservation = is_array($blaBooking) ? ($blaBooking['data']['booking'] ?? []) : [];
    $blaSegments = is_array($blaReservation)
        ? array_merge($blaReservation['outbound_booking_tariff_segments'] ?? [], $blaReservation['inbound_booking_tariff_segments'] ?? [])
        : [];
    $hasBlaBooking = !empty($blaSegments);
@endphp

@if ($hasFlixBooking)
    @include('v2.service.bus.designs.desk.partials.flix-booking')
@elseif ($hasBlaBooking)
    @include('v2.service.bus.designs.desk.partials.bla-booking')
@elseif ($hasResults)
    @if ($hasRoundTripResults)
        @include('v2.service.bus.designs.desk.partials.roundtrip-results')
    @else
        @include('v2.service.bus.designs.desk.partials.results')
    @endif
@else
    @include('v2.service.bus.designs.desk.partials.empty-state')
@endif
