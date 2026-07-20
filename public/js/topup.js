/**
 * Detect operator animation toggle
 * @param className
 */
function toggleAnime(className) {
    if(className == "hide")
    {
        $("#detectAnime").addClass('hide');
    }else{
        $("#detectAnime").removeClass('hide');
    }
}

/**
 * Toggle DIV grid or list
 * @param className
 */
function toggleGridProviders(className) {
    $("#summaryDiv").addClass('hide');
    $("#gridProviderDiv").addClass('hide');
    if(className != "hide")
    {
        $("#gridProviderDiv").removeClass('hide');
    }
    $("#gridProviderList")
        .find('li')
        .remove();
    $(".div-grid-products").addClass('hide');
    $(".div-list-products").addClass('hide');
    $("#divRangeAmount").addClass('hide');
}

/**
 * Toggle Products Grid or List
 * @param divType
 * @param className
 */
function toggleProductsDiv(divType,className) {
    $(".div-grid-products").addClass('hide');
    $(".div-list-products").addClass('hide');
    $("#divRangeAmount").addClass('hide');
    if(divType == "list")
    {
        if(className != "hide")
        {
            $('#productLists').find('li').remove();
            $(".div-list-products").removeClass('hide');
        }
    }
    if(divType == "grid")
    {
        if(className != "hide")
        {
            $('#gridproductLists').find('li').remove();
            $(".div-grid-products").removeClass('hide');
        }
    }
}

/**
 * Toggle Enter Range Amount DIV
 * @param className
 */
function toggleRangeAmountDiv(className) {
    $(".div-grid-products").addClass('hide');
    $(".div-list-products").addClass('hide');
    $("#divRangeAmount").addClass('hide');
    if(className != "hide")
    {
        $("#divRangeAmount").removeClass('hide');
    }
}

/**
 * Filter ul list product by name or description
 */
function filterPriceList() {
    var input, filter, ul, li, a, i,b;
    input = document.getElementById("productSearch");
    filter = input.value.toUpperCase();
    ul = document.getElementById("productLists");
    li = ul.getElementsByTagName("li");
    for (i = 0; i < li.length; i++) {
        a = li[i].getElementsByClassName("strong-mobile")[0];
        if (a.innerHTML.toUpperCase().indexOf(filter) > -1) {
            li[i].style.display = "";
        } else {
            b = li[i].getElementsByTagName("h3")[0];
            if (b.innerHTML.toUpperCase().indexOf(filter) > -1) {
                li[i].style.display = "";
            } else {
                li[i].style.display = "none";
            }
        }
    }
}

/**
 * build providers as grid
 * @param providers
 */
function buildGridProviders(providers) {
    var mobileNumber, countryCode,providerLength;
    mobileNumber = $("#mobile").val();
    countryCode = $("#countryCode").val();
    $.each(providers, function (key, value) {
        var flag = value.provider_code.replace(value.country_iso, "");
        $('#gridProviderList')
            .append('\n' +
                '                                                <li class="provider col-md-6">\n' +
                '                                                    <a href="javascript:void(0);" onclick="fetchProducts(\''+value.country_iso+'\',\''+countryCode+'\',\''+value.country_iso+'\',\''+value.provider_code+'\',\''+value.name+'\',\''+value.country+'\')">\n' +
                '                                                        <div class="panel panel-default provider-'+value.provider_code.toLowerCase()+'">\n' +
                '                                                            <div class="logo">\n' +
                '<img src="https://imagerepo.ding.com/logo/' + flag + '.svg">\n' +
                '                                                            </div>\n' +
                '                                                            <div class="title">' + value.name + '</div>\n' +
                '                                                        </div>\n' +
                '                                                    </a>\n' +
                '                                                </li>');
    });
    if(providers.length === 1)
    {
        $("#gridProviderList .provider a div.panel").css("border", "1px solid");
    }
}

/**
 * Get Providers for entered mobile number
 * @param ajaxUrl
 */
function getProviders(ajaxUrl) {
    $.ajax({
        url: ajaxUrl,
        method: 'GET',
        success: function (response) {
            // console.log(response);
            toggleAnime('hide');
            toggleGridProviders('show');
            buildGridProviders(response.data);
            if(response.data.length === 1)
            {
                //call fetchProducts
                // console.log(response.data[0].name)
                // console.log(response.data[0].country)
                $("#gridProviderList li:first-child a").click();
            }
        },
        error: function (data) {
            console.log('fetching operator returns error ', data);
            var obj = JSON.parse(data.responseText);
            $.alert({
                title: 'Information',
                content: obj.error.message,
            });
        }
    });
}

/**
 * Get Providers for entered mobile number
 * @param ajaxUrl
 */
function getProvidersprepay(ajaxUrl) {
    var mobileNumber, countryCode, countryIso;
    mobileNumber = $("#mobile").val();
    countryCode = $("#countryCode").val();
    countryIso = $("#countryIso").val();
    $.ajax({
        url: ajaxUrl,
        method: 'GET',
        success: function (response) {
            toggleAnime('hide');
            toggleGridProviders('show');
            var values = response.data[0].provider_code;
            console.log(values);
            if(values){
                buildGridPrepayprovider(response.data);
            }
            else {
                // toggleAnime('show');
                // var str = ajaxUrl;
                // var res = str.replace("fetchprepay", "fetch");
                // getProviders(res);
                // toggleAnime('hide');
                window.location.href = api_base_url + "/tama-topup/plan_s?accountNumber=" + mobileNumber + "&countryCode=" + countryCode + "&countryIsos=" + countryIso;
            }

            if(response.data.length === 1)
            {
                //call fetchProducts
                //console.log(response.data[0].name)
                //console.log(response.data[0].country)
                $("#gridProviderList li:first-child a").click();
            }
        },
        error: function (data) {
            console.log('fetching operator returns error ', data);
            var obj = JSON.parse(data.responseText);
            $.alert({
                title: 'Information',
                content: obj.error.message,
            });
        }
    });
}

function buildGridPrepayprovider(response) {

    var mobileNumber, countryCode,providerLength;
    mobileNumber = $("#mobile").val();
    countryCode = $("#countryCode").val();
    // console.log(countryCode);
    $.each(response, function (key, value) {

        $('#gridProviderList')
            .append('\n' +
                '                                                <li class="provider col-md-6">\n' +
                '                                                    <a href="javascript:void(0);" onclick="fetchProducts1(\''+value.country_iso+'\',\''+countryCode+'\',\''+value.country_iso+'\',\''+value.provider_code+'\',\''+value.name+'\',\''+value.country+'\')">\n' +
                '                                                        <div class="panel panel-default provider' +
                '-'+value.provider_code+'">\n' +
                '                                                            <div class="logo">\n' +
                '<img src="https://www.tamademat.com/images/' + value.name + '.svg">\n' +
                '                                                            </div>\n' +
                '                                                            <div class="title">' + value.name + '</div>\n' +
                '                                                        </div>\n' +
                '                                                    </a>\n' +
                '                                                </li>');
    });
    if(response.length === 1)
    {
        $("#gridProviderList .provider a div.panel").css("border", "1px solid");
    }
}

