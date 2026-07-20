  <div class="row">
    @if($showBanners)
    {{-- Left: Banners --}}
    <div class="{{ $isRetailer ? 'col-lg-6 col-md-6' : 'col-md-6' }}">
      <div class="panel-modern banner-modern">
        <div id="bannerCarousel"
            class="carousel slide"
            data-bs-ride="carousel"
            data-bs-interval="5000"
            data-bs-pause="hover"
            aria-label="Promotions">

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
      </div>
    </div>
    @endif
    @if($showKPIs)
    {{-- Right: KPI tiles --}}
    <div class="{{ $isSuperAdmin ? 'col-md-12' : ($isRetailer ? ($showBanners ? 'col-lg-6 col-md-6' : 'col-md-12') : 'col-md-6') }}">
      <div class="kpi-shell {{ $isSuperAdmin ? 'root-console-shell' : '' }}">
        @if($isSuperAdmin)
        <div class="root-console-head">
          <div class="root-console-title-wrap">
            <span class="root-console-emblem"><i class="fa fa-shield" aria-hidden="true"></i></span>
            <div>
              <h4 class="root-console-title">Root Control Center</h4>
              <p class="root-console-subtitle">Setup control for users, groups, permissions, menus, services, cache, and audit logs.</p>
            </div>
          </div>
          <a href="{{ url('menus-v2') }}" class="root-workspace-pill">
            <span>Root workspace</span>
            <i class="fa fa-external-link" aria-hidden="true"></i>
          </a>
        </div>
        <div class="root-health-grid" id="root-health-grid">
          @for($i=0;$i<5;$i++)
            <div class="root-health-card" aria-hidden="true">
              <div class="root-health-icon"></div>
              <div style="flex:1">
                <div class="skeleton" style="height:10px;width:72px;margin-bottom:7px"></div>
                <div class="skeleton" style="height:18px;width:96px"></div>
              </div>
            </div>
          @endfor
        </div>
        <div class="root-attention-grid" id="root-attention-grid">
          @for($i=0;$i<5;$i++)
            <div class="root-attention-card" aria-hidden="true">
              <div class="root-attention-icon"></div>
              <div style="flex:1">
                <div class="skeleton" style="height:10px;width:82px;margin-bottom:7px"></div>
                <div class="skeleton" style="height:18px;width:62px"></div>
              </div>
            </div>
          @endfor
        </div>
        <div class="root-system-grid" id="root-system-grid">
          @for($i=0;$i<4;$i++)
            <div class="root-system-card" aria-hidden="true">
              <div class="root-system-icon"></div>
              <div style="flex:1">
                <div class="skeleton" style="height:10px;width:68px;margin-bottom:7px"></div>
                <div class="skeleton" style="height:18px;width:84px"></div>
              </div>
            </div>
          @endfor
        </div>
        <div class="root-activity-panel">
          <div class="root-activity-head">
            <div class="root-activity-heading">
              <span class="root-activity-icon"><i class="fa fa-list-alt" aria-hidden="true"></i></span>
              <h5 class="root-activity-title">Recent Admin Activity</h5>
            </div>
            <a href="{{ url('activity-logs') }}" class="root-activity-action">
              <span>View all logs</span>
              <i class="fa fa-chevron-right" aria-hidden="true"></i>
            </a>
          </div>
          <div class="table-responsive">
            <table class="root-activity-table">
              <thead>
                <tr>
                  <th>Type</th>
                  <th>Action</th>
                  <th>User</th>
                  <th>Time</th>
                </tr>
              </thead>
              <tbody id="root-activity-tbody">
                @for($i=0;$i<5;$i++)
                  <tr><td colspan="4"><div class="skeleton" style="height:24px"></div></td></tr>
                @endfor
              </tbody>
            </table>
          </div>
        </div>
        @else
        <div class="row" id="kpi-grid">
          {{-- skeletons --}}
          @for($i=0;$i<6;$i++)
            <div class="col-sm-6">
              <div class="kpi-tile" aria-hidden="true">
                <div class="kpi-icon"></div>
                <div style="flex:1">
                  <div class="skeleton" style="height:10px;width:80px;margin-bottom:6px"></div>
                  <div class="skeleton" style="height:18px;width:120px"></div>
                </div>
              </div>
            </div>
          @endfor
        </div>
        @endif
      </div>
    </div>
    @endif
  </div> {{-- <-- close Row 1 --}}
