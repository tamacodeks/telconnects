  <div class="retailer-quick-section">
    <div class="retailer-section-head">
      <h4 class="retailer-heading-title">
        <span class="retailer-heading-icon retailer-heading-icon-green"><i class="fa fa-bolt" aria-hidden="true"></i></span>
        <span>{{ $retailerText['quick_access'] }}</span>
      </h4>
    </div>
    <div class="retailer-actions-grid" id="retailer-actions-grid">
      @for($i=0;$i<3;$i++)
        <div class="retailer-action-card" aria-hidden="true">
          <span class="retailer-action-icon"></span>
          <span class="retailer-action-copy">
            <span class="skeleton" style="display:block;height:10px;width:82px;margin-bottom:8px"></span>
            <span class="skeleton" style="display:block;height:16px;width:118px"></span>
            <span class="skeleton" style="display:block;height:9px;width:150px;margin-top:8px"></span>
          </span>
          <span class="retailer-action-arrow"></span>
        </div>
      @endfor
    </div>
  </div>
