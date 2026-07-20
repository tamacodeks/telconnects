<div id="slidebar-white" class="slidebar-nav">
    <nav class="navbar navbar-default" role="navigation">
        <ul class="nav navbar-nav" style="margin-bottom: 30px;">
            <div class="profile-userpic-aside hide">
                {{--<img src="{{ asset($image) }}" class="img-responsive" alt="">--}}
            </div>
            <div class="text-center m-t-5">
                <div class="profile-user-aside-title-name">
                    {{ auth()->user()->username }}
                </div>
                <div class="profile-user-aside-title-job">
                    {{ session()->get('userGroup') }}
                </div>
            </div>
            <div class="profile-user-aside">
                @if(!in_array(auth()->user()->group_id,[1,2,6]))
                    <li class="nav-items">
                        <h5 class="text-muted">{{ trans('common.balance') }}</h5>
                        <h3 class="nav-link text-center" id="tamaBalance">{{ \app\Library\AppHelper::getBalance(auth()->user()->id,auth()->user()->currency,true) }}</h3>
                    </li>
                @endif
                @if(auth()->user()->group_id == 2)
                    <li class="nav-items" style="border-bottom: #ddd 1px solid;">
                        <h5 class="text-muted">TAMA {{ trans("common.balance") }}</h5>
                        <h3 class="nav-link text-center" id="tamaBalance" style="margin-top: -5px;">
                            {{ \app\Library\AppHelper::getAdminBalance() }}</h3>
                    </li>
                    <li class="nav-items">
                        <h5 class="text-muted">MyService {{ trans('common.balance') }}</h5>
                        <h3 class="nav-link text-center" id="myserviceBalance" style="margin-top: -5px;">
                            {{ \app\Library\AppHelper::getMyServiceBalance(auth()->user()->id,auth()->user()->currency,true) }}</h3>
                    </li>
                    {{--<li class="nav-items">--}}
                    {{--<h5 class="text-muted">Aleda {{ trans('common.balance') }}</h5>--}}
                    {{--<h3 class="nav-link text-center" id="aledaBalance" style="margin-top: -5px;">--}}
                    {{--{{ \app\Library\AppHelper::aledaBalance() }}</h3>--}}
                    {{--</li>--}}
                    <li class="nav-items">
                        <h5 class="text-muted">Bimedia {{ trans('common.balance') }}</h5>
                        <h3 class="nav-link text-center" id="bimediaBalance" style="margin-top: -5px;">
                        {{--{{ \app\Library\AppHelper::BimediaBalance() }}</h3> --}}
                    </li>
                @endif
                @if(in_array(auth()->user()->group_id,[6]))
                    <li class="nav-items hide">
                        <form class="navbar-form" role="search">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search" name="srch-term" id="srch-term">
                                <div class="input-group-btn">
                                    <button class="btn btn-default" type="submit"><i class="glyphicon glyphicon-search"></i></button>
                                </div>
                            </div>
                        </form>
                    </li>
                @endif
            </div>
            <?php $sidebar = \app\Library\AppHelper::menus('sidebar', 1, auth()->user()->group_id);?>
            @foreach ($sidebar as $menu)
                <li class="nav-item @if(count($menu['childs']) > 0 ) dropdown
                <?php
                $collection = collect($menu['childs']);
                $url = !empty(request()->segment(2)) ? request()->segment(1).'/'.request()->segment(2) : request()->segment(1) ?>
                @if($collection->contains('url', $url)) open @endif @endif @if(request()->segment(1) == $menu['url']) active @endif">
                    <a href="{{ secure_url($menu['url']) }}" @if(count($menu['childs'])>0) class="dropdown-toggle"
                       data-toggle="dropdown" @else class="li-a" @endif><i class="{{$menu['icon']}}"></i>
                        @if(ENABLE_MULTI_LANG ==1 && isset($menu['trans_lang']['title'][session('locale')]))
                            {{ $menu['trans_lang']['title'][session('locale')] }}
                        @else
                            {{$menu['name']}}
                        @endif
                        @if(count($menu['childs'])>0)
                            <b class="caret"></b>
                        @endif
                    </a>
                    @if(count($menu['childs'])>0)
                        <ul class="dropdown-menu" role="menu">
                            @foreach ($menu['childs'] as $menu2)
                                <?php
                                if(auth()->user()->group_id != 4 && $menu2['url'] == "cc-print-requests"){
                                    $note_count = \App\Models\PinPrintRequest::where('to_user',auth()->user()->id)->where('status',0)->count();
                                }
                                ?>
                                {{--@if(\app\Library\AppHelper::skip_service_as_menu($menu2['url']))--}}
                                <li class="nav-item @if($url == $menu2['url']) active @endif">
                                    <a href="{{ secure_url($menu2['url'])}}"  class="li-a">
                                        <i class="{{$menu2['icon']}}"></i>&nbsp;&nbsp;@if(ENABLE_MULTI_LANG ==1 && isset($menu2['trans_lang']['title'][session('locale')])){{ $menu2['trans_lang']['title'][session('locale')] }}@else{{$menu2['name']}}@endif
                                        @if($menu2['url'] == "cc-print-requests")
                                            <span data-count="{{ $note_count }}" class=" notification-icon"></span>
                                        @endif
                                        @if(count($menu2['childs'])>0)<b class="caret"></b>@endif</a>
                                    @if(count($menu2['childs'])>0)
                                        <b class="caret"></b>
                                    @endif
                                    @if(count($menu2['childs'])>0)
                                        <ul class="dropdown-menu">
                                            @foreach ($menu2['childs'] as $menu3)
                                                <li>
                                                    <a href="{{ secure_url($menu3['url'])}}"  class="li-a">
                                                        <i class="{{$menu3['icon']}}"></i>
                                                        @if(ENABLE_MULTI_LANG ==1 && isset($menu3['trans_lang']['title'][session('locale')]))
                                                            {{ $menu3['trans_lang']['title'][session('locale')] }}
                                                        @else
                                                            {{$menu3['name']}}
                                                        @endif
                                                        @if(count($menu3['childs'])>0)
                                                            <b class="caret"></b>
                                                        @endif
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
    </nav><!--/.navbar -->
</div>