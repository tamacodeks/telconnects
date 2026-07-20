@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
        ['name' => "Inbox",'url'=> '','active' => 'yes']
    ]
    ])
    <div class="container-fluid" id="app">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="col-md-3">
                            <ul class="list-group">
                                @foreach($users as $user)
                                    <a href="{{ secure_url('private.chat.index', $user->id) }}">
                                        <li class="list-group-item @if($active_user == $user->id) active @endif">{{ $user->username }}
                                        </li>
                                    </a>
                                @endforeach
                            </ul>
                        </div>
                        <div class="col-md-9">
                            <form id="group-chat" class="form-horizontal" role="form" method="POST"
                                  @submit.prevent="sendMessage">
                                {{ csrf_field() }}
                                <div id="messages" style="min-height: 400px">
                                    <div class="conversation">
                                        <div v-if="messages.length">
                                            <div v-for="message in messages" :key="`A-${message.id}`" class="conversation-container">
                                                <div class="message sent" v-if="message.sender.username === userName">
                                                    <b class="chat-sender">{{ trans('myservice.you') }}<span class="metadata text-right"
                                                                                                             style="bottom:0px"><span
                                                                    class="time">@{{ message.created_at }}</span></span></b>@{{ message.message }}
                                                    <span class="metadata"></span></div>
                                                <div v-else class="message received"><b
                                                            class="chat-sender">@{{ message.sender.username }}<span
                                                                class="metadata text-right" style="bottom: 0px"><span
                                                                    class="time">@{{ message.created_at }}</span></span></b>@{{ message.message }}
                                                </div>
                                            </div>
                                            {{--<message v-bind:value="template"--}}
                                            {{--v-on:input="template = message.sender.username === this.userName ? 'sent' : 'receive'"--}}

                                            {{--:sender="message.sender.username" :message="message.message"--}}
                                            {{--:createdat="message.created_at"></message>--}}
                                        </div>
                                        <div v-else style="margin-top: 200px;margin-bottom: 0">
                                            <div class="alert alert-warning text-center">{{ trans('common.no_chat_yet') }}</div>
                                        </div>
                                    </div>
                                </div>
                                <span class="typing"
                                      v-if="isTyping"><i><span>@{{ isTyping }}</span> {{ trans('common.is_typing') }}...</i></span>
                                <hr/>
                                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }} chat-box">
                                    <div class="col-md-10">
                                        <textarea v-model="message" type="textarea" class="form-control" name="message"
                                                  @keyup.enter="sendMessage" @keypress="userIsTyping({{$chatRoom->id}})"
                                                  required autofocus></textarea>

                                        @if ($errors->has('email'))
                                            <span class="help-block">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </span>
                                        @endif
                                    </div>
                                    <div class="col-md-2 chat-btn">
                                        <button type="submit" class="btn btn-primary" :disabled="!message">
                                            <i class="fa fa-paper-plane"></i>&nbsp;{{ trans('service.send') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <audio id="chatAudio"><source src="{{ secure_asset('others/notify.mp3') }}" type="audio/mpeg"></audio>
    <script>
        var fetchChatURL = "{{ secure_url('fetch-private.chat', $chatRoom->id) }}";
        var postChatURL = "{{ secure_url('private.chat.store', $chatRoom->id) }}";
    </script>
    <script src="{{ secure_asset('js/socket.io.js') }}"></script>
    <script src="{{ secure_asset('js/vue.js') }}"></script>
    <script src="{{ secure_asset('js/chat.js') }}"></script>
    <script>
        window.Echo.connector.options.auth.headers['Authorization'] = 'Bearer {{ auth()->user()->api_token }}';
        window.Echo.private('chat-room-{{$chatRoom->id}}')
            .listen('PrivateMessageEvent', (e) => {
            $('#chatAudio')[0].play();
        app.updateChat(e);
        }).listenForWhisper('typing', (e) => {
            app.isTyping = e.user;
        setTimeout(function () {
            app.isTyping = '';
        }, 1500);
        });
    </script>
@endsection