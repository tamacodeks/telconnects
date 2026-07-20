@extends('layout.app')
@section('content')
    @include('layout.breadcrumb',['data' => [
         ['name' => trans('common.products'),'url'=> secure_url('products'),'active' => 'no'],
         ['name' => trans('common.btn_update'),'url'=> '','active' => 'yes']
    ]])
    <link href="{{ secure_asset('vendor/summernote/summernote.css') }}" rel="stylesheet">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <form id="frmProduct" class="form-horizontal" action="{{ secure_url('product/update') }}" method="POST" enctype="multipart/form-data">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            @if($row['id'] == '') {{ trans('common.btn_add').' '.trans('common.lbl_product') }} @else {{ trans('common.btn_update').' '.$row['name'] }} @endif
                            <div class="pull-right" style="    margin-top: -5px;">
                                <div class="btn-group">
                                    <a href="{{ secure_url('products') }}" type="button" class="btn btn-warning  btn-sm"><i class="fa fa-chevron-circle-left"></i>&nbsp;{{ trans('common.btn_cancel') }}</a>
                                    <button id="btnSubmit" class="btn btn-primary btn-sm" type="submit"><i class="fa fa-save"></i>&nbsp;{{ trans('common.btn_save') }}</button>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body">
                            {{ csrf_field() }}
                            <input type="hidden" value="{{ $row['id'] }}" name="id">
                            <ul class="nav nav-tabs">
                                <li class="active"><a data-toggle="tab"
                                                      href="#prod_info">{{ trans("common.lbl_product") }}</a></li>
                                @if(ENABLE_MULTI_LANG ==1)
                                    <?php $lang = \Kalamsoft\Langman\Lman::langOption();
                                    foreach($lang as $l) {
                                    if($l['folder'] != 'en') {
                                    ?>
                                    <li><a data-toggle="tab" role="tab"
                                           href="#lang_<?php echo $l['folder'];?>">In <?php echo $l['name'];?></a>
                                    </li>
                                    <?php
                                    }
                                    }
                                    ?>
                                @endif
                            </ul>

                            <div class="tab-content">
                                <div id="prod_info" class="tab-pane fade in active m-t-20">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name" class="col-sm-4 control-label">{{ trans('common.prod_name') }}</label>
                                            <div class="col-md-8">
                                                <input type="text" class="form-control" id="name" placeholder="" value="{{ $row['name'] }}" name="name">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="tags" class="col-sm-4 control-label">Tags</label>
                                            <div class="col-sm-8">
                                                <input type="text" name="tags" class="form-control" id="tags" placeholder="Tags" value="{{ $row['tags'] }}">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="description" class="col-sm-4 control-label">{{ trans('common.lbl_desc') }}</label>
                                            <div class="col-sm-8">
                                                <textarea class="form-control" name="description" id="description">{{ $row['description'] }}</textarea>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="country_id" class="col-sm-4 control-label">{{ trans('myservice.lbl_country') }}</label>
                                            <div class="col-md-8">
                                                <select name="country_id" class="form-control" id="country_id">
                                                    <option value="">{{ trans('common.lbl_please_choose') }}</option>
                                                    @foreach($countries as $country)
                                                        <option value="{{ $country->id }}" @if($row['country_id'] == $country->id) selected @endif>{{ $country->nice_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="category_id" class="col-sm-4 control-label">{{ trans('common.category') }}</label>
                                            <div class="col-md-8">
                                                <select name="category_id" class="form-control" id="category_id">
                                                    <option value="">{{ trans('common.lbl_please_choose') }}</option>
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->id }}" @if($row['category_id'] == $category->id) selected @endif>{{ $category->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="image" class="col-sm-4 control-label">{{ trans('myservice.image') }}</label>
                                            <div class="col-sm-8">                                                                                     <img src="{{ $row['image'] }}" class="img-responsive">
                                                <br>
                                                <input type="file" name="image" class="form-control" id="image">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="stock_status_id" class="col-sm-4 control-label">{{ trans('common.stock_status') }}</label>
                                            <div class="col-md-8">
                                                <select class="form-control" name="stock_status_id" id="stock_status_id">
                                                    <option value="">{{ trans('common.lbl_please_choose') }}</option>
                                                    @foreach($stock_status as $ss)
                                                        <option value="{{ $ss->id }}" @if($row['stock_status_id']) selected @endif>{{ $ss->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="qty" class="col-sm-4 control-label">{{ trans('common.lbl_qty') }}</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="qty" placeholder="" name="qty" value="{{ $row['qty'] }}">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="cost" class="col-sm-4 control-label">{{ trans('common.cost') }}</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="money-input form-control" id="cost" placeholder="" name="cost" value="{{ $row['cost'] }}">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="own_price" class="col-sm-4 control-label">{{ trans('common.own_price') }}</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="money-input form-control" id="own_price" placeholder="" name="own_price" value="{{ $row['own_price'] }}">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="reseller_price" class="col-sm-4 control-label">{{ trans('common.res_price') }}</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="money-input form-control" id="reseller_price" placeholder="" name="reseller_price" value="{{ $row['reseller_price'] }}">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="shipping_charge" class="col-sm-4 control-label">{{ trans('common.shipping_charge') }}</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="money-input form-control" id="shipping_charge" placeholder="" name="shipping_charge" value="{{ $row['shipping_charge'] }}">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="sur_charge" class="col-sm-4 control-label">{{ trans('common.sur_charge') }}</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="money-input form-control" id="sur_charge" placeholder="" name="sur_charge" value="{{ $row['sur_charge'] }}">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="sur_charge_desc" class="col-sm-4 control-label">{{ trans('common.sur_charge_desc') }}</label>
                                            <div class="col-sm-8">
                                                <textarea class="form-control" name="sur_charge_desc" id="sur_charge_desc">{{ $row['sur_charge_desc'] }}</textarea>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="status" class="col-sm-4 control-label">{{ trans('myservice.lbl_status') }}</label>
                                            <div class="col-sm-8">
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" name="status" value="1" @if($row['status'] == 1) checked @endif>&nbsp;{{ trans('common.lbl_enabled') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="status" class="col-sm-4 control-label">{{ trans('common.track_qty') }}</label>
                                            <div class="col-sm-8">
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" name="is_track_qty" value="1" @if($row['is_track_qty'] == 1) checked @endif>&nbsp;{{ trans('common.lbl_enabled') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="track_qty" class="col-sm-4 control-label">{{ trans('common.track_qty_no') }}</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="track_qty" placeholder="" name="track_qty" value="{{ $row['track_qty'] }}">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="min_to_order" class="col-sm-4 control-label">{{ trans('common.min_to_order') }}</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="min_to_order" placeholder="" name="min_to_order" value="{{ $row['min_to_order'] }}">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="max_to_order" class="col-sm-4 control-label">{{ trans('common.max_to_order') }}</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="max_to_order" placeholder="" name="max_to_order" value="{{ $row['max_to_order'] }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if(ENABLE_MULTI_LANG ==1)
                                    <?php $lang = \Kalamsoft\Langman\Lman::langOption();
                                    $translated = json_decode($row['trans_lang'], true);
                                    foreach($lang as $l) {
                                    if($l['folder'] != 'en') {
                                    ?>
                                    <div id="lang_<?php echo $l['folder'];?>" class="tab-pane m-t-20"
                                         role="tabpanel">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="product_name<?php echo $l['name'];?>"
                                                       class="col-md-4 control-label">{{ trans('common.prod_name') }}</label>
                                                <div class="col-md-8">
                                                    <input name="product_name_trans[<?php echo $l['folder'];?>]"
                                                           type="text"
                                                           class="form-control"
                                                           value="<?php echo(isset($translated['product_name'][$l['folder']]) ? $translated['product_name'][$l['folder']] : '');?>"/>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label for="product_desc<?php echo $l['name'];?>"
                                                       class="col-md-4 control-label ">{{ trans('common.lbl_desc') }}</label>
                                                <div class="col-md-8">
                                <textarea class="form-control trans_lang"
                                          name="product_desc_trans[<?php echo $l['folder'];?>]"><?php echo(isset($translated['product_desc'][$l['folder']]) ? $translated['product_desc'][$l['folder']] : '<br>');?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6"></div>
                                    </div>
                                    <?php
                                    }

                                    }
                                    ?>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="{{ secure_asset('vendor/summernote/summernote.min.js') }}" type="text/javascript"></script>
    <script>
        $(document).ready(function () {
            $('#description').summernote();
            $('.trans_lang').summernote();
        });
    </script>
@endsection