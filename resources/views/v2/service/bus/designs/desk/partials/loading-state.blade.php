<section class="bus-v2-panel bus-v2-results-panel bus-v2-results-loading bus-v2-desk-loading" aria-live="polite" aria-busy="true">
    <div class="bus-v2-desk-loading-head">
        <span class="bus-v2-desk-loading-icon">
            <i class="fas fa-bus"></i>
        </span>
        <div>
            <span class="bus-v2-results-kicker">{{ __('bus.search.searching') }}</span>
            <h2>{{ __('bus.search.finding_routes') }}</h2>
            <p>{{ __('bus.search.loading_live_results') }}</p>
        </div>
    </div>

    <div class="bus-v2-results-meta-row">
        <span class="bus-v2-results-meta-chip" data-bus-v2-loading-route>{{ __('bus.results.route') }}</span>
        <span class="bus-v2-results-meta-chip" data-bus-v2-loading-date>{{ __('bus.results.journey_date') }}</span>
        <span class="bus-v2-results-meta-chip" data-bus-v2-loading-passengers>{{ __('bus.results.travellers') }}</span>
    </div>

    <div class="bus-v2-desk-skeleton-list">
        @for ($i = 0; $i < 4; $i++)
            <article class="bus-v2-trip-card bus-v2-desk-trip bus-v2-trip-card--skeleton" aria-hidden="true">
                <span class="bus-v2-loading-block bus-v2-loading-block--brand"></span>
                <span class="bus-v2-loading-block bus-v2-loading-block--time"></span>
                <span class="bus-v2-loading-block bus-v2-loading-block--station"></span>
                <span class="bus-v2-loading-block bus-v2-loading-block--duration"></span>
                <span class="bus-v2-loading-block bus-v2-loading-block--price"></span>
                <span class="bus-v2-loading-block bus-v2-loading-block--button"></span>
            </article>
        @endfor
    </div>
</section>