function fetchProducts1(countryIso,countryCode, region, providerCode,providerName,providerCountry) {
    $("#loadingProducts").toggle();
    $("#gridProviderList .provider a div.panel").css("border", "none");
    toggleProductsDiv("",'');
    $(".provider-"+providerCode.toLowerCase()).css("border", "1px solid");
    $("#summaryDiv").addClass('hide');
    var mobileNumber;
    mobileNumber = $("#mobile").val();
    $.ajax({
        url: api_base_url+"/tama-topup/fetchprepay/products?countryIso="+countryIso+"&countryCode="+countryCode+"&region="+region+"&providerCode="+providerCode+"&accountNumber="+mobileNumber,

        method: 'GET',
        success: function (response) {
            $("#loadingProducts").toggle();
            // console.log(response);
            //update provider name and country
            $("#_hid_provider_name").val(providerName);
            $("#_hid_provider_country").val(providerCountry);
            if(response.data.denomination_style == "list" && response.data.is_denominated == true)
            {
                //list style
                toggleProductsDiv("grid",'hide');
                toggleProductsDiv("list",'show');
                console.log('list');
                buildGridPrepayProducts(response.data.products);
            }else if(response.data.denomination_style == "grid" && response.data.is_denominated == true)
            {
                //grid style
                toggleProductsDiv("list",'hide');
                toggleProductsDiv("grid",'show');
                console.log('grid');
                var products = response.data.products;
                var data = products.sort(function(a, b){return a.maxSendValue - b.maxSendValue});
                buildGridPrepayProducts(data);
            }else{
                console.log(response.data.products);
                //range input show+
                toggleProductsDiv("list",'hide');
                toggleProductsDiv("grid",'hide');
                toggleRangeAmountDiv('show');
                var amount_between,minVal,maxVal;
                amount_between = between_trans+" "+response.data.products[0].minSendValue+" - "+response.data.products[0].maxSendValue;
                minVal = response.data.products[0].minSendValue;
                maxVal = response.data.products[0].maxSendValue;
                $("#SkuCode").val(response.data.products[0].sku_code);
                $("#exchange_rate").val(response.data.products[0].exchange_rate);
                $("#CurrencyIso").val(response.data.products[0].sendCurrencyIso);
                $("#percentage").val(response.data.products[0].percentage);
                $("#countryid").val(response.data.products[0].country);
                $("#range_amount").attr("placeholder", amount_between)
                    .attr("title", amount_between)
                    .attr("min", minVal)
                    .attr("max", maxVal).val('');
                $("#betweenDenomination").html(amount_between);
                $("#amountReceived").html('');
                $("#amountReceivedDiv").hide();
                /*get your element and containing validated form*/
                var $that = $('#range_amount');
                var $form = $that.closest('form');
                /*get validator object for the form*/
                var validator = $form.validate();
                /*use internal code of a plugin to clear the field*/
                if (validator.settings.unhighlight) {
                    validator.settings.unhighlight.call( validator, $that[0], validator.settings.errorClass, validator.settings.validClass );
                }
                validator.hideThese( validator.errorsFor( $that[0] ) );
            }
            $("#summaryDiv").removeClass('hide');
        },
        error: function (data) {
            $("#loadingProducts").toggle();
            // console.log('fetching operator returns error ', data);
            var obj = JSON.parse(data.responseText);
            $.alert({
                title: 'Information',
                content: obj.error.message,
            });
        }
    });
}

function buildGridPrepayProducts(products) {
    $.each(products, function (key, value) {
        $("#gridproductLists").append(' <li class="denominated-product product-type-topup">\n' +
            '                                                <a href="javascript:void(0);" onclick="gridPrepayClickLists(this,\''+value.sku_code+'\',\''+value.maxSendValue+'\',\''+value.sendValueOriginal+'\',\''+value.display_text+'\',\''+value.country+'\',\''+value.country+'\')">\n' +
            '                                                    <div class="panel panel-default panel-data activatable-item">\n' +
            '                                                        <div class="data">\n' +
            '                                                            <div class="price">\n' +
            '                                                                <h3>'+value.maxSendValue+'</h3>\n' +
            '                                                            </div>\n' +
            '                                                            <div class="receive-amount">\n' +
            '                                                                <span>'+value.sendCurrencyIso+'</span>\n' +
            '                                                                <span class="strong-mobile">'+value.display_text+'</span>\n' +
            '                                                                <span class="light-mobile">'+ will_be_received +'</span>\n' +
            '                                                            </div>\n' +
            '                                                            <div class="clearfix"></div>\n' +
            '                                                        </div>\n' +
            '                                                        <div class="active-icon">\n' +
            '                                                            <i class="fa fa-check"></i>\n' +
            '                                                        </div>\n' +
            '                                                    </div>\n' +
            '                                                </a>\n' +
            '                                            </li>');
    });
}
function gridPrepayClickLists(el, skuCode,sentAmount,SendValueOriginal,LocalCurrency,Country) {
    $('#gridproductLists li.active').removeClass('active');
    $('#gridproductLists li').find('.more-info').hide();
    $(el).parents('li').addClass('active');
    $("#SkuId").val(skuCode);
    $("#FaceValue").val(sentAmount);
    $("#OriginalValue").val(SendValueOriginal);
    $("#LocalCurrency").val(LocalCurrency);
    $("#Countryi").val(Country);
}
function buildGridPrepayproduct(response) {
    $.each(response, function (key, value) {
        $("#productLists").append('<li class="denomination">\n' +
            '                                                <a href="javascript:void(0);" class="li-a">\n' +
            '                                                    <div class="panel panel-default panel-data activatable-item">\n' +
            '                                                        <div class="data">\n' +
            '                                                            <div class="price">\n' +
            '                                                                <!-- ko ifnot: isDomesticProduct -->\n' +
            '                                                                <h3 >' + value.faceValue + '</h3>\n' +
            '                                                                <!-- /ko -->\n' +
            '                                                            </div>\n' +
            '                                                            <div class="receive-amount">\n' +
            '                                                                <span class="strong-mobile">' + value.faceValue + '</span>\n' +
            '                                                                <span class="light-mobile">'+ value.faceValue +'</span>\n' +
            '                                                            </div>\n' +
            '                                                            <div class="validity">\n' +
            '                                                                <div>' + value.faceValue + '</div>\n' +
            '                                                                <div></div>\n' +
            '                                                            </div>\n' +
            '                                                            <!-- ko if: hasDescription -->\n' +
            '                                                            <div class="more-info-toggle open">\n' +
            '                                                                <i class="fa fa-chevron-right"></i>\n' +
            '                                                            </div>\n' +
            '                                                            <!-- /ko -->\n' +
            '                                                            <div class="more-info" style="display: none;">\n' +
            '                                                                <p>\n' +
            '                                                                    <span >' + value.faceValue + '</span>\n' +
            '                                                                </p>\n' +
            '                                                                <p></p>\n' +
            '                                                            </div>\n' +
            '                                                            <div class="clearfix"></div>\n' +
            '                                                            <!-- /ko -->\n' +
            '                                                        </div>\n' +
            '                                                        <div class="active-icon">\n' +
            '                                                            <i class="fa fa-check"></i>\n' +
            '                                                        </div>\n' +
            '                                                    </div>\n' +
            '                                                </a>\n' +
            '                                            </li>');
    });
}


/**
 * Mark list as active and update skucode
 * @param el
 * @param skuCode
 */
