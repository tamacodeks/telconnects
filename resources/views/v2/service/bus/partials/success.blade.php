<section class="bus-v2-panel bus-v2-success">
    <div class="bus-v2-success-mark">
        <i class="fas fa-check"></i>
    </div>
    <h2>{{ __('bus.success.title', ['provider' => $provider]) }}</h2>
    <p>{{ __('bus.success.description', ['orderId' => $orderId]) }}</p>

    <div class="bus-v2-success-actions">
        <a href="{{ $ticketUrl }}" target="_blank" rel="noopener" class="bus-v2-button bus-v2-button--primary">
            <i class="fas fa-ticket-alt"></i>
            {{ __('bus.success.open_ticket') }}
        </a>
        <a href="{{ $transactionsUrl }}" class="bus-v2-button bus-v2-button--ghost">
            <i class="fas fa-list"></i>
            {{ __('bus.success.view_transactions') }}
        </a>
        <button type="button" class="bus-v2-button bus-v2-button--soft" data-bus-v2-reset>
            <i class="fas fa-redo"></i>
            {{ __('bus.success.new_search') }}
        </button>
    </div>
</section>
