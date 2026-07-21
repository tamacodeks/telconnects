<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3"></script>
<script>
/* -------- Retailer charts after latest orders -------- */
(function($){
  var servicesChartRef = null;
  var monthlySalesChartRef = null;
  var servicesCanvasEl = document.getElementById('retailerTopServicesChart');
  var monthlySalesCanvasEl = document.getElementById('retailerMonthlySalesChart');
  if (!servicesCanvasEl && !monthlySalesCanvasEl) return;

  function isDarkMode(){
    return document.body.classList.contains('dark-only') ||
      document.documentElement.classList.contains('dark') ||
      document.body.getAttribute('data-bs-theme') === 'dark' ||
      document.documentElement.getAttribute('data-bs-theme') === 'dark';
  }

  function cssVar(name, fallback){
    var value = window.getComputedStyle(document.documentElement).getPropertyValue(name);
    value = String(value || '').trim();
    return value || fallback;
  }

  function rgbVar(name, fallback){
    var value = cssVar(name, fallback || '');
    if (!value) return fallback || '37, 99, 235';
    return value.replace(/\s+/g, ' ').trim();
  }

  function rgbaVar(name, alpha, fallback){
    return 'rgba(' + rgbVar(name, fallback) + ', ' + alpha + ')';
  }

  function alphaToSolid(color){
    return String(color || '').replace(/,\s*0?\.\d+\)$/, ', 1)');
  }

  function chartPalette(){
    return [
      rgbaVar('--theme-primary-rgb', .86, '37, 99, 235'),
      rgbaVar('--theme-accent-rgb', .86, '56, 189, 248'),
      rgbaVar('--theme-sidebar-active-rgb', .86, '22, 165, 107'),
      rgbaVar('--theme-header-rgb', .86, '124, 58, 237'),
      rgbaVar('--theme-login-rgb', .86, '217, 31, 47'),
      rgbaVar('--theme-dashboard-muted-rgb', .82, '100, 116, 139')
    ];
  }

  function chartTheme(){
    var dark = isDarkMode();
    return {
      text: cssVar(dark ? '--theme-dark-muted' : '--theme-dashboard-muted', dark ? '#c7d3e7' : '#536986'),
      grid: rgbaVar(dark ? '--theme-dark-border-rgb' : '--theme-dashboard-border-rgb', dark ? .18 : .10, dark ? '148, 163, 184' : '16, 47, 93'),
      blue: rgbaVar('--theme-primary-rgb', dark ? .92 : .86, '37, 99, 235'),
      blueSoft: rgbaVar('--theme-primary-rgb', dark ? .22 : .12, '37, 99, 235'),
      green: rgbaVar('--theme-accent-rgb', dark ? .90 : .84, '56, 189, 248'),
      amber: rgbaVar('--theme-sidebar-active-rgb', dark ? .90 : .84, '22, 165, 107'),
      purple: rgbaVar('--theme-header-rgb', dark ? .90 : .84, '124, 58, 237'),
      danger: rgbaVar('--theme-login-rgb', dark ? .90 : .84, '217, 31, 47'),
      surface: cssVar(dark ? '--theme-dark-card' : '--theme-dashboard-card', dark ? '#0f172a' : '#ffffff')
    };
  }

  function euro(value){
    var amount = Number(value) || 0;
    return '\u20ac ' + amount.toLocaleString(undefined, { maximumFractionDigits: 2 });
  }

  function drawRetailerTopServices(){
    if (!servicesCanvasEl) return;
    $('#retailer-services-skel').show();
    $('#retailerTopServicesChart').hide();
    dashboardFetch('/dashboard/service-monthly', { range: 'last_6_months' }, {
      keepOld: true,
      onData: function(res){
        if (!res) return;
        var theme = chartTheme();
        var palette = chartPalette();
        var items = (res.items || []).slice(0, 8);
        var labels = items.map(function(item){ return item.label || '-'; });
        var values = items.map(function(item){ return Number(item.total_amount) || 0; });

        if (servicesChartRef && servicesChartRef.destroy) servicesChartRef.destroy();
        $('#retailer-services-skel').hide();
        $('#retailerTopServicesChart').show();
        servicesChartRef = new Chart(servicesCanvasEl.getContext('2d'), {
          type: 'bar',
          data: {
            labels: labels,
            datasets: [{
              data: values,
              borderWidth: 1,
              borderRadius: 8,
              maxBarThickness: 34,
              backgroundColor: values.map(function(_, index){ return palette[index % palette.length]; }),
              borderColor: values.map(function(_, index){ return alphaToSolid(palette[index % palette.length]); })
            }]
          },
          options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { display: false },
              tooltip: { callbacks: { label: function(ctx){ return ' ' + euro(ctx.parsed.x); } } }
            },
            scales: {
              x: {
                beginAtZero: true,
                ticks: { color: theme.text, callback: function(v){ return euro(v); } },
                grid: { color: theme.grid }
              },
              y: {
                ticks: { color: theme.text },
                grid: { display: false }
              }
            }
          }
        });
      }
    });
  }

  function drawRetailerMonthlySales(){
    if (!monthlySalesCanvasEl) return;
    $('#retailer-monthly-sales-skel').show();
    $('#retailerMonthlySalesChart').hide();
    dashboardFetch('/dashboard/monthly-transactions', { range: 'last_6_months' }, {
      keepOld: true,
      onData: function(res){
        if (!res) return;
        var theme = chartTheme();
        if (monthlySalesChartRef && monthlySalesChartRef.destroy) monthlySalesChartRef.destroy();
        $('#retailer-monthly-sales-skel').hide();
        $('#retailerMonthlySalesChart').show();
        monthlySalesChartRef = new Chart(monthlySalesCanvasEl.getContext('2d'), {
          type: 'line',
          data: {
            labels: res.labels || [],
            datasets: [{
              label: 'Monthly sales',
              data: res.data || [],
              borderWidth: 2,
              tension: .32,
              pointRadius: 3,
              pointHoverRadius: 5,
              fill: true,
              backgroundColor: theme.blueSoft,
              borderColor: theme.blue,
              pointBackgroundColor: theme.blue,
              pointBorderColor: theme.surface
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
              legend: { display: false },
              tooltip: { callbacks: { label: function(ctx){ return ' ' + euro(ctx.parsed.y); } } }
            },
            scales: {
              x: {
                ticks: { color: theme.text, maxRotation: 0, autoSkip: true },
                grid: { color: theme.grid }
              },
              y: {
                beginAtZero: true,
                ticks: { color: theme.text, callback: function(v){ return euro(v); } },
                grid: { color: theme.grid }
              }
            }
          }
        });
      }
    });
  }

  lazyRun('retailerTopServicesChartWrap', drawRetailerTopServices);
  lazyRun('retailerMonthlySalesChartWrap', drawRetailerMonthlySales);
})(jQuery);

