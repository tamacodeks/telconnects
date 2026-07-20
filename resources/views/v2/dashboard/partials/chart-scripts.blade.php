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

  function chartTheme(){
    var dark = isDarkMode();
    return {
      text: dark ? '#c7d3e7' : '#536986',
      grid: dark ? 'rgba(148,163,184,.13)' : 'rgba(16,47,93,.08)',
      blue: dark ? 'rgba(31,111,255,.90)' : 'rgba(18,100,255,.86)',
      blueSoft: dark ? 'rgba(31,111,255,.16)' : 'rgba(18,100,255,.12)',
      green: dark ? 'rgba(53,209,124,.86)' : 'rgba(22,164,93,.82)',
      amber: dark ? 'rgba(255,179,61,.86)' : 'rgba(214,137,16,.82)',
      purple: dark ? 'rgba(176,140,255,.86)' : 'rgba(115,85,239,.82)'
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
              backgroundColor: [theme.blue, theme.green, theme.amber, theme.purple, theme.blue, theme.green, theme.amber, theme.purple],
              borderColor: [theme.blue, theme.green, theme.amber, theme.purple, theme.blue, theme.green, theme.amber, theme.purple]
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
              pointBorderColor: '#ffffff'
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
    $('#svc-skel').show(); $('#svcMonthChart').hide();
    dashboardFetch('/dashboard/service-monthly', params, { keepOld: true, onData: function(res){
      if (!res) return;
      var items  = res.items || [];
      var labels = items.map(function(it){ return it.label; });
      var values = items.map(function(it){ return Number(it.total_amount) || 0; });

      if (svcChart && svcChart.destroy) svcChart.destroy();
      var ctx = svcCanvasEl.getContext('2d');
      svcChart = new Chart(ctx, {
        type:'bar',
        data:{ labels:labels, datasets:[{ label:'€ Amount', data:values,
          backgroundColor:[
            'rgba(23,100,168,0.85)','rgba(35,186,206,0.85)','rgba(240,173,78,0.85)',
            'rgba(228,90,90,0.85)','rgba(72,144,212,0.85)','rgba(138,108,183,0.85)',
            'rgba(31,168,174,0.85)','rgba(160,160,160,0.85)','rgba(95,130,160,0.85)','rgba(107,114,128,0.8)'
          ],
          borderColor:[
            'rgba(23,100,168,1)','rgba(35,186,206,1)','rgba(240,173,78,1)',
            'rgba(228,90,90,1)','rgba(72,144,212,1)','rgba(138,108,183,1)',
            'rgba(31,168,174,1)','rgba(160,160,160,1)','rgba(95,130,160,1)','rgba(107,114,128,1)'
          ],
          borderWidth:1, borderRadius:6, maxBarThickness:48
        }]},
        options:{
          indexAxis:'y', responsive:true, maintainAspectRatio:false,
          plugins:{ legend:{ display:false }, tooltip:{ callbacks:{ label:function(ctx){ var v=Number(ctx.parsed.x)||0; return ' € '+v.toLocaleString(); } } } },
          scales:{ x:{ beginAtZero:true, ticks:{ callback:function(v){ return '€ '+Number(v).toLocaleString(); } }, grid:{ color:'rgba(17,24,39,.08)' } }, y:{ grid:{ display:false } } }
        }
      });
      $('#svc-skel').hide(); $('#svcMonthChart').show();
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
    $('#tt-orders-skel').show(); $('#ttOrdersPie').hide();
    dashboardFetch('/dashboard/topup-health', params, { keepOld: true, onData: function(res){
      if (!res) return;
      var counts = [ Number(res.success_count)||0, Number(res.failed_count)||0 ];
      var total  = counts[0] + counts[1];
      if (ttOrdersPie && ttOrdersPie.destroy) ttOrdersPie.destroy();
      ttOrdersPie = new Chart(ttOrdersCanvasEl.getContext('2d'), {
        type:'pie',
        data:{ labels:['Success','Failed'], datasets:[{ data:counts,
          backgroundColor:['rgba(35,186,206,0.85)','rgba(228,90,90,0.85)'],
          borderColor:['rgba(35,186,206,1)','rgba(228,90,90,1)'], borderWidth:1 }] },
        options:{
          responsive:true, maintainAspectRatio:false,
          plugins:{ legend:{ position:'bottom' }, tooltip:{ callbacks:{ label:function(ctx){ var v=ctx.parsed||0, pct= total? (v*100/total):0; return ' '+ctx.label+': '+v.toLocaleString()+' ('+pct.toFixed(1)+'%)'; } } } }
        }
      });
      $('#tt-orders-skel').hide(); $('#ttOrdersPie').show();
    }});
  }
  window.registerRangeListener(debounce(function(params){ lazyRun('ttOrdersPie', function(){ drawTT(params); }); }, 180));
})(jQuery);

/* -------- Total Margin (line) -------- */
(function($){
  var marginChartRef = null;
  var marginCanvasEl = document.getElementById('marginChart');
  if (!marginCanvasEl) return;
  function euro(n){ if(n==null) return '€ —'; var x=Number(n)||0; return '€ '+x.toLocaleString(); }
  function drawMargin(params){
    $('#margin-skel').show(); $('#marginChart').hide();
    dashboardFetch('/dashboard/margins', params, { keepOld: true, onData: function(res){
      if (!res) return;
      var labels = res.labels || [];
      var margin = (res.series && res.series.margin) ? res.series.margin : [];
      var buy    = (res.series && res.series.buy) ? res.series.buy : [];
      var sale   = (res.series && res.series.sale) ? res.series.sale : [];

      if (marginChartRef && marginChartRef.destroy) marginChartRef.destroy();
      var ctx = marginCanvasEl.getContext('2d');
      marginChartRef = new Chart(ctx, {
        type:'line',
        data:{ labels: labels, datasets:[
          { label:'Margin', data: margin, borderWidth:2, pointRadius:2, tension:.25, fill:true, backgroundColor:'rgba(35,186,206,0.12)', borderColor:'rgba(35,186,206,1)' },
          { label:'Sale',   data: sale,   borderWidth:1, pointRadius:1.5, tension:.25, fill:false, borderColor:'rgba(23,100,168,1)' },
          { label:'Buy',    data: buy,    borderWidth:1, pointRadius:1.5, tension:.25, fill:false, borderColor:'rgba(107,114,128,1)' }
        ]},
        options:{
          responsive:true, maintainAspectRatio:false,
          interaction:{ mode:'index', intersect:false },
          plugins:{ legend:{ display:true }, tooltip:{ callbacks:{ label: function(ctx){ var v = Number(ctx.parsed.y)||0; var name = ctx.dataset.label || ''; return ' '+name+': '+euro(v); } } } },
          scales:{ y:{ beginAtZero:true, ticks:{ callback:function(v){ return '€ '+Number(v).toLocaleString(); } } }, x:{ ticks:{ maxRotation:0, autoSkip:true } } }
        }
      });

      var t = res.totals || {};
      $('#marginTotals').html(
        '<span><strong>Total Margin:</strong> '+euro(t.margin)+'</span>'+' &nbsp; | &nbsp; '+
        '<span>Sale: '+euro(t.sale)+'</span>'+' &nbsp; | &nbsp; '+
        '<span>Buy: '+euro(t.buy)+'</span>'
      );

      $('#margin-skel').hide(); $('#marginChart').show();
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
    $('#op-skel').show(); $('#opChart').hide();
    dashboardFetch('/dashboard/top-sales', params, { keepOld: true, onData: function(d){
      if (!d) return;
      var ops = (d.top_sales_tt || []).slice().sort(function(a,b){ return (b.total_sales||0)-(a.total_sales||0); }).slice(0,5);
      var labels = ops.map(function(x){ return x.operator; });
      var values = ops.map(function(x){ return Number(x.total_sales)||0; });

      var ctx = opCanvasEl.getContext('2d');
      if (opChartRef && opChartRef.destroy) opChartRef.destroy();
      opChartRef = new Chart(ctx, {
        type:'bar',
        data:{ labels: labels, datasets:[{ data: values, backgroundColor:[
          'rgba(23,100,168,0.85)','rgba(35,186,206,0.85)','rgba(240,173,78,0.85)','rgba(228,90,90,0.85)','rgba(72,144,212,0.85)'
        ], borderColor:[
          'rgba(23,100,168,1)','rgba(35,186,206,1)','rgba(240,173,78,1)','rgba(228,90,90,1)','rgba(72,144,212,1)'
        ], borderWidth:1, borderRadius:6, maxBarThickness:48 }] },
        options:{
          responsive:true, maintainAspectRatio:false,
          plugins:{ legend:{display:false}, tooltip:{ callbacks:{ label:function(ctx){ return (ctx.parsed.y||0).toLocaleString(); } } } },
          scales:{ x:{ grid:{display:false}}, y:{ beginAtZero:true, ticks:{ callback:function(v){ return v.toLocaleString(); } } } }
        }
      });

      $('#op-skel').hide(); $('#opChart').show();
    }});
  }
  window.registerRangeListener(debounce(function(params){ lazyRun('opChart', function(){ drawTopOps(params); }); }, 180));
})(jQuery);
</script>
