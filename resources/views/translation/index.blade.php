@extends('layout.app')

@section('content')
    @include('layout.breadcrumb', [
        'data' => [['name' => "Translation", 'url' => '', 'active' => 'yes']]
    ])

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Manage Translations</div>

                    <div class="panel-body">
                        <a href="{{ secure_url('translation/add') }}" class="btn btn-primary pull-right">Add New Translation</a>
                        <br><br>
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>Language Name</th>
                                <th>Folder Name</th>
                                <th>Author</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($languages as $lang)
                                <tr>
                                    <td>{{ $lang['name'] }}</td>
                                    <td>{{ $lang['folder'] }}</td>
                                    <td>{{ $lang['author'] }}</td>
                                    <td>
                                        <a href="{{ secure_url('translation?edit=' . $lang['folder']) }}" class="btn btn-sm btn-primary">Edit</a>
                                        <a href="{{ secure_url('translation/remove/' . $lang['folder']) }}" class="btn btn-sm btn-danger">Delete</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
