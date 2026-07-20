<section class="bus-v2-panel bus-v2-results-panel bus-v2-results-loading" aria-live="polite" aria-busy="true">
    <div class="bus-v2-results-header bus-v2-results-header--loading">
        <div class="bus-v2-loading-journey">
            <div class="bus-v2-loading-journey-track">
                <span class="bus-v2-loading-journey-bus"><i class="fas fa-bus"></i></span>
            </div>
        </div>
        <div class="bus-v2-loading-journey-copy">
            <span class="bus-v2-results-kicker">{{ __('bus.search.searching') }}</span>
            <h2>{{ __('bus.search.finding_routes') }}</h2>
            <p>{{ __('bus.search.loading_live_results') }}</p>
            <div class="bus-v2-results-meta-row">
                <span class="bus-v2-results-meta-chip" data-bus-v2-loading-route>{{ __('bus.results.route') }}</span>
                <span class="bus-v2-results-meta-chip" data-bus-v2-loading-date>{{ __('bus.results.journey_date') }}</span>
                <span class="bus-v2-results-meta-chip" data-bus-v2-loading-passengers>{{ __('bus.results.travellers') }}</span>
            </div>
        </div>
    </div>

    <div class="bus-v2-filter-bar bus-v2-filter-bar--results bus-v2-filter-bar--loading">
        <span class="bus-v2-loading-block bus-v2-loading-block--pill"></span>
        <span class="bus-v2-loading-block bus-v2-loading-block--pill"></span>
        <span class="bus-v2-loading-block bus-v2-loading-block--control"></span>
        <span class="bus-v2-loading-block bus-v2-loading-block--control"></span>
        <span class="bus-v2-loading-block bus-v2-loading-block--route"></span>
    </div>

    <div class="bus-v2-result-list bus-v2-result-list--loading">
        @for ($i = 0; $i < 3; $i++)
            <article class="bus-v2-trip-card bus-v2-trip-card--skeleton" aria-hidden="true">
                <div class="bus-v2-trip-card-top">
                    <div class="bus-v2-trip-badges">
                        <span class="bus-v2-loading-block bus-v2-loading-block--brand"></span>
                        <span class="bus-v2-loading-block bus-v2-loading-block--chip"></span>
                    </div>
                    <div class="bus-v2-trip-supporting">
                        <span class="bus-v2-loading-block bus-v2-loading-block--chip"></span>
                        <span class="bus-v2-loading-block bus-v2-loading-block--chip"></span>
                    </div>
                </div>

                <div class="bus-v2-trip-card-body">
                    <div class="bus-v2-trip-main">
                        <div class="bus-v2-trip-stop">
                            <span class="bus-v2-loading-block bus-v2-loading-block--label"></span>
                            <span class="bus-v2-loading-block bus-v2-loading-block--time"></span>
                            <span class="bus-v2-loading-block bus-v2-loading-block--station"></span>
                        </div>

                        <div class="bus-v2-trip-timeline">
                            <div class="bus-v2-trip-track" aria-hidden="true">
                                <span class="bus-v2-trip-track-dot"></span>
                                <span class="bus-v2-trip-track-line"></span>
                                <span class="bus-v2-trip-track-bus"><i class="fas fa-bus"></i></span>
                                <span class="bus-v2-trip-track-line"></span>
                                <span class="bus-v2-trip-track-dot"></span>
                            </div>
                            <span class="bus-v2-loading-block bus-v2-loading-block--duration"></span>
                            <span class="bus-v2-loading-block bus-v2-loading-block--meta"></span>
                        </div>

                        <div class="bus-v2-trip-stop bus-v2-trip-stop--arrival">
                            <span class="bus-v2-loading-block bus-v2-loading-block--label"></span>
                            <span class="bus-v2-loading-block bus-v2-loading-block--time"></span>
                            <span class="bus-v2-loading-block bus-v2-loading-block--station"></span>
                        </div>
                    </div>

                    <div class="bus-v2-trip-side">
                        <div class="bus-v2-trip-price">
                            <span class="bus-v2-loading-block bus-v2-loading-block--label"></span>
                            <span class="bus-v2-loading-block bus-v2-loading-block--price"></span>
                        </div>
                        <div class="bus-v2-trip-action">
                            <span class="bus-v2-loading-block bus-v2-loading-block--button"></span>
                        </div>
                    </div>
                </div>
            </article>
        @endfor
    </div>
</section>