function clickLists(el, skuCode,euro,euro_formatted,dest,dest_formatted,commission,sentAmount,SendValueOriginal,sentCurrencyIso,UatNumber) {
    // console.log('sendOrginalValue',SendValueOriginal);
    $('.product-lists li.active').removeClass('active');
    $('.product-lists li').find('.more-info').hide();
    $(el).parents('li').addClass('active');
    $("#SkuCode").val(skuCode);
    $("#_hid_euro_amount").val(euro);
    $("#_hid_euro_amount_formatted").val(euro_formatted);
    $("#_hid_dest_amount").val(dest);
    $("#_hid_dest_amount_formatted").val(dest_formatted);
    $("#_hid_commission_rate").val(commission);
    $(el).find('.more-info').toggle();
    $("#SendValue").val(sentAmount);
    $("#SendValueOriginal").val(SendValueOriginal);
    $("#SendCurrencyIso").val(sentCurrencyIso);
    $("#UatNumber").val(UatNumber);
}

/**
 * Mark grid as active and update skucode
 * @param el
 * @param skuCode
 */
function gridClickLists(el, skuCode,euro,euro_formatted,dest,dest_formatted,commission,sentAmount,SendValueOriginal,sentCurrencyIso,UatNumber) {
    $('#gridproductLists li.active').removeClass('active');
    $('#gridproductLists li').find('.more-info').hide();
    $(el).parents('li').addClass('active');
    $("#SkuCode").val(skuCode);
    $("#_hid_euro_amount").val(euro);
    $("#_hid_euro_amount_formatted").val(euro_formatted);
    $("#_hid_dest_amount").val(dest);
    $("#_hid_dest_amount_formatted").val(dest_formatted);
    $("#_hid_commission_rate").val(commission);

    $("#SendValue").val(sentAmount);
    $("#SendValueOriginal").val(SendValueOriginal);
    $("#SendCurrencyIso").val(sentCurrencyIso);
    $("#UatNumber").val(UatNumber);
}

/**
 * Generate li and append with ul list
 * @param products
 */
function buildListProducts(products) {
    $.each(products, function (key, value) {
        $("#productLists").append('<li class="denomination">\n' +
            '                                                <a href="javascript:void(0);" class="li-a" onclick="clickLists(this,\'' + value.sku_code + '\',\'' + value.maxSendValue + '\',\'' + value.maxSendAmountFormatted + '\',\'' + value.maxReceiveValue + '\',\'' + value.maxReceiveAmountFormatted + '\',\'' + value.commission_rate + '\',\'' + value.maxSendValue + '\',\'' + value.sendValueOriginal + '\',\'' + value.sendCurrencyIso + '\',\'' + value.uat_number + '\')">\n' +
            '                                                    <div class="panel panel-default panel-data activatable-item">\n' +
            '                                                        <div class="data">\n' +
            '                                                            <div class="price">\n' +
            '                                                                <!-- ko ifnot: isDomesticProduct -->\n' +
            '                                                                <h3 >' + value.maxSendAmountFormatted + '</h3>\n' +
            '                                                                <!-- /ko -->\n' +
            '                                                            </div>\n' +
            '                                                            <div class="receive-amount">\n' +
            '                                                                <span class="strong-mobile">' + value.display_text + '</span>\n' +
            '                                                                <span class="light-mobile">'+ will_be_received +'</span>\n' +
            '                                                            </div>\n' +
            '                                                            <div class="validity">\n' +
            '                                                                <div>' + value.validity + '</div>\n' +
            '                                                                <div></div>\n' +
            '                                                            </div>\n' +
            '                                                            <!-- ko if: hasDescription -->\n' +
            '                                                            <div class="more-info-toggle open">\n' +
            '                                                                <i class="fa fa-chevron-right"></i>\n' +
            '                                                            </div>\n' +
            '                                                            <!-- /ko -->\n' +
            '                                                            <div class="more-info" style="display: none;">\n' +
            '                                                                <p>\n' +
            '                                                                    <span >' + value.description + '</span>\n' +
            '                                                                </p>\n' +
            '                                                                <p></p>\n' +
            '                                                            </div>\n' +
            '                                                            <div class="clearfix"></div>\n' +
            '                                                            <!-- /ko -->\n' +
            '                                                        </div>\n' +
            '                                                        <div class="active-icon">\n' +
            '                                                            <i class="fa fa-check"></i>\n' +
            '                                                        </div>\n' +
            '                                                    </div>\n' +
            '                                                </a>\n' +
            '                                            </li>');
    });
}

/**
 * Generate li as grid style and appent with ul
 * @param products
 */
function buildGridProducts(products) {
    $.each(products, function (key, value) {
        $("#gridproductLists").append(' <li class="denominated-product product-type-topup">\n' +
            '                                                <a href="javascript:void(0);" onclick="gridClickLists(this,\''+value.sku_code+'\',\''+value.maxSendValue+'\',\''+value.maxSendAmountFormatted+'\',\''+value.maxReceiveValue+'\',\''+value.maxReceiveAmountFormatted+'\',\''+value.commission_rate+'\',\''+value.minSendValue+'\',\''+value.sendValueOriginal+'\',\''+value.sendCurrencyIso+'\',\''+value.uat_number+'\')">\n' +
            '                                                    <div class="panel panel-default panel-data activatable-item">\n' +
            '                                                        <div class="data">\n' +
            '                                                            <div class="price">\n' +
            '                                                                <h3>'+value.maxSendAmountFormatted+'</h3>\n' +
            '                                                            </div>\n' +
            '                                                            <div class="receive-amount">\n' +
            '                                                                <span class="strong-mobile">'+value.display_text+'</span>\n' +
            '                                                                <span class="light-mobile">'+ will_be_received +'</span>\n' +
            '                                                            </div>\n' +
            '                                                            <div class="clearfix"></div>\n' +
            '                                                        </div>\n' +
            '                                                        <div class="active-icon">\n' +
            '                                                            <i class="fa fa-check"></i>\n' +
            '                                                        </div>\n' +
            '                                                    </div>\n' +
            '                                                </a>\n' +
            '                                            </li>');
    });
}

/**
 * Fetch Products
 * @param countryIso
 * @param countryCode
 * @param region
 * @param providerCode
 */
function fetchProducts(countryIso,countryCode, region, providerCode,providerName,providerCountry) {
    $("#loadingProducts").toggle();
    $("#gridProviderList .provider a div.panel").css("border", "none");
    toggleProductsDiv("",'');
    $(".provider-"+providerCode.toLowerCase()).css("border", "1px solid");
    $("#summaryDiv").addClass('hide');
    var mobileNumber;
    mobileNumber = $("#mobile").val();
    $.ajax({
        url: api_base_url+"/tama-topup/fetch/products?countryIso="+countryIso+"&countryCode="+countryCode+"&region="+region+"&providerCode="+providerCode+"&accountNumber="+mobileNumber,
        method: 'GET',
        success: function (response) {
            $("#loadingProducts").toggle();
            // console.log('product fetched ',response);
            //update provider name and country
            $("#_hid_provider_name").val(providerName);
            $("#_hid_provider_country").val(providerCountry);
            if(response.data.denomination_style == "list" && response.data.is_denominated == true)
            {
                //list style
                toggleProductsDiv("grid",'hide');
                toggleProductsDiv("list",'show');
                buildListProducts(response.data.products);
            }else if(response.data.denomination_style == "grid" && response.data.is_denominated == true)
            {
                //grid style
                toggleProductsDiv("list",'hide');
                toggleProductsDiv("grid",'show');
                buildGridProducts(response.data.products);
            }else{
                //range input show
                toggleProductsDiv("list",'hide');
                toggleProductsDiv("grid",'hide');
                toggleRangeAmountDiv('show');
                var amount_between,minVal,maxVal;
                amount_between = between_trans+" "+response.data.products[0].minSendAmountFormatted+" - "+response.data.products[0].maxSendAmountFormatted;
                minVal = response.data.products[0].minSendValue;
                maxVal = response.data.products[0].maxSendValue;
                $("#SkuCode").val(response.data.products[0].sku_code);
                $("#SendCurrencyIso").val(response.data.products[0].sendCurrencyIso);
                $("#_hid_commission_rate").val(response.data.products[0].commission_rate);
                $("#range_amount").attr("placeholder", amount_between)
                    .attr("title", amount_between)
                    .attr("min", minVal)
                    .attr("max", maxVal).val('');
                $("#betweenDenomination").html(amount_between);
                $("#amountReceived").html('');
                $("#amountReceivedDiv").hide();
                $("#UatNumber").val(response.data.products[0].uat_number);
                $("#providerCode").val(response.data.products[0].provider_code);
                /*get your element and containing validated form*/
                var $that = $('#range_amount');
                var $form = $that.closest('form');
                /*get validator object for the form*/
                var validator = $form.validate();
                /*use internal code of a plugin to clear the field*/
                if (validator.settings.unhighlight) {
                    validator.settings.unhighlight.call( validator, $that[0], validator.settings.errorClass, validator.settings.validClass );
                }
                validator.hideThese( validator.errorsFor( $that[0] ) );
            }
            $("#summaryDiv").removeClass('hide');
        },
        error: function (data) {
            $("#loadingProducts").toggle();
            console.log('fetching operator returns error ', data);
            var obj = JSON.parse(data.responseText);
            $.alert({
                title: 'Information',
                content: obj.error.message,
            });
        }
    });
}

