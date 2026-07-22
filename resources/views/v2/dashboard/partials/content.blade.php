@include('v2.dashboard.retailer.header-sync')
<div class="container-fluid dashboard-v2-container {{ $isSuperAdmin ? 'root-dashboard-v2' : '' }} {{ $isRetailer ? 'retailer-dashboard retailer-dashboard-redesign' : '' }}">
  <div class="dashboard-ajax-error" id="dashboard-error">
    <span id="dashboard-error-message">{{ $isRetailer ? $retailerText['error_load'] : 'Dashboard data failed to load.' }}</span>
    <button type="button" class="dashboard-retry-btn" id="dashboard-retry">{{ $isRetailer ? $retailerText['retry'] : 'Retry' }}</button>
  </div>
  {{-- Row 1: Banner + KPI tiles --}}
  @if($isRetailer)
    @include('v2.dashboard.retailer.top')
  @else
    @include('v2.dashboard.admin.top')
  @endif
  @if($isRetailer)
    @include('v2.dashboard.retailer.quick-access')
  @endif
  @if($showBalances)
  {{-- Row 2: Provider balances --}}
  <div class="row">
    <div class="col-md-12">
      <div class="row" id="kpi-extra-grid">
        <div class="col-sm-4">
          <div class="kpi-tile">
            <div class="kpi-icon green"><i class="fa fa-bolt" aria-hidden="true"></i></div>
            <div>
              <p class="kpi-label">Reloadly</p>
              <p class="kpi-value" id="val-reloadly">€ —</p>
            </div>
          </div>
        </div>
        <div class="col-sm-4">
          <div class="kpi-tile">
            <div class="kpi-icon"><i class="fa fa-signal" aria-hidden="true"></i></div>
            <div>
              <p class="kpi-label">Ding</p>
              <p class="kpi-value" id="val-ding">€ —</p>
            </div>
          </div>
        </div>
        <div class="col-sm-4">
          <div class="kpi-tile">
            <div class="kpi-icon amber"><i class="fa fa-exchange" aria-hidden="true"></i></div>
            <div>
              <p class="kpi-label">TransferTo</p>
              <p class="kpi-value" id="val-transfer">€ —</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endif
  @if($showMonthlyChart)
  {{-- Row 3: Monthly Transactions --}}
  <div class="row">
    <div class="col-md-12">
      <div class="panel panel-modern">
        <div class="panel-heading">
          <strong>Monthly Transactions</strong>
        </div>
        <div class="panel-body">
          <div class="chart-wrap">
            <div id="chart-skel" class="skeleton" style="position:absolute;inset:0;"></div>
            <canvas id="monthlyTransactionsChart" style="display:none"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endif
  @if($showGlobalRange)
  {{-- Row 4: Global range toolbar + Custom Range Modal --}}
  <div class="row">
    <div class="col-md-12">
      <div class="panel panel-modern">
        <div class="panel-heading clearfix">
          <div class="pull-left" role="group" aria-label="Global range">
            <div id="global-range-group" class="btn-group btn-group-xs">
              <button class="btn btn-default active" data-range="today">Today</button>
              <button class="btn btn-default" data-range="last_week">Last Week</button>
              <button class="btn btn-default" data-range="last_month">Last Month</button>
              <button class="btn btn-default" data-range="last_3_months">Last 3 Months</button>
              <button class="btn btn-default" data-range="last_6_months">Last 6 Months</button>
              <button class="btn btn-default" data-range="custom" id="btn-custom-range">Custom…</button>
            </div>
            <span id="global-range-label" class="text-muted" style="margin-left:10px; font-size:12px;"></span>
          </div>
          <div class="pull-right">
            <button id="btn-global-refresh" class="btn btn-default btn-xs"><i class="fa fa-refresh"></i> Refresh</button>
          </div>
          <div class="clearfix"></div>
        </div>
      </div>
    </div>
  </div>
  @endif

  {{-- Row 5: Service totals + TT health + Total Margin --}}
  <div class="row">
    @if($showTopOps)
    <div class="col-md-3">
      <div class="panel panel-modern">
        <div class="panel-heading">
          <strong>Top 5 Operators</strong>
        </div>
        <div class="panel-body" style="height:350px">
          <div id="op-skel" class="skeleton" style="height:100%"></div>
          <canvas id="opChart" style="display:none"></canvas>
        </div>
      </div>
    </div>
    @endif
    @if($showServiceChart)
    <div class="col-md-3">
      <div class="panel panel-modern">
        <div class="panel-heading clearfix">
          <strong>Service</strong>
        </div>
        <div class="panel-body">
          <div class="chart-wrap" style="height:360px">
            <div id="svc-skel" class="skeleton" style="position:absolute;inset:0;"></div>
            <canvas id="svcMonthChart" style="display:none"></canvas>
          </div>
        </div>
      </div>
    </div>
    @endif
    @if($showTopupHealth)
    <div class="col-md-3">
      <div class="panel panel-modern">
        <div class="panel-heading clearfix">
          <strong>Tama Topup – Success vs Failed</strong>
        </div>
        <div class="panel-body">
          <div class="chart-wrap" style="height:300px">
            <div id="tt-orders-skel" class="skeleton" style="position:absolute;inset:0;"></div>
            <canvas id="ttOrdersPie" style="display:none"></canvas>
          </div>
          <div class="text-center" style="margin-top:6px"><small><strong>Orders</strong> (count)</small></div>
        </div>
      </div>
    </div>
    @endif
    @if($showMargin)
    <div class="col-md-3">
      <div class="panel panel-modern">
        <div class="panel-heading clearfix">
          <strong>Margin</strong>
        </div>
        <div class="panel-body">
          <div class="chart-wrap" style="height:360px">
            <div id="margin-skel" class="skeleton" style="position:absolute;inset:0;"></div>
            <canvas id="marginChart" style="display:none"></canvas>
          </div>
          <div id="marginTotals" class="text-right" style="margin-top:8px; color:#6b7280; font-size:12px;"></div>
        </div>
      </div>
    </div>
    @endif
  </div>
  @if($showLatestOrders)
  {{-- Row 6: Latest orders --}}
  <div class="row">
    <div class="col-md-12">
      <div class="panel panel-modern {{ $isRetailer ? 'retailer-latest-orders-panel' : '' }}">
        <div class="panel-heading clearfix">
          @if($isRetailer)
            <strong class="retailer-heading-title">
              <span class="retailer-heading-icon retailer-heading-icon-blue"><i class="fa fa-list-alt" aria-hidden="true"></i></span>
              <span>{{ $retailerText['latest_orders'] }}</span>
            </strong>
          @else
            <strong>Latest Orders</strong>
          @endif
          @if($isRetailer)
            <a class="retailer-orders-link" href="{{ url('orders-v2') }}">
              <span>{{ $retailerText['view_all_orders'] }}</span>
              <i class="fa fa-arrow-right" aria-hidden="true"></i>
            </a>
          @endif
        </div>
        <div class="panel-body">
          @if(!$isRetailer)
          <div class="retailer-orders-toolbar">
            <label class="retailer-order-field" for="retailer-order-search">
              <i class="fa fa-search" aria-hidden="true"></i>
              <input id="retailer-order-search" class="retailer-order-control" type="search" placeholder="{{ $isRetailer ? $retailerText['search_orders'] : 'Search orders' }}">
            </label>
            <select id="retailer-order-status" class="retailer-order-control" aria-label="{{ $isRetailer ? $retailerText['all_statuses'] : 'All statuses' }}">
              <option value="">{{ $isRetailer ? $retailerText['all_statuses'] : 'All statuses' }}</option>
              <option value="success">{{ $isRetailer ? $retailerText['status_success'] : 'Success' }}</option>
              <option value="pending">{{ $isRetailer ? $retailerText['status_pending'] : 'Pending' }}</option>
              <option value="failed">{{ $isRetailer ? $retailerText['status_failed'] : 'Failed' }}</option>
            </select>
            <select id="retailer-order-service" class="retailer-order-control" aria-label="{{ $isRetailer ? $retailerText['all_services'] : 'All services' }}">
              <option value="">{{ $isRetailer ? $retailerText['all_services'] : 'All services' }}</option>
            </select>
            <input id="retailer-order-from" class="retailer-order-control" type="date" aria-label="{{ $isRetailer ? $retailerText['from_date'] : 'From date' }}">
            <input id="retailer-order-to" class="retailer-order-control" type="date" aria-label="{{ $isRetailer ? $retailerText['to_date'] : 'To date' }}">
            <button id="retailer-order-export" class="retailer-orders-export" type="button">
              <i class="fa fa-download" aria-hidden="true"></i>
              <span>{{ $isRetailer ? $retailerText['export'] : 'Export' }}</span>
            </button>
          </div>
          @endif
          <div id="orders-results-meta" class="retailer-orders-meta"></div>
          <div class="table-responsive retailer-orders-table-wrap">
            <table id="retailer-orders-table" class="table table-striped table-modern table-hover">
              <thead>
                <tr>
                  <th style="min-width:140px">{{ $isRetailer ? $retailerText['date'] : 'Date' }}</th>
                  <th>{{ $isRetailer ? $retailerText['user'] : 'User' }}</th>
                  <th>{{ $isRetailer ? $retailerText['product'] : 'Product' }}</th>
                  <th>Service</th>
                  <th style="min-width:110px">{{ $isRetailer ? $retailerText['amount'] : 'Amount' }}</th>
                  <th>{{ $isRetailer ? $retailerText['status'] : 'Status' }}</th>
                </tr>
              </thead>
              <tbody id="orders-tbody">
                @for($i=0;$i<6;$i++)
                  <tr>
                    <td colspan="6"><div class="skeleton" style="height:34px"></div></td>
                  </tr>
                @endfor
              </tbody>
            </table>
          </div>
          <div id="orders-pagination" class="retailer-orders-pagination" aria-label="Orders pagination"></div>
        </div>
      </div>
    </div>
  </div>
  @endif

  @if($isRetailer)
    @include('v2.dashboard.retailer.charts')
  @endif

