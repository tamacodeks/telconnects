@php
    $locale = app()->getLocale() === 'fr' ? 'fr' : 'en';
    $copy = [
        'en' => [
            'eyebrow' => 'PIN support',
            'title' => 'Create an enquiry',
            'subtitle' => 'Send a clear request to your manager with the selected calling card details.',
            'from' => 'From',
            'to' => 'To',
            'card' => 'Card',
            'face_value' => 'Face value',
            'serial' => 'Serial',
            'pin' => 'PIN',
            'type' => 'Issue type',
            'card_issue' => 'Card issue',
            'card_issue_hint' => 'PIN, serial or card not working',
            'topup_request' => 'Topup request',
            'topup_request_hint' => 'Need help with a recharge',
            'others' => 'Other request',
            'others_hint' => 'Anything else related to this PIN',
            'message' => 'Message',
            'message_placeholder' => 'Describe the issue, expected result, and any customer context.',
            'send' => 'Send enquiry',
            'processing' => 'Processing',
            'type_required' => 'Please select an enquiry type.',
            'message_required' => 'Please enter a message.',
        ],
        'fr' => [
            'eyebrow' => 'Support PIN',
            'title' => 'Creer une demande',
            'subtitle' => 'Envoyez une demande claire a votre gestionnaire avec les details de la carte selectionnee.',
            'from' => 'De',
            'to' => 'A',
            'card' => 'Carte',
            'face_value' => 'Valeur faciale',
            'serial' => 'Serie',
            'pin' => 'PIN',
            'type' => 'Type de demande',
            'card_issue' => 'Probleme de carte',
            'card_issue_hint' => 'PIN, serie ou carte non fonctionnel',
            'topup_request' => 'Demande de recharge',
            'topup_request_hint' => 'Besoin d aide avec une recharge',
            'others' => 'Autre demande',
            'others_hint' => 'Autre sujet lie a ce PIN',
            'message' => 'Message',
            'message_placeholder' => 'Decrivez le probleme, le resultat attendu et le contexte client.',
            'send' => 'Envoyer la demande',
            'processing' => 'Traitement',
            'type_required' => 'Veuillez selectionner un type de demande.',
            'message_required' => 'Veuillez saisir un message.',
        ],
    ][$locale];
    $currentUser = auth()->user();
    $toUsername = optional(\App\User::find($currentUser->parent_id))->username ?: '-';
    $valueOrDash = function ($value) {
        return isset($value) && $value !== '' ? $value : '-';
    };
@endphp

