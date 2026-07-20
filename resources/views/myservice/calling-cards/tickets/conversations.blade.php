@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
        ['name' => trans('myservice.pin_usage_history'),'url'=> url('cc-pin-history'),'active' => 'no'],
        ['name' => trans('myservice.my_tickets'),'url'=> url('tickets'),'active' => 'no'],
        ['name' => $page_title,'url'=> '','active' => 'yes'],
    ]
    ])
    <div class="container-fluid">
        <div class="row m-t-10">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        {{ $page_title }}
                        <div class="pull-right">
                            @if(auth()->user()->username != $from_user)
                                <a style="margin-top: -5px" href="{{ url('') }}"  onclick="event.preventDefault(); document.getElementById('close-ticket-{{ $ticket->ticket_id }}').submit();" class="btn btn-primary btn-sm"><i class="fa fa-check-circle"></i>&nbsp;{{ trans('myservice.close_ticket') }} </a>
                                <form id="close-ticket-{{ $ticket->ticket_id }}" action="{{ url('ticket/close') }}" method="POST" style="display: none;">{{ csrf_field() }}
                                    <input type="hidden" name="ticket_id" value="{{ $ticket->ticket_id }}">
                                </form>
                            @endif
                            @if($to_user != optional(\App\User::find(auth()->user()->parent_id))->username && auth()->user()->group_id == 3)
                                <a onClick="AppModal(this.href,'{{ $ticket->name }}');return false;"  style="margin-top: -5px" href="{{ url('ticket/forward/'.$ticket_id) }}" class="btn btn-primary btn-sm"><i class="fa fa-arrow-right"></i>&nbsp;{{ trans('myservice.forward_to') }} {{ optional(\App\User::find(auth()->user()->parent_id))->username }} </a>
                            @endif
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="account" class="col-sm-4 control-label">From</label>
                                    <div class="col-sm-8">
                                        <h5 class="ticket-h5">
                                            {{ optional(\App\User::find($ticket->from_user))->username }}
                                        </h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="account" class="col-sm-4 control-label">To</label>
                                    <div class="col-sm-8">
                                        <h5 class="ticket-h5">{{ optional(\App\User::find($ticket->to_user))->username }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="account" class="col-sm-5 control-label">Card Name</label>
                                    <div class="col-sm-7">
                                        <h5 class="ticket-h5">{{ $ticket->name }}</h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="account" class="col-sm-5 control-label">Face Value</label>
                                    <div class="col-sm-7">
                                        <h5 class="ticket-h5">{{ $ticket->face_value }}</h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="account" class="col-sm-5 control-label">Serial</label>
                                    <div class="col-sm-7">
                                        <h5 class="ticket-h5">{{ $ticket->serial }}</h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="account" class="col-sm-5 control-label">Pin</label>
                                    <div class="col-sm-7">
                                        <h5 class="ticket-h5">{{ $ticket->pin }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row m-t-10">
                            <div class="col-md-12">
                                <div id="messages">
                                    @if(collect($ticket_conversations)->count() > 0)
                                        @foreach($ticket_conversations as $conversation)
                                            <div @if($loop->last) id="toScroll" @endif class="conversation">
                                                <div class="conversation-container">
                                                    @if($conversation->username == auth()->user()->username)
                                                        <div class="message sent">
                                                            <b class="chat-sender">{{ trans('myservice.you') }}
                                                                <span class="metadata text-right" style="bottom: 0px"><span class="time">{{ $conversation->created_at  }}</span></span>
                                                            </b>
                                                            {{ $conversation->message }}
                                                            <span class="metadata">
                      <span class="tick"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="15" id="msg-dblcheck-ack" x="2063" y="2076"><path d="M15.01 3.316l-.478-.372a.365.365 0 0 0-.51.063L8.666 9.88a.32.32 0 0 1-.484.032l-.358-.325a.32.32 0 0 0-.484.032l-.378.48a.418.418 0 0 0 .036.54l1.32 1.267a.32.32 0 0 0 .484-.034l6.272-8.048a.366.366 0 0 0-.064-.512zm-4.1 0l-.478-.372a.365.365 0 0 0-.51.063L4.566 9.88a.32.32 0 0 1-.484.032L1.892 7.77a.366.366 0 0 0-.516.005l-.423.433a.364.364 0 0 0 .006.514l3.255 3.185a.32.32 0 0 0 .484-.033l6.272-8.048a.365.365 0 0 0-.063-.51z" fill="#4fc3f7"/></svg></span>
                  </span>
                                                        </div>
                                                    @else
                                                        <div class="message received">
                                                            <b class="chat-sender">{{ $conversation->username}}
                                                                <span class="metadata text-right" style="bottom: 0px"><span class="time">{{ $conversation->created_at  }}</span></span>
                                                            </b>
                                                            {{ $conversation->message }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div>
                                            <div class="alert alert-warning">No chat yet!</div>
                                        </div>
                                    @endif
                                </div>
                                <hr/>
                                <form class="" action="{{ url('tickets/comment') }}" id="frmComment" method="POST">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="ticket_id" value="{{ $ticket->ticket_id }}">
                                    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }} chat-box">
                                        <div class="col-md-10">
                                            <textarea v-model="message" class="form-control" name="message" autofocus></textarea>
                                        </div>
                                        <div class="col-md-2 chat-btn">
                                            <button id="btnSubmit" type="submit" class="btn btn-primary">
                                                <i class="fa fa-paper-plane"></i> &nbsp;{{ trans('service.send') }}
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
    </div>
    <script>
        $(document).ready(function () {
            $("#messages").animate({scrollTop: $("#toScroll").position().top}, 1000);
            $('#frmComment').validate({
                // rules & options,
                rules: {
                    message: "required"
                },
                messages: {
                    message: "{{ trans('myservice.err_message') }}"
                },
                errorElement: "div",
                errorPlacement: function (error, element) {
                    // Add the `help-block` class to the error element
                    error.addClass("help-block");

                    if (element.prop("type") === "checkbox") {
                        error.insertAfter(element.parents("checkbox"));
                    }
                    if (element.prop("type") === "radio") {
                        error.insertAfter(element.parents("radio"));
                    } else {
                        error.insertAfter(element);
                    }
                },
                highlight: function (element, errorClass, validClass) {
                    $(element).parents(".form-group").addClass("has-error").removeClass("has-success");
                },
                unhighlight: function (element, errorClass, validClass) {
                    $(element).parents(".form-group").addClass("").removeClass("has-error");
                },
                submitHandler: function (form) {
                    $("#frmComment").LoadingOverlay("show");
                    $("#btnSubmit").html("<i class='fa fa-spinner fa-pulse'></i>&nbsp;{{ trans('myservice.sending') }}").attr('disabled', 'disabled');
                    form.submit();
                }
            });
        });
    </script>
@endsection