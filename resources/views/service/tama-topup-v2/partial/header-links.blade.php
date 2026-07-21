<div class="tama-v2-link-grid">
    <a href="{{ url('tama-topup-v2') }}" class="tama-v2-link-item tama-v2-link-item--primary">
        <div class="tama-v2-link-icon tama-v2-link-icon--bus">
            <img src="{{ asset('images/t.png') }}" alt="Top-up">
        </div>
        <div class="tama-v2-link-content">
        </div>
    </a>
    <a href="{{ url('calling-cards-v2') }}" class="tama-v2-link-item tama-v2-link-item--secondary">
        <div class="tama-v2-link-icon tama-v2-link-icon--bus">
            <img src="{{ asset('images/c.png') }}" alt="Calling Cards">
        </div>
        <div class="tama-v2-link-content">
        </div>
    </a>
    <a href="{{ route('bus.v2') }}" class="tama-v2-link-item tama-v2-link-item--tertiary">
        <div class="tama-v2-link-icon tama-v2-link-icon--bus">
            <img src="{{ asset('images/bus.png') }}" alt="Bus">
        </div>
        <div class="tama-v2-link-content">
        </div>
    </a>
</div>

{{-- <div id="refreshBox"
     style="max-width:100%; margin:20px auto; padding:18px 22px;
            background:linear-gradient(135deg,#f8fafc,#e2e8f0);
            border-left:5px solid #2563eb;
            border-radius:10px;
            font-family:Arial, Helvetica, sans-serif;
            box-shadow:0 4px 12px rgba(0,0,0,0.08);">
    <h3 style="margin:0 0 12px 0; font-size:16px; color:#1e293b; font-weight:600; line-height:1.6;">
        Si la page ne se charge pas correctement ou si le numero de mobile n'est pas detecte,
        veuillez effectuer une actualisation forcee
        (<span style="color:#2563eb; font-weight:700;">CTRL + MAJ + R</span>).
    </h3>
    <button onclick="hardRefresh()"
            style="padding:10px 16px;
                   background:#2563eb;
                   color:#ffffff;
                   border:none;
                   border-radius:6px;
                   font-size:14px;
                   font-weight:600;
                   cursor:pointer;
                   box-shadow:0 4px 8px rgba(0,0,0,0.1);">
        Actualisation forcee
    </button>
</div> --}}
