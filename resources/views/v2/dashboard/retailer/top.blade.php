  <div class="retailer-top-grid">
    <div class="retailer-intro-slot">
      <section class="retailer-welcome-card" id="retailerWelcomeCard" aria-label="Retailer welcome">
        <div class="retailer-welcome-copy">
          <h2>{{ $retailerText['welcome_prefix'] }}, {{ $dashboardUserName }} !</h2>
          <p>{{ $retailerText['welcome_subtitle'] }}</p>
          <ul class="retailer-feature-list">
            <li>
              <span><i class="fa fa-shield-alt" aria-hidden="true"></i></span>
              <strong>{{ $retailerText['feature_secure'] }}</strong>
            </li>
            <li>
              <span><i class="fa fa-bolt" aria-hidden="true"></i></span>
              <strong>{!! $retailerText['feature_instant'] !!}</strong>
            </li>
            <li>
              <span><i class="fa fa-headset" aria-hidden="true"></i></span>
              <strong>{!! $retailerText['feature_support'] !!}</strong>
            </li>
          </ul>
        </div>
        <div class="retailer-wallet-art" aria-hidden="true">
          <span class="wallet-card"></span>
          <span class="wallet-body"></span>
          <span class="wallet-coin"><i class="fa fa-store"></i></span>
        </div>
      </section>

      @if($showBanners)
      <section class="panel-modern banner-modern retailer-banner-strip" id="retailerBannerCard" aria-label="Promotions" aria-hidden="true">
        <div id="bannerCarousel"
            class="carousel slide"
            data-bs-ride="carousel"
            data-bs-interval="5000"
            data-bs-pause="hover">

          <div class="carousel-inner" id="banner-slides">
            <div class="carousel-item active">
              <img src="{{ asset('images/banner/banner_default_image.png') }}" class="d-block w-100" alt="Banner">
            </div>
          </div>

          <div class="overlay"></div>

          <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev" aria-label="Previous banner">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next" aria-label="Next banner">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>
      </section>
      @endif
    </div>

    @if($showKPIs)
    <section class="retailer-metric-panel" aria-label="Retailer metrics">
      <div class="row" id="kpi-grid">
        @for($i=0;$i<8;$i++)
          <div class="col-sm-6">
            <div class="kpi-tile" aria-hidden="true">
              <div class="kpi-icon"></div>
              <div style="width:100%">
                <div class="skeleton" style="height:10px;width:78px;margin:0 auto 8px"></div>
                <div class="skeleton" style="height:20px;width:112px;margin:0 auto"></div>
              </div>
            </div>
          </div>
        @endfor
      </div>
    </section>
    @endif
  </div>