/**
 * Fetch Estimated price
 * @param sendAmount
 */
function getEstimatePrice(sendAmount,providerCode) {
    $("#amountReceivedDiv").hide();
    $.ajax({
        url: api_base_url+"/tama-topup/fetch/estimate?sendAmount="+sendAmount+"&skuCode="+$("#SkuCode").val()+"&countryCode="+$("#countryCode").val()+"&providerCode="+providerCode,
        method: 'GET',
        success: function (response) {
            console.log('estimated price fetched ',response);
            /*get your element and containing validated form*/
            $("#amountReceivedDiv").show();
            //  console.log(response.data.withTax);
            // if(response.data.withTax === true)
            // {
            $("#amountReceived").html(response.data.formattedAmount);
            $("#ifTaxApplicable").show();
            $("#taxName").html(" "+response.data.taxName+"( "+response.data.receiveValueformattedAmount+" net)");
            // }else{
            //     $("#ifTaxApplicable").hide();
            //     $("#taxName").html('');
            //     $("#amountReceived").html(response.data.formattedAmount);
            // }
            setTimeout(function () {
                $("#range_amount").removeClass('loading');
                $("#reviewOrderBtn").removeAttr('disabled');
            },1000);
            $("#SendValue").val(response.data.sentAmount);
            $("#SendValueOriginal").val(response.data.sendValueOriginal);
            $("#range_amount").val(response.data.sentAmount);
            $("#SendCurrencyIso").val(response.data.SendCurrencyIso);
            $("#_hid_euro_amount").val(response.data.sentAmount);
            $("#_hid_euro_amount_formatted").val(response.data.sentAmountFormatted);
            $("#_hid_dest_amount").val(response.data.amount);
            $("#_hid_dest_amount_formatted").val(response.data.formattedAmount);
        },
        error: function (data) {
            $("#loadingProducts").toggle();
            // console.log('fetching operator returns error ', data);
            var obj = JSON.parse(data.responseText);
            $.alert({
                title: 'Information',
                content: obj.error.message,
            });
        }
    });
}
//calling card set api

function fetchCallingProducts(countryIso,countryCode, region, providerCode,providerName,providerCountry) {
    $("#gridproductLists").removeClass('hide');
    $("#gridproductLists1").removeClass('hide');
    $("#show_form").hide();
    $("#show_providers").addClass('hide');
    $("#loadingProducts").toggle();
    $("#gridProviderList .provider a div.panel").css("border", "none");
    toggleProductsDiv("",'');
    $(".provider-"+providerCode.toLowerCase()).css("border", "1px solid");
    $("#summaryDiv").addClass('hide');
    $.ajax({
        url: api_base_url+"/tama-topup-france/fetch/products?countryIso="+countryIso+"&countryCode="+countryCode+"&region="+region+"&providerCode="+providerCode,
        method: 'GET',
        success: function (response) {
            scrollingElement = (document.scrollingElement || document.body)
            $(scrollingElement).animate({
                scrollTop: document.body.scrollHeight
            }, 500);
            $("#show_providers").removeClass('hide');
            $("#loadingProducts").toggle();
            // console.log(response.products);
            // update provider name and country
            $("#_hid_provider_name").val(providerName);
            $("#_hid_provider_country").val(providerCountry);
            toggleProductsDiv("list",'hide');
            toggleProductsDiv("grid",'show');
            buildGridCalling(response.products);

            $("#summaryDiv").removeClass('hide');
        },
        error: function (data) {
            $("#loadingProducts").toggle();
            console.log('fetching operator returns error ', data);
            var obj = JSON.parse(data.responseText);
            $.alert({
                title: 'Information',
                content: obj.error.message,
            });
        }
    });
}

function buildGridCalling(products) {
    $.each(products, function (key, value) {
        var encodedString =   value.provider_code;
        var send_value = value.maxSendValue;
        if(value.region_code == 'GB'){
            // var send_value = value.sendValueOriginal;
            var send_value = value.maxSendValue;
        }
        $("#gridproductLists").append(' <li class="denominated-product product_height product-type-topup">\n' +
            '                                                <a href="javascript:void(0);"  onclick="gridCallingLists(this,\''+value.sku_code+'\',\''+value.maxSendValue+'\',\''+value.maxSendAmountFormatted+'\',\''+value.maxReceiveValue+'\',\''+ value.maxReceiveAmountFormatted +'\',\''+value.commission_rate+'\',\''+value.maxSendValue+'\',\''+value.sendValueOriginal+'\',\''+value.sendCurrencyIso+'\',\''+value.uat_number+'\',\''+encodedString+'\')">\n' +
            '                                                    <div class="panel panel-default panel-data activatable-item">\n' +
            '                                                        <div class="data">\n' +
            '                                                            <div class="price">\n' +
            '                                                                <h3>'+send_value+'</h3>\n' +
            '                                                            </div>\n' +
            '                                                            <div class="receive-amount">\n' +
            '                                                                <span class="strong-mobile">'+value.display_text+'</span>\n' +
            '                                   </div>\n' +
            '                                                            <div class="clearfix"></div>\n' +
            '                                                        </div>\n' +
            '                                                        <div class="active-icon">\n' +
            '                                                            <i class="fa fa-check"></i>\n' +
            '                                                        </div>\n' +
            '                                                    </div>\n' +
            '                                                </a>\n' +
            '                                            </li>');
    });
}


function gridCallingLists(el, skuCode,euro,euro_formatted,dest,dest_formatted,commission,sentAmount,SendValueOriginal,sentCurrencyIso,UatNumber,provider_code) {
    scrollingElement = (document.scrollingElement || document.body)
    $(scrollingElement).animate({
        scrollTop: document.body.scrollHeight
    }, 500);
    $('#gridproductLists li.active').removeClass('active');
    $('#gridproductLists li').find('.more-info').hide();
    $(el).parents('li').addClass('active');
    $("#SkuCode").val(skuCode);
    $("#_hid_euro_amount").val(euro);
    $("#_hid_euro_amount_formatted").val(euro_formatted);
    $("#_hid_dest_amount").val(dest);
    $("#_hid_dest_amount_formatted").val(dest_formatted);
    $("#_hid_commission_rate").val(commission);
    $("#SendValue").val(sentAmount);
    $("#SendValueOriginal").val(SendValueOriginal);
    $("#SendCurrencyIso").val(sentCurrencyIso);
    $("#UatNumber").val(UatNumber);
    $("#ProviderCode").val(provider_code);
}


