<style>
    :root {
        --bus-v2-bg: #f6f1e8;
        --bus-v2-surface: #ffffff;
        --bus-v2-ink: #123049;
        --bus-v2-muted: #607284;
        --bus-v2-line: #e6dccb;
        --bus-v2-green: #57a63a;
        --bus-v2-green-deep: #2f6d2e;
        --bus-v2-coral: #f06a4d;
        --bus-v2-sky: #dff2ed;
        --bus-v2-sand: #f7e7c7;
        --bus-v2-shadow: 0 24px 60px rgba(17, 40, 63, 0.12);
    }

    .bus-v2-page {
        background:
            radial-gradient(circle at top left, rgba(87, 166, 58, 0.08), transparent 36%),
            radial-gradient(circle at bottom right, rgba(240, 106, 77, 0.08), transparent 32%),
            linear-gradient(180deg, #f8f4eb 0%, #f3ede3 100%);
        margin: -15px;
        min-height: calc(100vh - 90px);
        padding: 18px 18px 42px;
    }

    .bus-v2-shell {
        color: var(--bus-v2-ink);
        font-family: "IBM Plex Sans", "Segoe UI", sans-serif;
        margin: 0 auto;
        max-width: 1380px;
    }

    .bus-v2-shell h1,
    .bus-v2-shell h2,
    .bus-v2-shell h3,
    .bus-v2-shell h4,
    .bus-v2-shell h5 {
        font-family: "Sora", "Segoe UI", sans-serif;
        letter-spacing: -0.03em;
    }

    .bus-v2-hero {
        background:
            linear-gradient(135deg, rgba(255, 255, 255, 0.14), rgba(255, 255, 255, 0.02)),
            linear-gradient(120deg, #173e61 0%, #1d5a58 46%, #3b7e3b 100%);
        border-radius: 30px;
        box-shadow: var(--bus-v2-shadow);
        color: #ffffff;
        overflow: hidden;
        padding: 34px 34px 28px;
        position: relative;
    }

    .bus-v2-hero::before,
    .bus-v2-hero::after {
        content: "";
        position: absolute;
        border-radius: 999px;
        opacity: 0.22;
    }

    .bus-v2-hero::before {
        background: #8fd0ff;
        height: 240px;
        right: -70px;
        top: -80px;
        width: 240px;
    }

    .bus-v2-hero::after {
        background: #ffd68e;
        bottom: -120px;
        height: 260px;
        left: -100px;
        width: 260px;
    }

    .bus-v2-hero-grid {
        align-items: end;
        display: grid;
        gap: 24px;
        grid-template-columns: minmax(0, 1.4fr) minmax(300px, 0.9fr);
        position: relative;
        z-index: 1;
    }

    .bus-v2-kicker {
        align-items: center;
        display: inline-flex;
        gap: 10px;
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.08em;
        margin-bottom: 18px;
        padding: 8px 14px;
        text-transform: uppercase;
    }

    .bus-v2-hero-title {
        font-size: clamp(30px, 4vw, 46px);
        line-height: 1.02;
        margin: 0 0 14px;
        max-width: 10ch;
    }

    .bus-v2-hero-copy {
        color: rgba(255, 255, 255, 0.84);
        font-size: 16px;
        line-height: 1.7;
        margin: 0;
        max-width: 62ch;
    }

    .bus-v2-hero-stats {
        display: grid;
        gap: 14px;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        margin-top: 26px;
    }

    .bus-v2-stat {
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 20px;
        min-height: 116px;
        padding: 18px;
        transform: translateY(12px);
        animation: busV2Lift 0.7s ease forwards;
    }

    .bus-v2-stat:nth-child(2) {
        animation-delay: 0.08s;
    }

    .bus-v2-stat:nth-child(3) {
        animation-delay: 0.16s;
    }

    .bus-v2-stat strong {
        display: block;
        font-family: "Sora", "Segoe UI", sans-serif;
        font-size: 28px;
        margin-bottom: 8px;
    }

    .bus-v2-stat span {
        color: rgba(255, 255, 255, 0.84);
        display: block;
        font-size: 13px;
        line-height: 1.5;
    }

    .bus-v2-hero-side {
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 24px;
        padding: 22px;
        backdrop-filter: blur(10px);
    }

    .bus-v2-pill-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 16px;
    }

    .bus-v2-pill {
        align-items: center;
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 999px;
        color: #ffffff;
        display: inline-flex;
        font-size: 13px;
        font-weight: 600;
        gap: 8px;
        padding: 9px 13px;
    }

    .bus-v2-summary-card {
        background: rgba(255, 255, 255, 0.12);
        border-radius: 20px;
        padding: 18px;
    }

    .bus-v2-summary-card h4 {
        font-size: 18px;
        margin: 0 0 10px;
    }

    .bus-v2-summary-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .bus-v2-summary-list li {
        align-items: center;
        display: flex;
        justify-content: space-between;
        gap: 16px;
        padding: 11px 0;
        border-top: 1px solid rgba(255, 255, 255, 0.12);
    }

    .bus-v2-summary-list li:first-child {
        border-top: 0;
        padding-top: 0;
    }

    .bus-v2-search-card,
    .bus-v2-panel,
    .bus-v2-checkout-panel {
        background: var(--bus-v2-surface);
        border: 1px solid rgba(18, 48, 73, 0.06);
        border-radius: 26px;
        box-shadow: var(--bus-v2-shadow);
        margin-top: 18px;
        padding: 26px;
    }

    .bus-v2-section-title {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    .bus-v2-section-title h2,
    .bus-v2-section-title h3 {
        font-size: 24px;
        margin: 0;
    }

    .bus-v2-section-note {
        color: var(--bus-v2-muted);
        font-size: 14px;
        margin: 0;
    }

    .bus-v2-search-grid {
        display: grid;
        gap: 14px;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .bus-v2-field {
        position: relative;
    }

    .bus-v2-field label {
        color: var(--bus-v2-muted);
        display: block;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.08em;
        margin-bottom: 10px;
        text-transform: uppercase;
    }

    .bus-v2-control,
    .bus-v2-control-static,
    .bus-v2-select {
        appearance: none;
        background: #fcfbf8;
        border: 1px solid var(--bus-v2-line);
        border-radius: 18px;
        color: var(--bus-v2-ink);
        font-size: 15px;
        font-weight: 600;
        min-height: 58px;
        outline: none;
        padding: 16px 18px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        width: 100%;
    }

    .bus-v2-control:focus,
    .bus-v2-select:focus,
    .bus-v2-control-static.is-active {
        border-color: rgba(87, 166, 58, 0.8);
        box-shadow: 0 0 0 4px rgba(87, 166, 58, 0.12);
        transform: translateY(-1px);
    }

    .bus-v2-icon {
        color: var(--bus-v2-muted);
        position: absolute;
        right: 18px;
        top: 46px;
    }

    .bus-v2-swap {
        align-items: center;
        background: var(--bus-v2-ink);
        border: 0;
        border-radius: 999px;
        color: #ffffff;
        cursor: pointer;
        display: inline-flex;
        height: 44px;
        justify-content: center;
        margin-top: 32px;
        width: 44px;
    }

    .bus-v2-counter-grid {
        display: grid;
        gap: 12px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        margin-top: 18px;
    }

    .bus-v2-counter {
        background: #f9f5ec;
        border: 1px solid var(--bus-v2-line);
        border-radius: 18px;
        display: flex;
        justify-content: space-between;
        gap: 14px;
        padding: 15px 16px;
    }

    .bus-v2-counter strong {
        display: block;
        font-size: 15px;
        margin-bottom: 4px;
    }

    .bus-v2-counter span {
        color: var(--bus-v2-muted);
        display: block;
        font-size: 12px;
    }

    .bus-v2-stepper {
        align-items: center;
        display: inline-flex;
        gap: 10px;
    }

    .bus-v2-stepper button {
        align-items: center;
        background: #ffffff;
        border: 1px solid var(--bus-v2-line);
        border-radius: 999px;
        color: var(--bus-v2-ink);
        cursor: pointer;
        display: inline-flex;
        font-size: 15px;
        font-weight: 700;
        height: 34px;
        justify-content: center;
        width: 34px;
    }

    .bus-v2-stepper input {
        background: transparent;
        border: 0;
        font-size: 18px;
        font-weight: 700;
        text-align: center;
        width: 36px;
    }

    .bus-v2-search-actions {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        justify-content: space-between;
        margin-top: 18px;
    }

    .bus-v2-inline-note {
        color: var(--bus-v2-muted);
        font-size: 13px;
        margin: 0;
    }

    .bus-v2-button {
        align-items: center;
        background: linear-gradient(135deg, var(--bus-v2-green), var(--bus-v2-green-deep));
        border: 0;
        border-radius: 16px;
        color: #ffffff;
        cursor: pointer;
        display: inline-flex;
        font-family: "Sora", "Segoe UI", sans-serif;
        font-size: 15px;
        font-weight: 700;
        gap: 10px;
        justify-content: center;
        min-height: 54px;
        padding: 0 24px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .bus-v2-button:hover,
    .bus-v2-button:focus {
        box-shadow: 0 16px 30px rgba(47, 109, 46, 0.24);
        color: #ffffff;
        text-decoration: none;
        transform: translateY(-1px);
    }

    .bus-v2-button--coral {
        background: linear-gradient(135deg, var(--bus-v2-coral), #d75338);
    }

    .bus-v2-chip {
        align-items: center;
        background: #f7f3eb;
        border: 1px solid var(--bus-v2-line);
        border-radius: 999px;
        color: var(--bus-v2-ink);
        display: inline-flex;
        font-size: 12px;
        font-weight: 700;
        gap: 8px;
        padding: 8px 12px;
        text-transform: uppercase;
    }

    .bus-v2-alert {
        background: #fff6ef;
        border: 1px solid rgba(240, 106, 77, 0.24);
        border-radius: 20px;
        color: #9b4d3d;
        margin-top: 18px;
        padding: 16px 18px;
    }

    .bus-v2-feature-grid,
    .bus-v2-result-list,
    .bus-v2-checkout-grid {
        display: grid;
        gap: 16px;
    }

    .bus-v2-feature-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .bus-v2-feature-card {
        background: linear-gradient(180deg, #ffffff 0%, #fbf8f2 100%);
        border: 1px solid var(--bus-v2-line);
        border-radius: 22px;
        min-height: 200px;
        padding: 22px;
    }

    .bus-v2-feature-card i {
        align-items: center;
        background: var(--bus-v2-sky);
        border-radius: 16px;
        color: var(--bus-v2-ink);
        display: inline-flex;
        font-size: 18px;
        height: 48px;
        justify-content: center;
        margin-bottom: 18px;
        width: 48px;
    }

    .bus-v2-feature-card p {
        color: var(--bus-v2-muted);
        line-height: 1.7;
        margin: 10px 0 0;
    }

    .bus-v2-filter-grid {
        display: grid;
        gap: 12px;
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .bus-v2-result-card {
        background: linear-gradient(180deg, #ffffff 0%, #fcfaf6 100%);
        border: 1px solid rgba(18, 48, 73, 0.08);
        border-radius: 24px;
        overflow: hidden;
        padding: 22px;
        position: relative;
    }

    .bus-v2-result-top {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    .bus-v2-operator-tag {
        align-items: center;
        background: #ecf8ea;
        border-radius: 999px;
        color: var(--bus-v2-green-deep);
        display: inline-flex;
        font-size: 12px;
        font-weight: 700;
        gap: 8px;
        padding: 8px 12px;
        text-transform: uppercase;
    }

    .bus-v2-operator-tag.is-bla {
        background: #fff1ed;
        color: #d75338;
    }

    .bus-v2-route-grid {
        align-items: center;
        display: grid;
        gap: 18px;
        grid-template-columns: minmax(0, 1fr) 180px minmax(0, 1fr);
    }

    .bus-v2-stop-label {
        color: var(--bus-v2-muted);
        display: block;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.08em;
        margin-bottom: 8px;
        text-transform: uppercase;
    }

    .bus-v2-time {
        display: block;
        font-family: "Sora", "Segoe UI", sans-serif;
        font-size: 30px;
        line-height: 1;
        margin-bottom: 6px;
    }

    .bus-v2-city {
        font-size: 18px;
        font-weight: 700;
        line-height: 1.35;
    }

    .bus-v2-axis {
        text-align: center;
    }

    .bus-v2-axis-line {
        align-items: center;
        display: flex;
        gap: 10px;
        justify-content: center;
        margin-bottom: 10px;
    }

    .bus-v2-axis-line span {
        background: #d4deeb;
        border-radius: 999px;
        display: block;
        height: 8px;
        width: 8px;
    }

    .bus-v2-axis-line::before,
    .bus-v2-axis-line::after {
        content: "";
        flex: 1 1 auto;
        height: 2px;
        background: linear-gradient(90deg, rgba(18, 48, 73, 0.12), rgba(87, 166, 58, 0.5));
    }

    .bus-v2-axis strong {
        display: block;
        font-family: "Sora", "Segoe UI", sans-serif;
        font-size: 20px;
        margin-bottom: 6px;
    }

    .bus-v2-axis small {
        color: var(--bus-v2-muted);
        display: block;
        font-size: 12px;
        line-height: 1.5;
    }

    .bus-v2-result-meta {
        border-top: 1px solid rgba(18, 48, 73, 0.08);
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 18px;
        padding-top: 18px;
    }

    .bus-v2-meta-box {
        background: #f7f3eb;
        border-radius: 16px;
        padding: 12px 14px;
    }

    .bus-v2-meta-box strong {
        display: block;
        font-size: 15px;
        margin-bottom: 4px;
    }

    .bus-v2-meta-box span {
        color: var(--bus-v2-muted);
        font-size: 12px;
    }

    .bus-v2-price-stack {
        align-items: end;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .bus-v2-price-stack strong {
        font-family: "Sora", "Segoe UI", sans-serif;
        font-size: 30px;
        line-height: 1;
    }

    .bus-v2-price-stack span {
        color: var(--bus-v2-muted);
        font-size: 12px;
        font-weight: 600;
    }

    .bus-v2-checkout-grid {
        align-items: start;
        grid-template-columns: minmax(0, 1.35fr) minmax(300px, 0.75fr);
    }

    .bus-v2-passenger-list {
        display: grid;
        gap: 16px;
    }

    .bus-v2-passenger-card {
        background: linear-gradient(180deg, #ffffff 0%, #fcfaf5 100%);
        border: 1px solid var(--bus-v2-line);
        border-radius: 22px;
        padding: 20px;
    }

    .bus-v2-passenger-head {
        align-items: center;
        display: flex;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 18px;
    }

    .bus-v2-passenger-head h4 {
        font-size: 20px;
        margin: 0;
    }

    .bus-v2-badge-soft {
        background: var(--bus-v2-sand);
        border-radius: 999px;
        color: #8a6220;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.06em;
        padding: 8px 10px;
        text-transform: uppercase;
    }

    .bus-v2-form-grid {
        display: grid;
        gap: 14px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .bus-v2-form-block {
        margin-top: 18px;
    }

    .bus-v2-form-block h5 {
        font-size: 15px;
        margin: 0 0 12px;
    }

    .bus-v2-form-block p {
        color: var(--bus-v2-muted);
        font-size: 13px;
        margin: -4px 0 12px;
    }

    .bus-v2-aside {
        position: sticky;
        top: 20px;
    }

    .bus-v2-aside-card {
        background: linear-gradient(180deg, #173e61 0%, #1e5676 100%);
        border-radius: 24px;
        color: #ffffff;
        overflow: hidden;
        padding: 24px;
        position: relative;
    }

    .bus-v2-aside-card::after {
        content: "";
        position: absolute;
        inset: auto -60px -80px auto;
        height: 180px;
        width: 180px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.08);
    }

    .bus-v2-aside-card h3 {
        font-size: 24px;
        margin: 0 0 10px;
    }

    .bus-v2-aside-card p,
    .bus-v2-aside-card li {
        color: rgba(255, 255, 255, 0.82);
    }

    .bus-v2-aside-list {
        list-style: none;
        margin: 20px 0 0;
        padding: 0;
    }

    .bus-v2-aside-list li {
        align-items: center;
        display: flex;
        gap: 10px;
        padding: 9px 0;
    }

    .bus-v2-total-card {
        background: #f9f6ee;
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 20px;
        margin-top: 16px;
        padding: 18px;
    }

    .bus-v2-total-card strong {
        display: block;
        font-family: "Sora", "Segoe UI", sans-serif;
        font-size: 34px;
        margin-top: 8px;
    }

    .bus-v2-submit {
        margin-top: 18px;
        width: 100%;
    }

    .bus-v2-error-text {
        color: #d75338;
        display: block;
        font-size: 12px;
        margin-top: 8px;
        min-height: 16px;
    }

    .ui-autocomplete {
        background: #ffffff;
        border: 1px solid var(--bus-v2-line);
        border-radius: 18px;
        box-shadow: 0 20px 30px rgba(17, 40, 63, 0.12);
        padding: 8px;
        z-index: 9999 !important;
    }

    .ui-menu-item-wrapper {
        border-radius: 12px;
        font-size: 14px;
        padding: 10px 12px;
    }

    .ui-state-active,
    .ui-widget-content .ui-state-active {
        background: #eef7eb !important;
        border: 0 !important;
        color: var(--bus-v2-green-deep) !important;
    }

    @keyframes busV2Lift {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 1199px) {
        .bus-v2-hero-grid,
        .bus-v2-checkout-grid {
            grid-template-columns: 1fr;
        }

        .bus-v2-route-grid {
            grid-template-columns: 1fr;
            text-align: left;
        }

        .bus-v2-axis {
            text-align: left;
        }

        .bus-v2-axis-line {
            justify-content: flex-start;
        }
    }

    @media (max-width: 991px) {
        .bus-v2-page {
            margin: -15px -15px 0;
            padding: 14px 14px 32px;
        }

        .bus-v2-search-grid,
        .bus-v2-filter-grid,
        .bus-v2-feature-grid,
        .bus-v2-form-grid,
        .bus-v2-counter-grid,
        .bus-v2-hero-stats {
            grid-template-columns: 1fr;
        }

        .bus-v2-hero,
        .bus-v2-search-card,
        .bus-v2-panel,
        .bus-v2-checkout-panel {
            border-radius: 24px;
            padding: 20px;
        }

        .bus-v2-price-stack {
            align-items: flex-start;
        }
    }

    @media (max-width: 767px) {
        .bus-v2-hero-title {
            max-width: none;
        }

        .bus-v2-search-actions,
        .bus-v2-result-top,
        .bus-v2-passenger-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .bus-v2-swap {
            margin-top: 8px;
        }
    }
</style>