/* -------- Totals by Service (bar, horizontal) -------- */
(function($){
  var svcChart = null;
  var svcCanvasEl = document.getElementById('svcMonthChart');
  if (!svcCanvasEl) return;

  function drawSvc(params){
    $('#svc-skel').show();
    $('#svcMonthChart').hide();
    dashboardFetch('/dashboard/service-monthly', params, { keepOld: true, onData: function(res){
      if (!res) return;
      var theme = chartTheme();
      var palette = chartPalette();
      var items  = res.items || [];
      var labels = items.map(function(it){ return it.label; });
      var values = items.map(function(it){ return Number(it.total_amount) || 0; });

      if (svcChart && svcChart.destroy) svcChart.destroy();
      svcChart = new Chart(svcCanvasEl.getContext('2d'), {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
            label: '\u20ac Amount',
            data: values,
            backgroundColor: values.map(function(_, index){ return palette[index % palette.length]; }),
            borderColor: values.map(function(_, index){ return alphaToSolid(palette[index % palette.length]); }),
            borderWidth: 1,
            borderRadius: 6,
            maxBarThickness: 48
          }]
        },
        options: {
          indexAxis: 'y',
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: function(ctx){ var v = Number(ctx.parsed.x) || 0; return ' \u20ac ' + v.toLocaleString(); } } }
          },
          scales: {
            x: {
              beginAtZero: true,
              ticks: { color: theme.text, callback: function(v){ return '\u20ac ' + Number(v).toLocaleString(); } },
              grid: { color: theme.grid }
            },
            y: {
              ticks: { color: theme.text },
              grid: { display: false }
            }
          }
        }
      });
      $('#svc-skel').hide();
      $('#svcMonthChart').show();
    }});
  }

  window.registerRangeListener(debounce(function(params){ lazyRun('svcMonthChart', function(){ drawSvc(params); }); }, 180));
})(jQuery);

/* -------- Tama Topup Health (pie) -------- */
(function($){
  var ttOrdersPie = null;
  var ttOrdersCanvasEl = document.getElementById('ttOrdersPie');
  if (!ttOrdersCanvasEl) return;

  function drawTT(params){
    $('#tt-orders-skel').show();
    $('#ttOrdersPie').hide();
    dashboardFetch('/dashboard/topup-health', params, { keepOld: true, onData: function(res){
      if (!res) return;
      var theme = chartTheme();
      var counts = [ Number(res.success_count) || 0, Number(res.failed_count) || 0 ];
      var total = counts[0] + counts[1];
      if (ttOrdersPie && ttOrdersPie.destroy) ttOrdersPie.destroy();
      ttOrdersPie = new Chart(ttOrdersCanvasEl.getContext('2d'), {
        type: 'pie',
        data: {
          labels: [DASH_I18N.status_success || 'Success', DASH_I18N.status_failed || 'Failed'],
          datasets: [{
            data: counts,
            backgroundColor: [theme.green, theme.danger],
            borderColor: [alphaToSolid(theme.green), alphaToSolid(theme.danger)],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'bottom', labels: { color: theme.text } },
            tooltip: { callbacks: { label: function(ctx){ var v = ctx.parsed || 0, pct = total ? (v * 100 / total) : 0; return ' ' + ctx.label + ': ' + v.toLocaleString() + ' (' + pct.toFixed(1) + '%)'; } } }
          }
        }
      });
      $('#tt-orders-skel').hide();
      $('#ttOrdersPie').show();
    }});
  }

  window.registerRangeListener(debounce(function(params){ lazyRun('ttOrdersPie', function(){ drawTT(params); }); }, 180));
})(jQuery);