/**
 * Get Providers for entered mobile number
 * @param ajaxUrl
 */
function getProvidersreloadly(ajaxUrl) {
    var mobileNumber, countryCode, countryIso;
    mobileNumber = $("#mobile").val();
    countryCode = $("#countryCode").val();
    countryIso = $("#countryIso").val();
    $.ajax({
        url: ajaxUrl,
        method: 'GET',
        success: function (response) {
            console.log(response);
            toggleAnime('hide');
            toggleGridProviders('show');
            if(response.status == 500){
                toggleProductsDiv("grid",'hide');
                toggleProductsDiv("list",'show');
                buildNoReloadlyRes();
            }else{
                if(response.success == 'failed'){
                    toggleProductsDiv("grid",'hide');
                    toggleProductsDiv("list",'show');
                    buildNoResponse();
                }else{
                    var values = response.data[0].provider_code;
                    if(values){
                        buildGridReloadlyprovider(response.data);
                    }
                    else {
                        window.location.href = api_base_url + "/tama-topup/plan_s?accountNumber=" + mobileNumber + "&countryCode=" + countryCode + "&countryIsos=" + countryIso;
                    }

                    if(response.data.length === 1)
                    {
                        $("#gridProviderList li:first-child a").click();
                    }
                }
            }
        },
        error: function (data) {
            console.log(data);
            var obj = JSON.parse(data.responseText);
            $.alert({
                title: 'Information',
                content: 'please try again',
            });
        }
    });
}
function buildGridReloadlyprovider(response) {

    var mobileNumber, countryCode,providerLength;
    mobileNumber = $("#mobile").val();
    countryCode = $("#countryCode").val();
    countryIso = $("#countryIso").val();
    console.log(response);
    var data_name = [];
    var data_logo = [];
    $.each(response, function (key, value) {
        if(value.country == "Côte d'Ivoire"){
            var con ='Ivory Coast';
        }else{
            var con = value.country;
        }
        $('#gridProviderList')
            .append('\n' +
                '                                                <li class="provider col-md-6">\n' +
                '                                                    <a href="javascript:void(0);" onclick="fetchreloadlyProducts(\''+value.country_iso+'\',\''+countryCode+'\',\''+value.country_iso+'\',\''+value.provider_code+'\',\''+value.name+'\',\''+con+'\')">\n' +
                '                                                        <div class="panel panel-default provider' +
                '-'+value.provider_code+'">\n' +
                '                                                            <div class="logo">\n' +
                '<img src="' + value.logo + '">\n' +
                '                                                            </div>\n' +
                '                                                            <div class="title">' + value.name + '</div>\n' +
                '                                                        </div>\n' +
                '                                                    </a>\n' +
                '                                                </li>');
        data_name.push(value.name);
        data_logo.push(value.logo);
    });
    var linkHtml = '';
    var transferPlanUrl = api_base_url+'/tama-topup/plan_ts?accountNumber=' + encodeURIComponent(mobileNumber) + '&countryCode=' + encodeURIComponent(countryCode) + '&countryIsos=' + encodeURIComponent(countryIso);


    if ([321].includes(parseInt(countryCode))) {
        linkHtml = '<a href="' + transferPlanUrl + '">';
    } else {
        linkHtml = '<a href="javascript:void(0);" onclick="fetchreloadlyData(\'' + countryIso + '\')">';
    }
    $('#changeplan')
        .html('\n' +
            '                                                <li class="provider col-md-6">\n'  +
            linkHtml +
            '                     <div class="panel panel-default provider-bundle">\n' +
            '                                                            <div class="logo">\n' +
            '<img src="'+ data_logo +'">\n' +
            '                                                            </div>\n' +
            '                                                            <div class="title">'+ data_name +' Data</div>\n' +
            '                                                        </div>\n' +
            '                                                    </a>\n' +
            '                                                </li>');
    if(response.length === 1)
    {
        $("#gridProviderList .provider a div.panel").css("border", "1px solid");
    }
}

function fetchreloadlyData(countryCode) {
    toggleAnime('show');
    $("#gridProviderDiv").addClass('hide');
    $("#gridProviderList .provider a div.panel").css("border", "none");
    toggleProductsDiv("",'');
    $(".provider-bundle").css("border", "1px solid");
    $("#summaryDiv").addClass('hide');
    var mobileNumber;
    mobileNumber = $("#mobile").val();
    $.ajax({
        url: api_base_url+"/tama-topup/fetchreloadly/data?countryCode="+countryCode,
        method: 'GET',
        success: function (response) {
            console.log(response);
            toggleAnime('hide');
            toggleGridProviders('show');
            buildGridReloadlyproviderdata(response.data);

            if(response.data.length === 1)
            {
                $("#gridProviderList li:first-child a").click();
            }
        },
        error: function (data) {
            $("#loadingProducts").toggle();
            // console.log('fetching operator returns error ', data);
            var obj = JSON.parse(data.responseText);
            $.alert({
                title: 'Information',
                content: obj.error.message,
            });
        }
    });
}

