@php
    $classPrefix = $classPrefix ?? 'v2-resource';
    $wrapperClass = trim('v2-resource-page ' . ($wrapperClass ?? '') . ' ' . $classPrefix);
    $wrapperAttributes = $wrapperAttributes ?? [];
    $stats = $stats ?? [];
    $headerActionsView = $headerActionsView ?? null;
    $headerActionsData = $headerActionsData ?? [];
    $panelActionsView = $panelActionsView ?? null;
    $panelActionsData = $panelActionsData ?? [];
    $toolbarView = $toolbarView ?? null;
    $toolbarData = $toolbarData ?? [];
    $bodyView = $bodyView ?? null;
    $bodyData = $bodyData ?? [];
    $panelId = $panelId ?? null;
    $showHeader = $showHeader ?? true;
@endphp

<div class="container-fluid">
    <div class="{{ $wrapperClass }}"
         @foreach($wrapperAttributes as $attributeName => $attributeValue)
             {{ $attributeName }}="{{ e($attributeValue) }}"
         @endforeach>
        @if($showHeader)
            <section class="v2-resource-page-head {{ $classPrefix }}-head">
                <div class="v2-resource-page-head-main {{ $classPrefix }}-head-main">
                    @if(!empty($kickerText))
                        <span class="v2-resource-page-kicker {{ $classPrefix }}-kicker">
                            @if(!empty($kickerIcon))
                                <i class="{{ $kickerIcon }}" aria-hidden="true"></i>
                            @endif
                            {{ $kickerText }}
                        </span>
                    @endif
                    <h2>{{ $title ?? '' }}</h2>
                    @if(!empty($subtitle))
                        <p>{{ $subtitle }}</p>
                    @endif
                </div>

                @if($headerActionsView)
                    <div class="v2-resource-page-head-actions {{ $classPrefix }}-head-actions">
                        @include($headerActionsView, $headerActionsData)
                    </div>
                @endif
            </section>
        @endif

        @if(!empty($stats))
            <section class="v2-resource-page-stats {{ $classPrefix }}-stats">
                @foreach($stats as $stat)
                    <div class="v2-resource-page-stat {{ $classPrefix }}-stat">
                        <span>{{ $stat['label'] ?? '' }}</span>
                        <strong>{{ $stat['value'] ?? '' }}</strong>
                    </div>
                @endforeach
            </section>
        @endif

        <section class="v2-resource-page-panel {{ $classPrefix }}-panel"
                 @if($panelId) id="{{ $panelId }}" @endif>
            <div class="v2-resource-page-panel-head {{ $classPrefix }}-panel-head">
                <div>
                    <h3>{{ $panelTitle ?? '' }}</h3>
                    @if(!empty($panelSubtitle))
                        <p>{{ $panelSubtitle }}</p>
                    @endif
                </div>

                @if($panelActionsView)
                    <div class="v2-resource-page-table-actions {{ $classPrefix }}-table-actions">
                        @include($panelActionsView, $panelActionsData)
                    </div>
                @endif
            </div>

            @if($toolbarView)
                @include($toolbarView, $toolbarData)
            @endif

            @if($bodyView)
                @include($bodyView, $bodyData)
            @endif
        </section>
    </div>
</div>