<style>
    .pin-enquiry-shell {
        --pe-blue: #1769ff;
        --pe-blue-dark: #0d4ed6;
        --pe-text: #061735;
        --pe-muted: #637795;
        --pe-border: #d7e4f7;
        --pe-surface: #ffffff;
        --pe-surface-soft: #f8fbff;
        --pe-danger: #e5484d;
        color: var(--pe-text);
        margin: -2px;
    }

    .pin-enquiry-card {
        overflow: hidden;
        border: 1px solid var(--pe-border);
        border-radius: 18px;
        background: linear-gradient(135deg, rgba(23, 105, 255, .08), transparent 36%), var(--pe-surface);
        box-shadow: 0 18px 50px rgba(18, 52, 92, .14);
    }

    .pin-enquiry-hero {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 20px 22px;
        border-bottom: 1px solid var(--pe-border);
        background: linear-gradient(180deg, rgba(255, 255, 255, .86), rgba(246, 250, 255, .92));
    }

    .pin-enquiry-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 44px;
        width: 44px;
        height: 44px;
        border: 1px solid rgba(23, 105, 255, .18);
        border-radius: 14px;
        background: rgba(23, 105, 255, .10);
        color: var(--pe-blue);
        font-size: 18px;
    }

    .pin-enquiry-eyebrow {
        display: block;
        margin-bottom: 4px;
        color: var(--pe-blue);
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .pin-enquiry-title {
        margin: 0;
        color: var(--pe-text);
        font-size: 20px;
        font-weight: 900;
        line-height: 1.25;
    }

    .pin-enquiry-subtitle {
        max-width: 620px;
        margin: 6px 0 0;
        color: var(--pe-muted);
        font-size: 13px;
        font-weight: 700;
        line-height: 1.45;
    }

    .pin-enquiry-body {
        padding: 20px 22px 22px;
    }

    .pin-enquiry-summary,
    .pin-enquiry-options {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .pin-enquiry-summary {
        margin-bottom: 18px;
    }

    .pin-enquiry-summary-item,
    .pin-enquiry-option-body {
        border: 1px solid var(--pe-border);
        border-radius: 14px;
        background: var(--pe-surface-soft);
    }

    .pin-enquiry-summary-item {
        min-width: 0;
        padding: 11px 12px;
    }

    .pin-enquiry-summary-item span {
        display: block;
        margin-bottom: 5px;
        color: var(--pe-muted);
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .pin-enquiry-summary-item strong {
        display: block;
        overflow: hidden;
        color: var(--pe-text);
        font-size: 13px;
        font-weight: 900;
        line-height: 1.25;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .pin-enquiry-field {
        margin-bottom: 16px;
    }

    .pin-enquiry-label {
        display: block;
        margin-bottom: 8px;
        color: var(--pe-text);
        font-size: 13px;
        font-weight: 900;
    }

    .pin-enquiry-options {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .pin-enquiry-option {
        position: relative;
        display: block;
        margin: 0;
        cursor: pointer;
    }

    .pin-enquiry-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .pin-enquiry-option-body {
        display: flex;
        min-height: 88px;
        height: 100%;
        gap: 10px;
        padding: 12px;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease, background .18s ease;
    }

    .pin-enquiry-option-body i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 30px;
        width: 30px;
        height: 30px;
        border-radius: 10px;
        background: rgba(23, 105, 255, .09);
        color: var(--pe-blue);
        font-size: 13px;
    }

    .pin-enquiry-option-body strong,
    .pin-enquiry-option-body small {
        display: block;
        line-height: 1.25;
    }

    .pin-enquiry-option-body strong {
        color: var(--pe-text);
        font-size: 13px;
        font-weight: 900;
    }

    .pin-enquiry-option-body small {
        margin-top: 4px;
        color: var(--pe-muted);
        font-size: 11px;
        font-weight: 700;
    }

    .pin-enquiry-option:hover .pin-enquiry-option-body,
    .pin-enquiry-option input:focus + .pin-enquiry-option-body {
        border-color: rgba(23, 105, 255, .46);
        box-shadow: 0 10px 24px rgba(23, 105, 255, .10);
        transform: translateY(-1px);
    }

    .pin-enquiry-option input:checked + .pin-enquiry-option-body {
        border-color: rgba(23, 105, 255, .72);
        background: linear-gradient(180deg, rgba(23, 105, 255, .10), rgba(23, 105, 255, .045));
        box-shadow: inset 0 0 0 1px rgba(23, 105, 255, .16), 0 12px 28px rgba(23, 105, 255, .12);
    }

    .pin-enquiry-message {
        width: 100%;
        min-height: 118px;
        resize: vertical;
        border: 1px solid var(--pe-border);
        border-radius: 14px;
        background: var(--pe-surface);
        color: var(--pe-text);
        font-size: 13px;
        font-weight: 700;
        line-height: 1.45;
        padding: 13px 14px;
        outline: none;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    .pin-enquiry-message:focus {
        border-color: rgba(23, 105, 255, .66);
        box-shadow: 0 0 0 3px rgba(23, 105, 255, .12);
    }

    .pin-enquiry-actions {
        display: flex;
        justify-content: flex-end;
    }

    .pin-enquiry-submit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 42px;
        border: 1px solid var(--pe-blue);
        border-radius: 12px;
        background: linear-gradient(180deg, #1887ff, var(--pe-blue));
        color: #fff;
        font-size: 13px;
        font-weight: 900;
        padding: 0 18px;
        box-shadow: 0 12px 26px rgba(23, 105, 255, .22);
        transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
    }

    .pin-enquiry-submit:hover,
    .pin-enquiry-submit:focus {
        background: linear-gradient(180deg, #147dff, var(--pe-blue-dark));
        color: #fff;
        box-shadow: 0 16px 32px rgba(23, 105, 255, .28);
        transform: translateY(-1px);
    }

    .pin-enquiry-shell .help-block {
        margin: 7px 0 0;
        color: var(--pe-danger);
        font-size: 12px;
        font-weight: 800;
    }

    .pin-enquiry-shell .has-error .pin-enquiry-message,
    .pin-enquiry-shell .has-error .pin-enquiry-option-body {
        border-color: rgba(229, 72, 77, .72);
    }

    body.dark-only .pin-enquiry-shell,
    html.dark .pin-enquiry-shell,
    [data-bs-theme="dark"] .pin-enquiry-shell {
        --pe-text: #f4f8ff;
        --pe-muted: #9fb0c7;
        --pe-border: rgba(110, 151, 210, .34);
        --pe-surface: #0d1829;
        --pe-surface-soft: #111f34;
    }

    body.dark-only .pin-enquiry-card,
    html.dark .pin-enquiry-card,
    [data-bs-theme="dark"] .pin-enquiry-card {
        background: linear-gradient(135deg, rgba(47, 125, 255, .12), transparent 38%), var(--pe-surface);
        box-shadow: 0 18px 50px rgba(0, 0, 0, .32);
    }

    body.dark-only .pin-enquiry-hero,
    html.dark .pin-enquiry-hero,
    [data-bs-theme="dark"] .pin-enquiry-hero {
        background: linear-gradient(180deg, rgba(25, 43, 69, .92), rgba(13, 24, 41, .92));
    }

    @media (max-width: 767px) {
        .pin-enquiry-hero,
        .pin-enquiry-body {
            padding-left: 16px;
            padding-right: 16px;
        }

        .pin-enquiry-summary,
        .pin-enquiry-options {
            grid-template-columns: 1fr;
        }

        .pin-enquiry-option-body {
            min-height: 74px;
        }

        .pin-enquiry-actions,
        .pin-enquiry-submit {
            width: 100%;
        }
    }
</style>

<div class="pin-enquiry-shell">
    <form action="{{ $contactActionUrl ?? url('cc-pin-history-v2/contact') }}" id="frmEnquiry" method="post">
        {{ csrf_field() }}
        <input type="hidden" name="fwdStatus" value="{{ isset($ticket_fwd) ? "true" : "false" }}">
        <input type="hidden" name="pin_id" value="{{ $pin_id }}">

        <div class="pin-enquiry-card">
            <div class="pin-enquiry-hero">
                <span class="pin-enquiry-icon" aria-hidden="true"><i class="fa fa-comments"></i></span>
                <div>
                    <span class="pin-enquiry-eyebrow">{{ $copy['eyebrow'] }}</span>
                    <h3 class="pin-enquiry-title">{{ $copy['title'] }}</h3>
                    <p class="pin-enquiry-subtitle">{{ $copy['subtitle'] }}</p>
                </div>
            </div>

            <div class="pin-enquiry-body">
                <div class="pin-enquiry-summary" aria-label="PIN details">
                    <div class="pin-enquiry-summary-item">
                        <span>{{ $copy['from'] }}</span>
                        <strong title="{{ $currentUser->username }}">{{ $currentUser->username }}</strong>
                    </div>
                    <div class="pin-enquiry-summary-item">
                        <span>{{ $copy['to'] }}</span>
                        <strong title="{{ $toUsername }}">{{ $toUsername }}</strong>
                    </div>
                    <div class="pin-enquiry-summary-item">
                        <span>{{ $copy['card'] }}</span>
                        <strong title="{{ $valueOrDash($card->name) }}">{{ $valueOrDash($card->name) }}</strong>
                    </div>
                    <div class="pin-enquiry-summary-item">
                        <span>{{ $copy['face_value'] }}</span>
                        <strong>{{ $valueOrDash(isset($card->face_value) ? $card->face_value : null) }}</strong>
                    </div>
                    <div class="pin-enquiry-summary-item">
                        <span>{{ $copy['serial'] }}</span>
                        <strong title="{{ $valueOrDash($card->serial) }}">{{ $valueOrDash($card->serial) }}</strong>
                    </div>
                    <div class="pin-enquiry-summary-item">
                        <span>{{ $copy['pin'] }}</span>
                        <strong title="{{ $valueOrDash($card->pin) }}">{{ $valueOrDash($card->pin) }}</strong>
                    </div>
                </div>

                <div class="pin-enquiry-field">
                    <label class="pin-enquiry-label">{{ $copy['type'] }}</label>
                    <div class="pin-enquiry-options">
                        <label class="pin-enquiry-option">
                            <input type="radio" value="card_issue" name="type" required>
                            <span class="pin-enquiry-option-body">
                                <i class="fa fa-credit-card" aria-hidden="true"></i>
                                <span>
                                    <strong>{{ $copy['card_issue'] }}</strong>
                                    <small>{{ $copy['card_issue_hint'] }}</small>
                                </span>
                            </span>
                        </label>
                        <label class="pin-enquiry-option">
                            <input type="radio" value="topup_request" name="type" required>
                            <span class="pin-enquiry-option-body">
                                <i class="fa fa-bolt" aria-hidden="true"></i>
                                <span>
                                    <strong>{{ $copy['topup_request'] }}</strong>
                                    <small>{{ $copy['topup_request_hint'] }}</small>
                                </span>
                            </span>
                        </label>
                        <label class="pin-enquiry-option">
                            <input type="radio" value="others" name="type" required>
                            <span class="pin-enquiry-option-body">
                                <i class="fa fa-question-circle" aria-hidden="true"></i>
                                <span>
                                    <strong>{{ $copy['others'] }}</strong>
                                    <small>{{ $copy['others_hint'] }}</small>
                                </span>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="pin-enquiry-field">
                    <label for="message" class="pin-enquiry-label">{{ $copy['message'] }}</label>
                    <textarea class="pin-enquiry-message" name="message" id="message" required placeholder="{{ $copy['message_placeholder'] }}"></textarea>
                </div>

                <div class="pin-enquiry-actions">
                    <button class="pin-enquiry-submit" id="btnSubmitSend" type="submit">
                        <i class="fa fa-paper-plane" aria-hidden="true"></i>
                        <span>{{ $copy['send'] }}</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    (function () {
        if (!window.jQuery || !jQuery.fn.validate) {
            return;
        }

        var $ = window.jQuery;

        $('#frmEnquiry').validate({
            rules: {
                type: "required",
                message: "required"
            },
            messages: {
                type: {!! json_encode($copy['type_required']) !!},
                message: {!! json_encode($copy['message_required']) !!}
            },
            errorElement: "div",
            errorPlacement: function (error, element) {
                error.addClass("help-block");

                if (element.prop("type") === "radio") {
                    error.insertAfter(element.closest(".pin-enquiry-options"));
                    return;
                }

                error.insertAfter(element);
            },
            highlight: function (element) {
                $(element).closest(".pin-enquiry-field").addClass("has-error").removeClass("has-success");
            },
            unhighlight: function (element) {
                $(element).closest(".pin-enquiry-field").removeClass("has-error").addClass("has-success");
            },
            submitHandler: function (form) {
                var $form = $("#frmEnquiry");
                var $button = $("#btnSubmitSend");

                if ($.fn.LoadingOverlay) {
                    $form.LoadingOverlay("show");
                }

                $button
                    .html("<i class='fa fa-spinner fa-pulse'></i><span>{{ $copy['processing'] }}</span>")
                    .attr("disabled", "disabled");

                form.submit();
            }
        });
    })();
</script>