function buildGridReloadlyproviderdata(response) {
    var mobileNumber, countryCode,providerLength;
    mobileNumber = $("#mobile").val();
    countryCode = $("#countryCode").val();
    countryIso = $("#countryIso").val();
    $('#changeplan').hide();
    // console.log(response);
    $.each(response, function (key, value) {
        if(value.country == "Côte d'Ivoire"){
            var con ='Ivory Coast';
        }else{
            var con = value.country;
        }
        $('#gridProviderList')
            .append('\n' +
                '                                                <li class="provider col-md-6">\n' +
                '                                                    <a href="javascript:void(0);" onclick="fetchreloadlyProductsbyId(\''+value.country_iso+'\',\''+countryCode+'\',\''+value.country_iso+'\',\''+value.provider_code+'\',\''+value.name+'\',\''+con+'\')">\n' +
                '                                                        <div class="panel panel-default provider' +
                '-'+value.provider_code+'">\n' +
                '                                                            <div class="logo">\n' +
                '<img src="' + value.logo + '">\n' +
                '                                                            </div>\n' +
                '                                                            <div class="title">' + value.name + '</div>\n' +
                '                                                        </div>\n' +
                '                                                    </a>\n' +
                '                                                </li>');
    });

    if(response.length === 1)
    {
        $("#gridProviderList .provider a div.panel").css("border", "1px solid");
    }
}
function fetchreloadlyProductsbyId(countryIso,countryCode, region, providerCode,providerName,providerCountry) {
    $("#loadingProducts").toggle();
    $("#gridProviderList .provider a div.panel").css("border", "none");
    toggleProductsDiv("",'');
    $(".provider-"+providerCode.toLowerCase()).css("border", "1px solid");
    $("#summaryDiv").addClass('hide');
    var mobileNumber;
    mobileNumber = $("#mobile").val();
    $.ajax({
        url: api_base_url+"/tama-topup/fetchreloadly/productsID?operator_id="+providerCode,
        method: 'GET',
        success: function (response) {
            $("#loadingProducts").toggle();
            // console.log(response);
            //update provider name and country
            $("#_hid_provider_name").val(providerName);
            $("#_hid_provider_country").val(providerCountry);
            if(response.is_denominated == true)
            {
                //list style
                toggleProductsDiv("grid",'hide');
                toggleProductsDiv("list",'show');
                buildReloadlyListProducts(response.products);
            }else{
                //range input show
                // console.log(response.products[0]);
                toggleProductsDiv("list",'hide');
                toggleProductsDiv("grid",'hide');
                toggleRangeAmountDiv('show');
                var amount_between,minVal,maxVal;
                amount_between = between_trans+" "+response.products[0].minSendValue+" - "+response.products[0].maxSendValue;
                minVal = response.products[0].minSendValue;
                maxVal = response.products[0].maxSendValue;
                $("#SkuCode").val(response.products[0].provider_code);
                $("#SendCurrencyIso").val(response.products[0].currencyCode);
                $("#exchange_rate").val(response.products[0].exchange_rate);
                $("#countryid").val(response.products[0].country);
                $("#local_amount").val(response.products[0].fx_rate);
                $("#percentage").val(response.products[0].percentage);
                $("#range_amount").attr("placeholder", amount_between)
                    .attr("title", amount_between)
                    .attr("min", minVal)
                    .attr("max", maxVal).val('');
                $("#betweenDenomination").html(amount_between);
                $("#amountReceived").html('');
                $("#amountReceivedDiv").hide();
                /*get your element and containing validated form*/
                var $that = $('#range_amount');
                var $form = $that.closest('form');
                /*get validator object for the form*/
                var validator = $form.validate();
                /*use internal code of a plugin to clear the field*/
                if (validator.settings.unhighlight) {
                    validator.settings.unhighlight.call( validator, $that[0], validator.settings.errorClass, validator.settings.validClass );
                }
                validator.hideThese( validator.errorsFor( $that[0] ) );
            }
            $("#summaryDiv").removeClass('hide');
        },
        error: function (data) {
            $("#loadingProducts").toggle();
            // console.log('fetching operator returns error ', data);
            var obj = JSON.parse(data.responseText);
            $.alert({
                title: 'Information',
                content: obj.error.message,
            });
        }
    });
}
function fetchreloadlyProducts(countryIso,countryCode, region, providerCode,providerName,providerCountry) {
    $("#loadingProducts").toggle();
    $("#gridProviderList .provider a div.panel").css("border", "none");
    toggleProductsDiv("",'');
    $(".provider-"+providerCode.toLowerCase()).css("border", "1px solid");
    $("#summaryDiv").addClass('hide');
    var mobileNumber;
    mobileNumber = $("#mobile").val();
    $.ajax({
        url: api_base_url+"/tama-topup/fetchreloadly/products?accountNumber="+mobileNumber+"&countryCode="+countryCode+"&countryIsos="+countryIso,
        method: 'GET',
        success: function (response) {
            $("#loadingProducts").toggle();
            console.log(response);
            //update provider name and country
            $("#_hid_provider_name").val(providerName);
            $("#_hid_provider_country").val(providerCountry);
            if(response.is_denominated == true)
            {
                //list style
                toggleProductsDiv("grid",'hide');
                toggleProductsDiv("list",'show');
                buildReloadlyListProducts(response.products);
            }else{
                //range input show
                console.log(response.products[0]);
                toggleProductsDiv("list",'hide');
                toggleProductsDiv("grid",'hide');
                toggleRangeAmountDiv('show');
                var amount_between,minVal,maxVal;
                amount_between = between_trans+" "+response.products[0].minSendValue+" - "+response.products[0].maxSendValue;
                minVal = response.products[0].minSendValue;
                maxVal = response.products[0].maxSendValue;
                $("#SkuCode").val(response.products[0].provider_code);
                $("#CurrencyIso").val(response.products[0].currencyCode);
                $("#exchange_rate").val(response.products[0].exchange_rate);
                $("#countryid").val(response.products[0].country);
                $("#local_amount").val(response.products[0].fx_rate);
                $("#percentage").val(response.products[0].percentage);
                $("#range_amount").attr("placeholder", amount_between)
                    .attr("title", amount_between)
                    .attr("min", minVal)
                    .attr("max", maxVal).val('');
                $("#betweenDenomination").html(amount_between);
                $("#amountReceived").html('');
                $("#amountReceivedDiv").hide();
                /*get your element and containing validated form*/
                var $that = $('#range_amount');
                var $form = $that.closest('form');
                /*get validator object for the form*/
                var validator = $form.validate();
                /*use internal code of a plugin to clear the field*/
                if (validator.settings.unhighlight) {
                    validator.settings.unhighlight.call( validator, $that[0], validator.settings.errorClass, validator.settings.validClass );
                }
                validator.hideThese( validator.errorsFor( $that[0] ) );
            }
            $("#summaryDiv").removeClass('hide');
        },
        error: function (data) {
            $("#loadingProducts").toggle();
            // console.log('fetching operator returns error ', data);
            var obj = JSON.parse(data.responseText);
            $.alert({
                title: 'Information',
                content: obj.error.message,
            });
        }
    });
}

