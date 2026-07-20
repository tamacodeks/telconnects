  <div class="row retailer-chart-row">
    <div class="col-md-6">
      <div class="panel panel-modern retailer-chart-panel">
        <div class="panel-heading">
          <strong>{{ $retailerText['top_selling_services'] }}</strong>
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
          <strong>{{ $retailerText['monthly_sales_chart'] }}</strong>
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
