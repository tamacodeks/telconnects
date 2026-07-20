@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
        ['name' => trans('myservice.order_cc'),'url'=> '','active' => 'yes']
    ]
    ])
    <style>

        /*Nestable lists*/
        .dd {
            position: relative;
            display: block;
            margin: 0;
            padding: 0;
            max-width: 600px;
            list-style: none;
            font-size: 13px;
            line-height: 20px;
        }

        .dd-list {
            display: block;
            position: relative;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .dd-list .dd-list {
            padding-left: 30px;
        }

        .dd-collapsed .dd-list {
            display: none;
        }

        .dd-item,
        .dd-empty,
        .dd-placeholder {
            display: block;
            position: relative;
            margin: 0;
            padding: 0;
            min-height: 30px;
            font-size: 13px;
            line-height: 25px;
        }

        .dd-handle {
            cursor: default;
            display: block;
            margin: 5px 0;
            padding: 7px 10px;
            color: #333;
            text-decoration: none;
            border: 1px solid #ddd;
            background: #00427f;
        }

        .dd-handle:hover {
            color: #FFF;
            background: #4D90FD;
            border-color: #428BCA;
        }

        .dd-item > button {
            color: #555;
            font-family: FontAwesome;
            display: block;
            position: relative;
            cursor: pointer;
            float: left;
            width: 25px;
            height: 20px;
            margin: 8px 2px;
            padding: 0;
            text-indent: 100%;
            white-space: nowrap;
            overflow: hidden;
            border: 0;
            background: transparent;
            font-size: 10px;
            line-height: 1;
            text-align: center;
        }

        .dd-item > button:before {
            display: block;
            position: absolute;
            width: 100%;
            text-align: center;
            text-indent: 0;
        }

        .dd-item > button[data-action="collapse"]:before {
        }

        .dd-placeholder,
        .dd-empty {
            margin: 5px 0;
            padding: 0;
            min-height: 30px;
            background: #FFF;
            border: 1px dashed #b6bcbf;
            box-sizing: border-box;
            -moz-box-sizing: border-box;
        }

        .dd-empty {
            border: 1px dashed #bbb;
            min-height: 100px;
            background-color: #e5e5e5;
            background-image: -webkit-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff),
            -webkit-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff);
            background-image: -moz-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff),
            -moz-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff);
            background-image: linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff),
            linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff);
            background-size: 60px 60px;
            background-position: 0 0, 30px 30px;
        }

        .dd-dragel {
            position: absolute;
            pointer-events: none;
            z-index: 9999;
        }

        .dd-dragel > .dd-item .dd-handle {
            margin-top: 0;
        }

        .dd-dragel .dd-handle {
            -webkit-box-shadow: 2px 4px 6px 0 rgba(0, 0, 0, .1);
            box-shadow: 2px 4px 6px 0 rgba(0, 0, 0, .1);
        }

        .dd3-content {
            display: block;
            margin: 5px 0;
            padding: 2px 10px 2px 40px;
            color: #333;
            text-decoration: none;
            background: none repeat scroll 0 0 #FFFFFF;
            border: 1px solid #DDDDDD;
            color: #333333;
            background: #eeeeee;
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#fcfff4', endColorstr='#e9e9ce', GradientType=0); /* IE6-9 */
        }

        .dd3-content:hover {
            background: #fff;
        }

        .dd-dragel > .dd3-item > .dd3-content {
            margin: 0;
        }

        .dd3-item > button {
            margin-left: 35px;
        }

        .dd3-handle {
            position: absolute;
            margin: 0;
            left: 0;
            top: 0;
            cursor: all-scroll;
            width: 30px;
            text-indent: 100%;
            white-space: nowrap;
            overflow: hidden;
            border: 1px solid #3276B1;
            background: #00427f;
            height: 30px;
            box-shadow: 1px 1px 0 rgba(255, 255, 255, 0.2) inset;
        }

        .dd3-handle:before {
            content: '=';
            display: block;
            position: absolute;
            left: 0;
            top: 2px;
            width: 100%;
            text-align: center;
            text-indent: 0;
            color: #fff;
            font-size: 12px;
            font-weight: normal;
        }

        .dd3-handle:hover {
            background: #4E9DFF;
        }
    </style>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{{ trans('myservice.order_cc') }}</h3>
                    </div>
                    <div class="panel-body">
                        <form class="form-horizontal" action="#">
                            {{ csrf_field() }}
                            <div class="form-group">
                                <label for="telecom_provider_id" class="col-sm-4 control-label">{{ trans('service.telecom_provider') }}</label>
                                <div class="col-md-4">
                                    <select class="form-control" id="telecom_provider_id" name="telecom_provider_id">
                                        <option value="">{{ trans('common.lbl_please_choose') }}</option>
                                        @if(isset($telecom_providers))
                                            @foreach($telecom_providers as $telecom_provider)
                                                <option value="{{ $telecom_provider->id }}" @if($tp_config_id == $telecom_provider->id) selected @endif>{{ $telecom_provider->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </form>
                        @if(isset($tp_config_id) && $tp_config_id != '')
                            <div class="row">
                                <div class="col-md-3"></div>
                                <div class="col-md-6">
                                    <div id="list2" class="dd">
                                        <ol class="dd-list">
                                            @if(isset($cards))
                                                @foreach ($cards as $card)
                                                    <li data-id="{{ $card->id }}"
                                                        class="dd-item dd3-item">
                                                        <div class="dd-handle dd3-handle"></div>
                                                        <div class="dd3-content">{{ $card->name }} {{ \app\Library\AppHelper::formatAmount('EUR',$card->face_value) }} (<span data-trigger="hover" data-container="body" data-toggle="popover" data-placement="top" data-content="{{  $card->description }}" data-original-title="{{ $card->name }} {{ \app\Library\AppHelper::formatAmount('EUR',$card->face_value) }}" title="">{{  \app\Library\AppHelper::doTrim_text($card->description,50,true) }}</span>)                                                                                                     </div>
                                                    </li>
                                                @endforeach
                                            @endif
                                        </ol>
                                    </div>
                                    <form class="" method="POST"
                                          action="{{ secure_url('cc/align/cards/update') }}">
                                        <input type="hidden" name="reorder" id="reorder"
                                               value=""/>
                                        <input type="hidden" name="tp_config_id" value="{{ $tp_config_id }}">
                                        {{ csrf_field() }}
                                        <br><br>
                                        <button type="submit"
                                                class="btn btn-theme pull-right"><i class="fa fa-list-alt"></i>&nbsp;{{ trans('common.lbl_reorder_menu') }}
                                        </button>
                                    </form>
                                </div>
                                <div class="col-md-3"></div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="{{ secure_asset('vendor/common/jquery.nestable.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('.dd').nestable({
                maxDepth : 1
            });
            update_order('#list2', "#reorder");
            $("#telecom_provider_id").change(function () {
                if($(this).val() != ''){
                    window.location = "{{ secure_url('cc/align/cards') }}?telecom_provider="+$(this).val();
                }
            });
            $('#list2').on('change', function () {
                var out = $('#list2').nestable('serialize');
                $('#reorder').val(JSON.stringify(out));

            });
        });
        function update_order(selector, sel2) {

            var out = $(selector).nestable('serialize');
            $(sel2).val(JSON.stringify(out));

        }
    </script>
@endsection