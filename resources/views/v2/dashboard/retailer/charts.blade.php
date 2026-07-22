  <div class="row retailer-chart-row">
    <div class="col-md-6">
      <div class="panel panel-modern retailer-chart-panel">
        <div class="panel-heading">
          <strong class="retailer-heading-title">
            <span class="retailer-heading-icon retailer-heading-icon-green"><i class="fa fa-bar-chart" aria-hidden="true"></i></span>
            <span>{{ $retailerText['top_selling_services'] }}</span>
          </strong>
          <span class="retailer-chart-subtitle">{{ $retailerText['top_selling_services_subtitle'] }}</span>
        </div>
        <div class="panel-body">
          <div class="retailer-chart-wrap" id="retailerTopServicesChartWrap">
            <div id="retailer-services-skel" class="skeleton" style="position:absolute;inset:0;"></div>
            <canvas id="retailerTopServicesChart" style="display:none"></canvas>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="panel panel-modern retailer-chart-panel">
        <div class="panel-heading">
          <strong class="retailer-heading-title">
            <span class="retailer-heading-icon retailer-heading-icon-blue"><i class="fa fa-line-chart" aria-hidden="true"></i></span>
            <span>{{ $retailerText['monthly_sales_chart'] }}</span>
          </strong>
          <span class="retailer-chart-subtitle">{{ $retailerText['monthly_sales_chart_subtitle'] }}</span>
        </div>
        <div class="panel-body">
          <div class="retailer-chart-wrap" id="retailerMonthlySalesChartWrap">
            <div id="retailer-monthly-sales-skel" class="skeleton" style="position:absolute;inset:0;"></div>
            <canvas id="retailerMonthlySalesChart" style="display:none"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>
