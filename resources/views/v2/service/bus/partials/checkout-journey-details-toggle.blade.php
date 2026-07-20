@if (!empty($journeyDetailsList))
    <details class="bco-sidebar-details">
        <summary>
            <span class="bco-sidebar-details-title">
                <i class="fas fa-route"></i>
                {{ __('bus.checkout.journey_details') }}
            </span>
            <i class="fas fa-chevron-down bco-sidebar-details-chevron"></i>
        </summary>
        <div class="bco-sidebar-details-body">
            @foreach ($journeyDetailsList as $journeyDetail)
                @include('v2.service.bus.partials.journey-details', ['journeyDetails' => $journeyDetail])
            @endforeach
        </div>
    </details>
@endif