function buildReloadlyListProducts(products) {
    console.log(products[0]);
    $.each(products, function (key, value) {
        var text = value.display_text;
        var will_be_received = ''; // Define this if needed
        var descriptionSafe = value.description ? value.description.replace(/'/g, "\\'").replace(/\n/g, ' ') : '';

        var receiveAmountHtml = '';
        if (value.description && value.description.trim() !== '') {
            receiveAmountHtml = '<div class="receive-amount">\n' +
                '    <span class="strong-mobile">' + value.description + '</span>\n' +
                '</div>\n';
        } else {
            receiveAmountHtml = '<div class="receive-amount">\n' +
                '    <span class="strong-mobile">' + value.RecivedCurrencyIso + text + '</span>\n' +
                '    <span class="light-mobile">' + will_be_received + '</span>\n' +
                '</div>\n';
        }

        $("#productLists").append(
            '<li class="denomination">\n' +
            '    <a href="javascript:void(0);" class="li-a" onclick="gridReloadlyClickLists(this,\'' + value.provider_code + '\',\'' + value.minSendValue + '\',\'' + value.display_text + '\',\'' + value.commission + '\',\'' + value.sendValueOriginal + '\',\'' + value.country_iso + '\',\'' + descriptionSafe + '\')">\n' +
            '        <div class="panel panel-default panel-data activatable-item">\n' +
            '            <div class="data">\n' +
            '                <div class="price">\n' +
            '                    <h3>' + value.sendCurrencyIso + value.minSendValue + '</h3>\n' +
            '                </div>\n' +
            receiveAmountHtml +
            '                <div class="validity">\n' +
            '                    <div>' + value.validity + '</div>\n' +
            '                    <div></div>\n' +
            '                </div>\n' +
            '                <div class="more-info-toggle open">\n' +
            '                    <i class="fa fa-chevron-right"></i>\n' +
            '                </div>\n' +
            '                <div class="more-info" style="display: none;">\n' +
            '                    <p>\n' +
            '                        <span>' + value.description + '</span>\n' +
            '                    </p>\n' +
            '                </div>\n' +
            '                <div class="clearfix"></div>\n' +
            '            </div>\n' +
            '            <div class="active-icon">\n' +
            '                <i class="fa fa-check"></i>\n' +
            '            </div>\n' +
            '        </div>\n' +
            '    </a>\n' +
            '</li>'
        );
    });
}

function gridReloadlyClickLists(el, operatorId,SendValue,local_value,Commission,sendValueOriginal,kk,description) {
    $('.product-lists li.active').removeClass('active');
    $('.product-lists li').find('.more-info').hide();
    $(el).parents('li').addClass('active');
    $("#operatorId").val(operatorId);
    $("#amount").val(SendValue);
    $("#description").val(description);
    $("#sendValue").val(sendValueOriginal);
    $("#Recivedamount").val(local_value);
    $("#Commission").val(Commission);
    $("#Country_fixed").val(kk);
    $("#denomination").val('fixed');
}
function buildNoResponse(){
    $("#productLists").append('<li class="denomination">\n' +
        '                                                <a href="javascript:void(0);" >\n' +
        '                                                    <div class="panel panel-default panel-data activatable-item">\n' +
        '                                                        <div class="data">\n' +
        '                                                            <div>\n' +
        '                                                               <h2>There Has Been Error In This Operator</h2></h>\n' +
        '                                                            </div>\n' +
        '                                                            <div class="clearfix"></div>\n' +
        '                                                            <!-- /ko -->\n' +
        '                                                        </div>\n' +
        '                                                    </div>\n' +
        '                                                </a>\n' +
        '                                            </li>');
}
function buildNoReloadlyRes(){
    $("#productLists").append('<li class="denomination">\n' +
        '                                                <a href="javascript:void(0);" >\n' +
        '                                                    <div class="panel panel-default panel-data activatable-item">\n' +
        '                                                        <div class="data">\n' +
        '                                                            <div>\n' +
        '                                                               <h2>Nous sommes désolés pour le dérangement; nous rencontrons actuellement un problème avec l\'opérateur et fournirons une mise à jour une fois le problème résolu.</h2></h>\n' +
        '                                                            </div>\n' +
        '                                                            <div class="clearfix"></div>\n' +
        '                                                            <!-- /ko -->\n' +
        '                                                        </div>\n' +
        '                                                    </div>\n' +
        '                                                </a>\n' +
        '                                            </li>');
}
/**
 * Get Providers for entered mobile number
 * @param ajaxUrl
 */

function gettransferservice(ajaxUrl) {
    mobileNumber = $("#mobile").val();
    countryCode = $("#countryCode").val();
    countryIso = $("#countryIso").val();
    $.ajax({
        url: ajaxUrl,
        method: 'GET',
        success: function (response) {
if (response.status === 400) {
    console.log(response.status);

    let msg = response.message; // raw string or object

    // If it's a JSON string, parse it
    if (typeof msg === 'string') {
        try {
            msg = JSON.parse(msg);
        } catch (e) {
            console.error('Failed to parse message JSON:', e);
        }
    }

    // Extract final text
    let finalMessage = msg && msg.error && msg.error.message
        ? msg.error.message
        : 'Unknown error';

    $.alert({
        title: 'Error',
        content: finalMessage,
        buttons: {
            ok: {
                text: 'OK',
                action: function () {
                    // Redirect to tama-topup
                    window.location.href = '/tama-topup';
                }
            }
        }
    });
}
            toggleAnime('hide');
            toggleGridProviders('show');
            buildGridTransferProviders(response.data);
            if(response.data.length === 1)
            {
                // $("#gridProviderList li:first-child a").click();
                TwoProducts(response.data);
            }
        },
        error: function (data) {
            console.log('fetching operator returns error ', data);
            var obj = JSON.parse(data.responseText);
            $.alert({
                title: 'Information',
                content: obj.error.message,
            });
        }
    });
}
/**
 * build providers as grid
 * @param providers
 */
function TwoProducts(providers) {
    var products = ['Airtime', 'Data & Bundle'];
    var mobileNumber = $("#mobile").val();
    var countryCode = $("#countryCode").val();

    $('#twoproducts').empty(); // Clear previous entries

    var hideAirtimeCountries = [321]; // Countries where Airtime should be hidden
    var shouldHideAirtime = hideAirtimeCountries.includes(parseInt(countryCode));

    // Set column class based on country
    var colClass = shouldHideAirtime ? 'col-md-12' : 'col-md-6';

    $.each(products, function (key, product) {
        // Skip Airtime only if country is in hide list
        if (shouldHideAirtime && product === 'Airtime') {
            return; // Skip adding Airtime
        }

        $.each(providers, function (key, value) {
            var flag = value.provider_code;

            $('#twoproducts').append(
                '<li class="provider ' + colClass + '">' +
                '<a href="javascript:void(0);" onclick="fetchTransferProducts(\'' + value.provider_code + '\',\'' + countryCode + '\',\'' + value.country_iso + '\',\'' + value.provider_code + '\',\'' + value.name + '\',\'' + value.country + '\',\'' + product + '\')">' +
                '<div class="panel panel-default provider-' + value.provider_code + '-c">' +
                '<div class="logo">' +
                '<img src="https://operator-logo.dtone.com/logo-' + flag + '-1.jpg">' +
                '</div>' +
                '<div class="title">' + product + '</div>' +
                '</div>' +
                '</a>' +
                '</li>'
            );
        });
    });

    if (providers.length === 1) {
        $("#twoproducts .provider a div.panel").css("border", "1px solid");

        if (shouldHideAirtime) {
            // Automatically trigger click only for countries 221, 223, 91
            setTimeout(function () {
                $("#twoproducts .provider a").first().click();
            }, 10);
        }
    }
}
/**
 * build providers as grid
 * @param providers
 */
function buildGridTransferProviders(providers) {
    // console.log(providers);
    var mobileNumber, countryCode,providerLength;
    mobileNumber = $("#mobile").val();
    countryCode = $("#countryCode").val();
    $.each(providers, function (key, value) {
        var flag = value.provider_code;
        $('#gridProviderList')
            .append('\n' +
                '                                                <li class="provider col-md-6">\n' +
                '                                                    <a href="javascript:void(0);">\n' +
                '                                                        <div class="panel panel-default provider-'+value.provider_code+'-c">\n' +
                '                                                            <div class="logo">\n' +
                '<img src="https://operator-logo.dtone.com/logo-' + flag + '-1.jpg">\n' +
                '                                                            </div>\n' +
                '                                                            <div class="title">' + value.name + '</div>\n' +
                '                                                        </div>\n' +
                '                                                    </a>\n' +
                '                                                </li>');
    });
    if(providers.length === 1)
    {
        $("#gridProviderList .provider a div.panel").css("border", "1px solid");
    }
}

function fetchTransferProducts(providercode,country_id, country_iso, country,mobileNumber,countryCode,product) {
    $("#loadingProducts").toggle();
    $("#gridProviderList .provider a div.panel").css("border", "none");
    toggleProductsDiv("",'');
    $(".provider-"+providercode+"-c").css("border", "1px solid");
    $("#summaryDiv").addClass('hide');
    var mobileNumber;
    var link = "/tama-topup/fetchtransfer/products?&country_iso_code="+country_iso+"&operator_id="+providercode+"&accountNumber="+mobileNumber+"&type=FIXED_VALUE_RECHARGE";
    if(product == 'Airtime'){
        var link = "/tama-topup/fetchtransfer/products?&country_iso_code="+country_iso+"&operator_id="+providercode+"&accountNumber="+mobileNumber+"&type=RANGED_VALUE_RECHARGE";
    }
    mobileNumber = $("#mobile").val();
    $.ajax({
        url: api_base_url+link,
        method: 'GET',
        success: function (response) {
            console.log(response);
            // if((response.data).length >= 0){
            //     window.location.href = api_base_url + "/tama-topup/ding_plans?accountNumber=" + mobileNumber + "&countryCode=" + countryCode + "&countryIsos=" + countryIso;
            // }
            $("#loadingProducts").toggle();
            // $("#_hid_provider_name").val(providerName);
            // $("#_hid_provider_country").val(providerCountry);
            if(response.data.denomination_style == "list" && response.data.is_denominated == true)
            {
                //list style
                toggleProductsDiv("grid",'hide');
                toggleProductsDiv("list",'show');
                buildListTransferProducts(response.data.products);
            }else if(response.data.denomination_style == "false" && response.data.is_denominated == false )
            {
                toggleProductsDiv("grid",'hide');
                toggleProductsDiv("list",'show');
                buildNoResponse();
            }else if(response.data.denomination_style == "grid" && response.data.is_denominated == true)
            {
                //grid style
                toggleProductsDiv("list",'hide');
                toggleProductsDiv("grid",'show');
                buildListTransferProducts(response.data.products);
            }else{
                if(product == "Airtime"){
                    //range input show
                    var pro = response.data.products[0];
                    toggleProductsDiv("list",'hide');
                    toggleProductsDiv("grid",'hide');
                    toggleRangeAmountDiv('show');
                    var amount_between,minVal,maxVal;
                    amount_between = between_trans+" "+(pro.minSendValue).toFixed(2)+" - "+Number((pro.maxSendValue)).toFixed(2);
                    minVal = pro.minSendValue;
                    maxVal = pro.maxSendValue;
                    $("#skuCode").val(pro.provider_code);
                    $("#CurrencyIso").val(pro.currencyCode);
                    $("#exchange_rate").val(pro.exchange_rate);
                    $("#countryid").val(pro.country);
                    $("#local_amount").val(pro.fx_rate);
                    $("#percentage").val(pro.percentage);
                    $("#operator_id").val(pro.product_code);
                    $("#sendCurrencyIso").val(pro.sendCurrencyIso);
                    $("#receiveCurrencyIso").val(pro.RecivedCurrencyIso);
                    $("#operator_name").val(pro.operator_name);
                    $("#name").val(pro.name);
                    $("#country").val(pro.country);
                    $("#validity").val("Range");
                    $("#display_text").val("Range");
                    $("#description").val("Range");


                    $("#range_amount").attr("placeholder", amount_between)
                        .attr("title", amount_between)
                        .attr("min", minVal)
                        .attr("max", maxVal).val('');
                    $("#betweenDenomination").html(amount_between);
                    $("#amountReceived").html('');
                    $("#amountReceivedDiv").hide();
                    /*get your element and containing validated form*/
                    var $that = $('#range_amount');
                    var $form = $that.closest('form');
                    /*get validator object for the form*/
                    var validator = $form.validate();
                    /*use internal code of a plugin to clear the field*/
                    if (validator.settings.unhighlight) {
                        validator.settings.unhighlight.call( validator, $that[0], validator.settings.errorClass, validator.settings.validClass );
                    }
                }else{
                    toggleProductsDiv("grid",'hide');
                    toggleProductsDiv("list",'show');
                    buildNoResponse();
                }
            }
            $("#summaryDiv").removeClass('hide');
        },
        error: function (data) {
            $("#productLists").empty();
            $("#loadingProducts").toggle();
            // console.log('fetching operator returns error ', data);
            var obj = JSON.parse(data.responseText);
            $.alert({
                title: 'Information',
                content: obj.error.message,
            });
        }
    });
}
function buildListTransferProducts(products) {

    $("#productLists").empty();
    $.each(products, function (key, value){
        buildloopTransfer(value);
    });
}
function buildloopTransfer(value){
    var display = value.display_text;
    var description = value.description;
    if(value.description == null){
        var description = '';
    }
    if(value.display_text == ''){
        var display = value.name;

    }
    $("#productLists").append('<li class="denomination">\n' +
        ' <a href="javascript:void(0);" class="li-a" onclick=\'clickProductLists(this,' +
        JSON.stringify(value.name) + ',' +
        JSON.stringify(value.provider_code) + ',' +
        JSON.stringify(value.validity) + ',' +
        JSON.stringify(value.display_text) + ',' +
        JSON.stringify(value.description) + ',' +
        JSON.stringify(value.ReceiveValue) + ',' +
        JSON.stringify(value.SendValue) + ',' +
        JSON.stringify(value.sendCurrencyIso) + ',' +
        JSON.stringify(value.receiveCurrencyIso) + ',' +
        JSON.stringify(value.operator_id) + ',' +
        JSON.stringify(value.operator_name) + ',' +
        JSON.stringify(value.country) +
        ')\'>\n' +
        '                                                    <div class="panel panel-default panel-data activatable-item">\n' +
        '                                                        <div class="data">\n' +
        '                                                            <div class="price">\n' +
        '                                                                <!-- ko ifnot: isDomesticProduct -->\n' +
        '                                                                <h3 >€' + value.SendValue + '</h3>\n' +
        '                                                                <!-- /ko -->\n' +
        '                                                            </div>\n' +
        '                                                            <div class="receive-amount">\n' +
        '                                                                <span class="strong-mobile">' + display + '</span>\n' +
        '                                                            </div>\n' +
        '                                                            <div class="validity">\n' +
        '                                                                <div>' + value.validity + '</div>\n' +
        '                                                                <button class="btn btn-primary">' + value.tags + '</button>\n' +
        '                                                                <div></div>\n' +
        '                                                            </div>\n' +
        '                                                            <!-- ko if: hasDescription -->\n' +
        '                                                            <div class="more-info-toggle open">\n' +
        '                                                                <i class="fa fa-chevron-right"></i>\n' +
        '                                                            </div>\n' +
        '                                                            <!-- /ko -->\n' +
        '                                                            <div class="more-info" style="display: none;">\n' +
        '                                                                <p>\n' +
        '                                                                <span >(' + value.name + ')</span>\n' +
        '                                                                    <span >' + description + '</span>\n' +
        '                                                                </p>\n' +
        '                                                                <p></p>\n' +
        '                                                            </div>\n' +
        '                                                            <div class="clearfix"></div>\n' +
        '                                                            <!-- /ko -->\n' +
        '                                                        </div>\n' +
        '                                                        <div class="active-icon">\n' +
        '                                                            <i class="fa fa-check"></i>\n' +
        '                                                        </div>\n' +
        '                                                    </div>\n' +
        '                                                </a>\n' +
        '                                            </li>');
}
function clickProductLists(el,name, skuCode,validity,display_text,description,ReceiveValue,SendValue,sendCurrencyIso,receiveCurrencyIso,operator_id,operator_name,country) {
    // console.log(operator_id,operator_name,country);
    $('.product-lists li.active').removeClass('active');
    $('.product-lists li').find('.more-info').hide();
    $(el).parents('li').addClass('active');
    $("#name").val(name);
    $("#skuCode").val(skuCode);
    $("#validity").val(validity);
    $("#display_text").val(display_text);
    $("#description").val(description);
    $("#ReceiveValue").val(ReceiveValue);
    $("#SendValue").val(SendValue);
    $("#sendCurrencyIso").val(sendCurrencyIso);
    $("#receiveCurrencyIso").val(receiveCurrencyIso);
    $("#operator_id").val(operator_id);
    $("#operator_name").val(operator_name);
    $("#country").val(country);
    $(el).find('.more-info').toggle();
    scrollingElement = (document.scrollingElement || document.body)
    $(scrollingElement).animate({
        scrollTop: document.body.scrollHeight
    }, 500);
}