/* -------- Total Margin (line) -------- */
(function($){
  var marginChartRef = null;
  var marginCanvasEl = document.getElementById('marginChart');
  if (!marginCanvasEl) return;

  function euro(n){
    if (n == null) return '\u20ac -';
    var x = Number(n) || 0;
    return '\u20ac ' + x.toLocaleString();
  }

  function drawMargin(params){
    $('#margin-skel').show();
    $('#marginChart').hide();
    dashboardFetch('/dashboard/margins', params, { keepOld: true, onData: function(res){
      if (!res) return;
      var theme = chartTheme();
      var labels = res.labels || [];
      var margin = (res.series && res.series.margin) ? res.series.margin : [];
      var buy = (res.series && res.series.buy) ? res.series.buy : [];
      var sale = (res.series && res.series.sale) ? res.series.sale : [];

      if (marginChartRef && marginChartRef.destroy) marginChartRef.destroy();
      marginChartRef = new Chart(marginCanvasEl.getContext('2d'), {
        type: 'line',
        data: {
          labels: labels,
          datasets: [
            { label: 'Margin', data: margin, borderWidth: 2, pointRadius: 2, tension: .25, fill: true, backgroundColor: theme.blueSoft, borderColor: alphaToSolid(theme.green) },
            { label: 'Sale', data: sale, borderWidth: 1, pointRadius: 1.5, tension: .25, fill: false, borderColor: alphaToSolid(theme.blue) },
            { label: 'Buy', data: buy, borderWidth: 1, pointRadius: 1.5, tension: .25, fill: false, borderColor: alphaToSolid(theme.purple) }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: { mode: 'index', intersect: false },
          plugins: {
            legend: { display: true, labels: { color: theme.text } },
            tooltip: { callbacks: { label: function(ctx){ var v = Number(ctx.parsed.y) || 0; var name = ctx.dataset.label || ''; return ' ' + name + ': ' + euro(v); } } }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: { color: theme.text, callback: function(v){ return '\u20ac ' + Number(v).toLocaleString(); } },
              grid: { color: theme.grid }
            },
            x: {
              ticks: { color: theme.text, maxRotation: 0, autoSkip: true },
              grid: { color: theme.grid }
            }
          }
        }
      });

      var t = res.totals || {};
      $('#marginTotals').html(
        '<span><strong>Total Margin:</strong> ' + euro(t.margin) + '</span>' + ' &nbsp; | &nbsp; ' +
        '<span>Sale: ' + euro(t.sale) + '</span>' + ' &nbsp; | &nbsp; ' +
        '<span>Buy: ' + euro(t.buy) + '</span>'
      );

      $('#margin-skel').hide();
      $('#marginChart').show();
    }});
  }

  window.registerRangeListener(debounce(function(params){ lazyRun('marginChart', function(){ drawMargin(params); }); }, 180));
})(jQuery);

/* -------- Top 5 Operators (bar) -------- */
(function($){
  var opChartRef = null;
  var opCanvasEl = document.getElementById('opChart');
  if (!opCanvasEl) return;

  function drawTopOps(params){
    $('#op-skel').show();
    $('#opChart').hide();
    dashboardFetch('/dashboard/top-sales', params, { keepOld: true, onData: function(d){
      if (!d) return;
      var theme = chartTheme();
      var palette = chartPalette();
      var ops = (d.top_sales_tt || []).slice().sort(function(a, b){ return (b.total_sales || 0) - (a.total_sales || 0); }).slice(0, 5);
      var labels = ops.map(function(x){ return x.operator; });
      var values = ops.map(function(x){ return Number(x.total_sales) || 0; });

      if (opChartRef && opChartRef.destroy) opChartRef.destroy();
      opChartRef = new Chart(opCanvasEl.getContext('2d'), {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
            data: values,
            backgroundColor: values.map(function(_, index){ return palette[index % palette.length]; }),
            borderColor: values.map(function(_, index){ return alphaToSolid(palette[index % palette.length]); }),
            borderWidth: 1,
            borderRadius: 6,
            maxBarThickness: 48
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: function(ctx){ return (ctx.parsed.y || 0).toLocaleString(); } } }
          },
          scales: {
            x: { ticks: { color: theme.text }, grid: { display: false } },
            y: { beginAtZero: true, ticks: { color: theme.text, callback: function(v){ return v.toLocaleString(); } }, grid: { color: theme.grid } }
          }
        }
      });

      $('#op-skel').hide();
      $('#opChart').show();
    }});
  }

  window.registerRangeListener(debounce(function(params){ lazyRun('opChart', function(){ drawTopOps(params); }); }, 180));
})(jQuery);
</script>
