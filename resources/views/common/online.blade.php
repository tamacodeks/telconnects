@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
        ['name' => "View Who is online",'url'=> '','active' => 'yes']
    ]])
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Who is Online?
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table id="users-table" class="table table-condensed">
                                <thead>
                                <tr>
                                    <th>Sl</th>
                                    <th>user ID</th>
                                    <th>username</th>
                                    <th>Last Login</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php($sl=1)
                                @forelse($users as $user)
                                    <tr>
                                        <td>{{ $sl }}</td>
                                        <td>{{ $user->id }}</td>
                                        <td>{{ $user->username }}</td>
                                        @if($user->last_activity == 'NULL')
                                            <td>Not Login Yet</td>
                                        @else
                                            <td>{{ $user->last_activity }}</td>
                                        @endif

                                        @if($user->isOnline())
                                            <td>
                                                <label class="label label-primary">Online</label>
                                            </td>
                                        @else
                                            <td>
                                                <label class="label label-danger">Offline</label>
                                            </td>
                                        @endif

                                    </tr>

                                </tbody>
                                @php($sl++)
                                @empty
                                @endforelse
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection