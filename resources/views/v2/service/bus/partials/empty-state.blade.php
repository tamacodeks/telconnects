<section class="bus-v2-panel bus-v2-empty">
    <div class="bus-v2-empty-card">
        <span class="bus-v2-empty-icon">
            <i class="fas fa-route"></i>
        </span>
        <h2>{{ __('bus.results.no_results_title') }}</h2>
        <p>{{ __('bus.results.no_results_text') }}</p>
        <div class="bus-v2-empty-actions">
            <button type="button" class="bus-v2-button bus-v2-button--ghost" data-bus-v2-scroll-search>
                <i class="fas fa-sliders-h"></i>
                {{ __('bus.results.modify_search') }}
            </button>
            <button type="button" class="bus-v2-button bus-v2-button--soft" data-bus-v2-reset>
                <i class="fas fa-rotate-left"></i>
                {{ __('bus.results.start_new_search') }}
            </button>
        </div>
    </div>
</section>
