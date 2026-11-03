@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
        ['name' => trans('myservice.vw_statistics'),'url'=> '','active' => 'yes']
    ]])
<link href="{{ secure_asset('vendor/datatables/datatables.css') }}" rel="stylesheet">

<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="clearfix">
        <h4 class="pull-left" style="margin-top:0">Bimedia — Balances</h4>

        {{-- Bootstrap 3/4 attributes (not data-bs-*) --}}
        <button class="btn btn-primary pull-right" data-toggle="modal" data-target="#addAccountModal">
          + Add Account
        </button>
      </div>

      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif
      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach($errors->all() as $e)
              <li>{{ $e }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <div class="panel panel-info">
        <div class="panel-heading"><h4 class="panel-title">Live Balances</h4></div>
        <div class="panel-body">
          <div class="table-responsive">
            <table id="bimedia_balances" class="table table-striped table-hover">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Account</th>
                  <th>COMCOD</th>
                  <th>TPVCOD</th>
                  <th>Client Code</th>
                  <th>Max (max_srd)</th>
                  <th>Remaining (conso_srd)</th>
                  <th>Balance (Max - Remaining)</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

{{-- Add Account Modal --}}
<div class="modal fade" id="addAccountModal" tabindex="-1" role="dialog" aria-labelledby="addAccountLabel">
  <div class="modal-dialog" role="document">
    <form method="POST" action="{{ route('bimedia.accounts.store') }}" class="modal-content">
      @csrf
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h4 class="modal-title" id="addAccountLabel">Add Bimedia Account</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Name</label>
          <input name="name" type="text" class="form-control" placeholder="Shop A" required>
        </div>
        <div class="form-group">
          <label>COMCOD</label>
          <input name="comcod" type="text" class="form-control" placeholder="COMXXXX" required>
        </div>
        <div class="form-group">
          <label>TPVCOD</label>
          <input name="tpvcod" type="text" class="form-control" placeholder="TPVXXXX" required>
        </div>
        <div class="form-group">
          <label>Authorization</label>
          <input name="authorization" type="text" class="form-control" placeholder="Basic dXNlcjpwYXNz OR just token" required>
          <p class="help-block">You can paste full <code>Basic ...</code> or just the token.</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Account</button>
      </div>
    </form>
  </div>
</div>

<script src="{{ secure_asset('vendor/datatables/datatables.js') }}"></script>
<script>
$(function(){
  var t = $('#bimedia_balances').DataTable({
    autoWidth: false,
    processing: true,
    serverSide: false, // we return all rows at once in AJAX
    ajax: {
      url: '{{ route('bimedia.balances.ajax') }}',
      dataSrc: 'data'
    },
    columns: [
      { data: null, orderable:false, searchable:false, render: function (d,t,r,meta){ return meta.row+1; } },
      { data: 'name' },
      { data: 'comcod' },
      { data: 'tpvcod' },
      { data: 'code_client' },
      { data: 'max_srd',     render: function(v){ return v==null ? '—' : parseFloat(v).toFixed(2); } },
      { data: 'conso_srd',   render: function(v){ return v==null ? '—' : parseFloat(v).toFixed(2); } },
      { data: 'balance',     render: function(v){ return v==null ? '—' : parseFloat(v).toFixed(2); } },
      { data: 'status' }
    ]
  });
});
</script>
@endsection