</div>
{{-- /container-fluid --}}

{{-- Custom Range Modal (Bootstrap 5) --}}
<div class="modal fade" id="customRangeModal" tabindex="-1" aria-labelledby="customRangeLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" style="border-radius:12px">
      <div class="modal-header">
        <h5 class="modal-title" id="customRangeLabel">Choose Date Range</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <p id="customRangeDesc" class="text-muted" style="margin-top:-4px">
          Pick a start and/or end date. Leaving one blank uses the other.
        </p>

        <form id="custom-range-form" novalidate>
          <div class="row gy-2">
            <div class="col-sm-6">
              <div class="mb-3">
                <label for="custom-from" class="form-label">From</label>
                <input type="date" id="custom-from" class="form-control" autocomplete="off" />
                <div class="invalid-feedback" id="from-help">Please choose a valid start date.</div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="mb-3">
                <label for="custom-to" class="form-label">To</label>
                <input type="date" id="custom-to" class="form-control" autocomplete="off" disabled />
                <div class="invalid-feedback" id="to-help">End date cannot be earlier than start date.</div>
              </div>
            </div>
          </div>
        </form>

        <p class="text-muted" style="margin-top:4px;font-size:12px">
          Tip: leave either field blank to use the other date.
        </p>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="apply-custom-range" class="btn btn-primary" disabled>Apply</button>
      </div>
    </div>
  </div>
</div>

{{-- FIX: proper Blade/JS for session var --}}
<script type="text/javascript">
  var session_layout = @json(session('layout'));
</script>
