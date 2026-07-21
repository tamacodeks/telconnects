@extends('v2.layout.simple.master')

@section('style')
    <link href="{{ asset('css/calling-cards-v2.css') }}?v={{ @filemtime(public_path('css/calling-cards-v2.css')) ?: time() }}" rel="stylesheet">
@endsection

@include('v2.layout.simple.breadcrumb', ['data' => [
    ['name' => 'Calling Cards V2', 'url' => '', 'active' => 'yes']
]])

@php
    $ccv2LogoFile = defined('APP_LOGO') && APP_LOGO ? APP_LOGO : 'logo.png';
    $ccv2LogoPath = file_exists(public_path('images/' . $ccv2LogoFile)) ? $ccv2LogoFile : 'logo.png';
@endphp

@section('content')
    <div class="container-fluid">
        <div class="ccv2">
            <div class="ccv2-header">
                <div>
                    <h2 class="ccv2-title">Calling Cards V2</h2>
                    <p class="ccv2-subtitle">Select a provider, pick a card, and print instantly.</p>
                </div>
                <div class="ccv2-pill">
                    <i class="fa fa-bolt"></i>
                    Instant Print
                </div>
            </div>

            <div class="ccv2-grid">
                <section class="ccv2-panel ccv2-panel--providers">
                    <h3>Providers</h3>
                    <div id="ccv2Providers" class="ccv2-list">
                        <div class="ccv2-empty">Loading providers...</div>
                    </div>
                </section>

                <section class="ccv2-panel ccv2-panel--cards">
                    <h3>Cards</h3>
                    <div id="ccv2Cards" class="ccv2-list">
                        <div class="ccv2-empty">Choose a provider to see cards.</div>
                    </div>
                </section>

                <section class="ccv2-panel ccv2-panel--preview">
                    <h3>Preview & Print</h3>
                    <div id="ccv2PrintArea" class="ccv2-card">
                        <div id="ccv2PrintableCard" class="ccv2-printable-card">
                            <div class="ccv2-card-header">
                                <img id="ccv2CardImg" class="hide" alt="">
                                <span id="ccv2CardFallback" class="ccv2-media-fallback ccv2-media-fallback--preview" aria-hidden="true">CC</span>
                                <div>
                                    <h4 id="ccv2CardName" class="ccv2-card-title">Select a card</h4>
                                    <p id="ccv2CardDesc" class="ccv2-card-desc">Card details will appear here.</p>
                                </div>
                            </div>

                            <div>
                                <div class="ccv2-meta">
                                    <span>Access</span>
                                    <span id="ccv2CardAccess">&mdash;</span>
                                </div>
                                <div class="ccv2-meta">
                                    <span>Validity</span>
                                    <span id="ccv2CardValidity">&mdash;</span>
                                </div>
                            </div>

                            <div id="ccv2PinBlock" class="ccv2-pin-block is-empty" aria-live="polite">
                                <div class="ccv2-pin-label">Code Secret</div>
                                <div class="ccv2-pin" id="ccv2CardPin">Select a card</div>
                                <div class="ccv2-meta ccv2-print-meta hide">
                                    <span>Serial</span>
                                    <span id="ccv2CardSerial">-</span>
                                </div>
                                <div class="ccv2-meta ccv2-print-meta hide">
                                    <span>Client</span>
                                    <span>{{ auth()->user()->username }}</span>
                                </div>
                                <div class="ccv2-meta ccv2-print-meta hide">
                                    <span>Date</span>
                                    <span id="ccv2CardDate">-</span>
                                </div>
                            </div>

                            <div>
                                <p id="ccv2CardComment1" class="ccv2-card-desc"></p>
                                <p id="ccv2CardComment2" class="ccv2-card-desc"></p>
                            </div>

                            <div class="ccv2-ticket-logo">
                                <img src="{{ asset('images/' . $ccv2LogoPath) }}" alt="{{ defined('APP_NAME') ? APP_NAME : 'Application' }}" onerror="this.remove()">
                            </div>
                        </div>

                        <div class="ccv2-actions">
                            <button id="ccv2PrintBtn" class="ccv2-btn ccv2-btn--primary" disabled title="Select a card to enable printing." aria-describedby="ccv2ActionHelp ccv2Notice">Print</button>
                            <button id="ccv2ReprintBtn" class="ccv2-btn ccv2-btn--ghost hide">Print Again</button>
                        </div>
                        <div id="ccv2Notice" class="ccv2-notice hide" role="alert">
                            <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                            <span id="ccv2NoticeText"></span>
                        </div>
                        <div id="ccv2ActionHelp" class="ccv2-action-help">Select a provider and card to enable printing.</div>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        var ccv2BaseUrl = "{{ url('') }}";
    </script>
    <script src="{{ asset('js/calling-cards-v2.js') }}?v={{ @filemtime(public_path('js/calling-cards-v2.js')) ?: time() }}" type="text/javascript"></script>
@endsection
