@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
        ['name' => trans('common.notifications'),'url'=> '','active' => 'yes']
    ]
    ])
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="">
                            <div class="pull-right">
                                <a href="{{ secure_url('notifications/mark-all-as-read') }}" class="btn btn-default m-b-10"><i class="fa fa-envelope"></i> &nbsp;{{ trans('common.mark_all_as_read') }}</a>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-inbox table-hover">
                                <tbody>
                                @if(isset($notifications) && collect($notifications)->count() > 0)
                                    @php($sl=1)
                                    @foreach($notifications as $notification)
                                        <tr class="clickable-row @if($notification->is_read != 1)unread @endif" data-href='{{ $notification->url."?read=true&notification=".$notification->id }}'>
                                            <td class="inbox-small-cells">
                                                {{ $sl }}
                                            </td>
                                            <td class="view-message dont-show">{{ $notification->username }}</td>
                                            <td class="view-message inbox-small-cells">{!! \app\Library\AppHelper::notification_badge($notification->type) !!}</td>
                                            <td class="view-message">
                                                <span data-trigger="hover" data-container="body" data-toggle="popover" data-placement="top" data-content="{{ $notification->message }}" data-original-title="{{ $notification->username }}" title="">{{ \app\Library\AppHelper::doTrim_text($notification->message,50)  }}</span>
                                            </td>
                                            <td class="view-message text-right">{{ $notification->created_at }}</td>
                                        </tr>
                                        @php($sl++)
                                    @endforeach
                                @else

                                @endif
                                </tbody>
                            </table>
                            <div class="pull-right">
                                @if(isset($notifications) && collect($notifications)->count() > 0)
                                    {{ $notifications->links() }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function($) {
            $(".clickable-row").click(function() {
                window.location = $(this).data("href");
            });
        });
    </script>
@endsection