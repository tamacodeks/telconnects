<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ isset($page_title) ? $page_title : APP_NAME }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ secure_asset('favicon.ico') }}">
    <!--- Styles -->
    <link href="{{ secure_asset('css/bootstrap.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('css/app.css'). '?v=' . rand(10000,99999) }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ secure_asset('vendor/font-awesome/css/font-awesome.css') }}">
    <link rel="stylesheet" href="{{ secure_asset('vendor/pace/themes/white/pace-theme-flash.css') }}">
    <link rel="stylesheet" href="{{ secure_asset('vendor/jquery-confirm/jquery-confirm.min.css') }}">
    <!-- jQuery  -->
    <script src="{{ secure_asset('vendor/jquery/jquery-3.3.1.js') }}"></script>
    <!-- Scripts -->
    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
            'user' =>  auth()->user()->username
        ]) !!};
        var fetchChatURL = null;
    </script>
</head>
<body id="tamaapp">
<div class="se-pre-con"></div>
@include('layout.header')


<div id="wrapper">
    <!-- Sidebar -->
@include('layout.aside')
<!--/.sidebar-nav -->
    <!-- Page Content -->
    <div id="page-wrapper6">
        @yield('content')
    </div>
</div><!-- /#wrapper -->
<div class="settings panel panel-default"><!-- settings -->
    <a class="btn btn-default no-shadow pos-abt ng-scope" href="{{ secure_url('inbox') }}">
        <i class="fa fa-comments"></i>
    </a>
</div>
<audio id="inNote"><source src="{{ secure_asset('others/new_notification.mp3') }}" type="audio/mpeg"></audio>
<!-- /#wrapper -->
<!-- Bootstrap Js CDN -->
<script src="{{ secure_asset('js/bootstrap.min.js') }}"></script>
<script src="{{ secure_asset('vendor/jquery-validator/jquery.validate.min.js') }}"></script>
<script src="{{ secure_asset('vendor/common/loadingoverlay.min.js') }}"></script>
<script src="{{ secure_asset('vendor/pace/pace.min.js') }}"></script>
<script src="{{ secure_asset('vendor/jquery-confirm/jquery-confirm.min.js') }}"></script>
<script src="{{ secure_asset('vendor/common/autoNumeric-1.9.41.js') }}"></script>
<script src="{{ secure_asset('js/socket.io.js') }}"></script>
<script>
    var base_url = "{{ secure_url('/') }}/";
    var notificationsWrapper   = $('.dropdown-notifications');
    var notificationsToggle    = notificationsWrapper.find('a[data-toggle]');
    var notificationsCountElem = notificationsToggle.find('i[data-count]');
    var notificationsCount     = parseInt(notificationsCountElem.data('count'));
    var notifications          = notificationsWrapper.find('ul.dropdown-menu');

    if (notificationsCount <= 0) {
        notificationsWrapper.hide();
    }
    var socketServerUrl = "{{ env('SOCKET_SERVER_URL', 'http://192.168.0.147:6001') }}";
    var socketAuthToken = "{{ auth()->user()->api_token }}";
    if (socketServerUrl && socketAuthToken) {
        var options = {
            auth: {
                headers: {'Authorization': 'Bearer ' + socketAuthToken}
            },
            reconnection: true,
            reconnectionDelay: 1000,
            reconnectionDelayMax : 5000,
            reconnectionAttempts: 5
        };
        var socket = io(socketServerUrl, options);
        socket.emit('subscribe', {
            channel: 'private-notify-user-{{ auth()->user()->id  }}',
            auth: options.auth
        }).on('App\\Events\\NotifyUser', function(channel, data) {
        var existingNotifications = notifications.html();
        var icon = '';
        if(data.data.type == 'message'){
            icon = "fa fa-comments fa-3x";
        }else if(data.data.type == 'payment'){
            icon = "fa fa-money-bill-alt fa-3x";
        }else if(data.data.type == 'enquiry'){
            icon = "fa fa-envelope fa-3x";
        }else if(data.data.type == 'request'){
            icon = "fa fa-credit-card fa-3x";
        }else{
            icon = "fa fa-info-circle fa-3x";
        }
        var newNotificationHtml = '<li class="notification"><a href="'+data.data.url+'?read=true&notification='+data.data.id+'"><div class="media"><div class="media-left"><div class="media-object"><i class="'+icon+'"></i></div></div><div class="media-body"><strong class="notification-title">'+data.data.title+'</strong><div class="notification-meta"><small class="timestamp">'+data.data.created_at+'</small></div></div></div></a></li>';
        notifications.html(newNotificationHtml + existingNotifications);
        notificationsCount += 1;
        notificationsCountElem.attr('data-count', notificationsCount);
        notificationsWrapper.find('.notif-count').text(notificationsCount);
        notificationsWrapper.show();
        $('#inNote')[0].play();
        });
    }

    $(document).ready(function () {
        $(".se-pre-con").fadeOut("slow");;
        // $('body').loading('stop');
        $("body").tooltip({selector: '[data-toggle=tooltip]'});
        @if(\Session::has('message'))
        $.alert({
            title: "{{ ucfirst(session('message_type'))  }}",
            content: "{!! session('message') !!}",
            buttons: {
                "{{ trans('common.btn_close') }}": function () {

                }
            },
            backgroundDismiss: true, // this will just close the modal
            theme: 'material',
            animation: 'zoom',
            closeAnimation: 'bottom',
            escapeKey: '{{ trans('common.btn_close') }}',
            type: '{{ \app\Library\AppHelper::notification_type(session('message_type'),false) }}',
            icon: '{{ \app\Library\AppHelper::notification_type(session('message_type'),true) }}'
        });
        @endif


    });

    function AppModal(url, title, className) {
        $.dialog({
            content: function () {
                var self = this;
                return $.ajax({
                    url: url,
                    method: 'GET'
                }).done(function (response) {
                    self.setContent(response);
                    self.setTitle(title);
                }).fail(function () {
                    self.setContent('Something went wrong.');
                });
            },
            columnClass: 'medium',
            theme: 'bootstrap', // 'material', 'bootstrap','dark','light',
            useBootstrap: true,
            escapeKey: '{{ strtolower(trans('common.btn_close')) }}',
            draggable: false,
            animation: 'zoom',
            closeAnimation: 'bottom',
            buttons: {
                "{{ strtolower(trans('common.btn_close')) }}": function () {

                }
            }
        });
    }
    function AppConfirmDelete(url, title, dialog) {
        $.confirm({
            title: title,
            content: dialog,
            autoClose: '{{ trans('common.btn_cancel') }}',
            theme: 'material', // 'material', 'bootstrap','dark','light'
            type: 'red',
            icon: "fa fa-exclamation-circle",
            escapeKey: '{{ trans('common.btn_cancel') }}',
            buttons: {
                "{{ trans('common.btn_delete') }}": {
                    text: '{{ trans('common.btn_delete') }}',
                    btnClass: 'btn-red',
                    keys: ['enter'],
                    action: function () {
                        var csrf = $('meta[name="csrf-token"]').attr('content');
                        var form = $('<form>', { method: 'POST', action: url, style: 'display:none;' });
                        form.append($('<input>', { type: 'hidden', name: '_token', value: csrf }));
                        $('body').append(form);
                        form.submit();
                    }
                },
                "{{ trans('common.btn_cancel') }}": function () {
                }
            }
        });
    }

</script>
<script src="{{ secure_asset('js/app.js') }}"></script>
</body>
</html>
