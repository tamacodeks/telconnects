
$(".arrival").hide();
$(".margin-bottom").hide();
$(document).on('click', '#two_way', function () {
    $(".arrival").show();
});
$(document).on('click', '#one_way', function () {
    $(".arrival").hide();
});

$("#frmBusBookbtn").click(function (e) {
    var from = $("#cityFrom").val();
    var to = $("#cityTo").val();
    var departure_date = $("#departureDate").val();
    var passengers = $("#passengers").val();
    if (from == '') {
        $("#cityFrom").focus();
    }else if(to == ''){
        $("#cityTo").focus();
    }else if(departure_date == ''){
        $("#departureDate").focus();
    }else if(passengers == ''){
        $("#passengers").focus();
    }else{

    }
    $('#frmBusBookbtn').html("<i class='fa fa-circle-notch fa-spin'></i>&nbsp; Processing...").attr('disabled', 'disabled');
    $("#sort_by").change();

});
$("#sort_by").change(function (e) {

    $("#gridBus").empty();
    $("#detectBooking").addClass('hide');
    toggleAnime('show');
    var search_by, from, to,departure_date,arrivalDate,adult,children,currency,cityFrom,cityTo,passengers,sort_by;
    search_by = 'city';
    from = $("#cityFrom").val();
    to = $("#cityTo").val();
    departure_date = $("#departureDate").val();
    arrivalDate = $("#arrivalDate").val();
    adult = $("#adult").val();
    children = $("#child").val();
    cityFrom = $("#cityFromHid").val();
    cityTo = $("#cityToHid").val();
    passengers = $("#passengers").val();
    sort_by = $("#sort_by").val();
    getBookingDetails(api_base_url + "/bus/search?search_by=" + search_by + "&from=" + from + "&to=" + to + "&departure_date=" + departure_date + "&arrivalDate=" + arrivalDate + "&adult=" + adult + "&children=" + children + "&currency=" + currency + "&cityFrom=" + cityFrom + "&cityTo=" + cityTo + "&passengers=" + passengers + "&sort_by=" + sort_by);
});
function getTimer(){
    var timer2 = "15:00";

    var interval = setInterval(function() {

        var timer = timer2.split(':');
        //by parsing integer, I avoid all extra string processing
        var minutes = parseInt(timer[0], 10);
        var seconds = parseInt(timer[1], 10);
        --seconds;
        minutes = (seconds < 0) ? --minutes : minutes;
        if (minutes < 0){
            $.alert({
                title: 'Your cart has expired',
                content: "The seats reserved in your shopping cart have been removed because the reservation time of 13 minutes was up",
            });
            window.location.href = api_base_url + "/flix-bus";
            clearInterval(interval);
            $('.countdown').empyt();
        }
        seconds = (seconds < 0) ? 59 : seconds;
        seconds = (seconds < 10) ? '0' + seconds : seconds;
        //minutes = (minutes < 10) ?  minutes : minutes;
        $('.countdown').html(minutes + ':' + seconds);
        timer2 = minutes + ':' + seconds;
    }, 1000);
}
function toggleAnime(className) {
    if(className == "hide")
    {
        $("#detectAnime").addClass('hide');
    }else{
        $("#detectAnime").removeClass('hide');
    }
}
function getBookingDetails(ajaxUrl) {
    $.ajax({
        url: ajaxUrl,
        method: 'GET',
        success: function (response) {
            if(response.data != ''){
                buildBusGrid(response.data);
                $(".margin-bottom").show();
                toggleAnime('hide');
            }else{
                buildNoBus();
                $(".margin-bottom").show();
                toggleAnime('hide');
            }
            $('#frmBusBookbtn').html("Search").removeAttr('disabled');
            scrollingElement = (document.scrollingElement || document.body)
            $(scrollingElement).animate({
                scrollTop: document.body.scrollHeight
            }, 5);
        },
        error: function (data) {
            console.log(data);
            var obj = JSON.parse(data.responseText);
            toggleAnime('hide');
            $.alert({
                title: 'Information',
                content: obj.error.message,
            });
            $('#frmBusBookbtn').html("Search").removeAttr('disabled');
            scrollingElement = (document.scrollingElement || document.body)
            $(scrollingElement).animate({
                scrollTop: document.body.scrollHeight
            }, 500);
        }
    });
}
function buildNoBus() {
    var departure_date = $("#departureDate").val();
    $('#gridBus').append('\n' +
        '<div class="panel panel-default">'+
        ' <div class="panel-body">'+
        '     <img src="'+api_base_url+'/images/no-bus.jpg" class="nobus">' +
        ' </div>'+
        '</div>');
}
function buildBusGrid(response) {
    $.each(response, function (key, value) {
        $('#gridBus')
            .append('\n' +
                '<div class="col-md-6">'+
                '  <div class="panel panel-default send-tama-panel products">'+
                '     <div class="panel-body">'+
                '          <div id="header">'+
                '              <div class="">'+
                '                  <div class="col-md-4">'+
                '                     <label>From</label>'+
                '                     <h5 class="product-name">'+value.from_name+'</h5>'+
                '                     <h4 class="product-name">'+value.departure+'</h4>'+
                '                  </div>'+
                '                  <div class="col-md-4">'+
                '                       <label>Duration</label>'+
                '                           <h4>'+value.duration_hour +' : '+value.duration_minutes+' Hrs</h4>'+
                '                  </div>'+
                '                  <div class="col-md-4">'+
                '                           <label>To</label>'+
                '                     <h5 class="product-name">'+value.to_name+'</h5>'+
                '                     <h4 class="product-name">'+value.arrival+'</h4>'+
                '                  </div>'+
                '               </div>'+
                '           </div>'+
                '   </div>'+
                '    <div class="panel-footer">'+
                '       <div class="col-md-4">'+
                '           <h5>'+
                '               Available Seats - '+value.available_seats+' '+
                '                  <br>'+
                '               Bus Type - '+value.bus_type+' '+
                '            </h5>'+
                '        </div>'+
                '     <div class="col-md-4">'+
                '          <h4><span class="fa fa-euro-sign">'+value.total_price+'</span>'+
                '          </h4>'+
                '      </div>'+
                '      <div class="col-md-4">'+
                '          <a href="javascript:void(0);" class="btn btn-danger" onclick="createReservations(\''+value.bus_uid+'\',\''+value.adult+'\',\''+value.children+'\',\''+value.bikes+'\',\''+value.currency+'\',\''+value.total_price+'\')">' +
                'Reserve '+value.total_selected_seats+' Seat</i></a>'+
                '      </div>'+
                '     <div class="clearfix"></div>'+
                ' </div>'+
                ' </div>'+
                '</div>');
    });
}
function createReservations(bus_uid, adult, children,bikes,currency,price) {
    $(".margin-bottom").hide();
    toggleAnime('show');
    $("#passenger_details").html('');
    $('#pas').html('');
    $('#frmBusBoo1k').trigger("reset");
    $.ajax({
        url: api_base_url+"/bus/create_reservations",
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: { trip_uid: bus_uid, adult: adult,children: children,bikes: bikes,currency: currency,price:price },
        success: function (response) {

            if(response.status== 200){
                toggleAnime('hide');
                buildReservation(response);
                getTimer();
            }else{
                toggleAnime('hide');
                $(".margin-bot").hide();
                $(".send-tama-panel").hide();
                $(".margin-bottom").show();
                buildNoBus();
            }

        },
        error: function (data) {
            toggleAnime('hide');
            $.alert({
                title: 'Information',
                content: obj.error.message,
            });
        }
    });
}
function buildReservation(response) {
    var d = response.data[0].departure_time;
    var n = d.toString();
    $(".margin-bottom").hide();
    $("#detectBooking").removeClass('hide');
    $("#des_from").html(response.data[0].from_name);
    $("#des_to").html(response.data[0].to_name);
    $("#dep_time").html(n);
    $("#price_show").html(response.data[0].price);
    $("#total_price").html(response.data[0].price);
    var count_passengers = response.data[0].passenger_details.length;
    var split_price = response.data[0].price / count_passengers ;
    $.each(response.data[0].passenger_details, function (key, value) {
        $('#passenger_details')
            .append('\n' +
                '<div class="col-md-12">'+
                '<h4 style="font-weight: bold">#'+value.passenger_no +' '+value.product_type+'</h4>'+
                '</div>'+
                ' <div class="col-md-4">'+
                '<label>Firstname</label>'+
                '<input type="text" class="booking-input" placeholder="firstname" name="firstname[]" >'+
                '</div>'+
                '<div class="col-md-4">'+
                '<label>Lastname</label>'+
                '<input type="text" class="booking-input" placeholder="Lastname" name="lastname[]">'+
                '</div>'+
                '<div class="col-md-4">'+
                '<label>Date of Birth</label>'+
                '<input type="text" class="datepic'+key+' booking-input" placeholder="birthdate" name="birthdate[]" ' +
                'id="birthdate'+value.product_type+''+key+'" readonly>'+
                '</div>'+

                '<input type="hidden"  name="reservation_token"  value="'+response.data[0].reservation_token+'">' +
                '<input type="hidden"  name="reservation_id" value="'+response.data[0].reservation_id+'">' +
                '<input type="hidden"  name="type[]" value="'+value.type+'">' +
                '<input type="hidden"  name="product_type[]"  value="'+value.product_type+'">' +
                '<input type="hidden"  name="reference_id[]"  value="'+value.reference_id+'">' +
                '<input type="hidden"  name="passenger_no[]" value="'+value.passenger_no+'">');
        $('#pas').append('\n' +
            '<div class="col-md-8 col-xs-8">'+
            '<h5 style="font-weight: bold">#'+value.passenger_no +' '+value.product_type+'</h5>'+
            '</div>'+
            '<div class="col-md-4 col-xs-4">'+
            '<h4>€ '+split_price+'</h4>'+
            '</div>');
        $("#birthdateadult"+key).datepicker( {
            dateFormat : "dd.mm.yy",
            firstDay: 1,
            changeMonth: true,
            changeYear: true,
            autosize: true,
            minDate: '-100y',
            maxDate: '-14y',
        });
        $("#birthdatechildren"+key).datepicker( {
            dateFormat : "dd.mm.yy",
            firstDay: 1,
            changeMonth: true,
            changeYear: true,
            autosize: true,
            minDate: '-15y',
            maxDate: new Date(),
        });
    });
}
$("#confirm_btn").click(function (e) {

    $("#detectBooking").addClass('hide');
    $( "#loadergif" ).addClass( "loadergif loadergif-default is-active" );
    toggleAnime('show');
    var reservation_token, reservation_id, type,product_type,reference_id,passenger_no,firstname,lastname,birthdate,email,phone_number;
    total_price = $("#total_price").html();

    reservation_token = $("input[name='reservation_token']").map(function(){return $(this).val();}).get();
    reservation_id = $("input[name='reservation_id']").map(function(){return $(this).val();}).get();
    type = $("input[name='type[]']").map(function(){return $(this).val();}).get();
    product_type = $("input[name='product_type[]']").map(function(){return $(this).val();}).get();
    reference_id = $("input[name='reference_id[]']").map(function(){return $(this).val();}).get();
    passenger_no = $("input[name='passenger_no[]']").map(function(){return $(this).val();}).get();
    firstname = $("input[name='firstname[]']").map(function(){return $(this).val();}).get();
    lastname = $("input[name='lastname[]']").map(function(){return $(this).val();}).get();
    birthdate = $("input[name='birthdate[]']").map(function(){return $(this).val();}).get();
    email = $("#email").val();
    phone_number = $("#phone_number").val();
    $.ajax({
        url: api_base_url+"/bus/add_passengers_details",
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {reservation_token: reservation_token, reservation_id: reservation_id,type: type,product_type: product_type,reference_id: reference_id,passenger_no: passenger_no,birthdate: birthdate,firstname: firstname,lastname: lastname,email:email,total_price: total_price,phone_number:phone_number},
        success: function (response) {
            console.log(response);
            if(response.status == 500){
                if(response.error.code == 201){
                    $.alert({
                        title: 'Error',
                        content: "Please fill neccessary fields, validation failed!",
                    });
                    $("#detectBooking").removeClass('hide');
                    $( "#loadergif" ).removeClass( "loadergif loadergif-default is-active" );
                    toggleAnime('hide');
                }else{
                    $.alert({
                        title: 'Error',
                        content: response.error.message,
                    });
                    window.location.href = api_base_url + "/flix-bus";
                    $("#detectBooking").removeClass('hide');
                    $( "#loadergif" ).removeClass( "loadergif loadergif-default is-active" );
                    toggleAnime('hide');
                }
            }else{
                window.open(response, '_blank');
                window.location.href = api_base_url + "/transactions";
            }

        },
        error: function (data) {
            $("#detectAnime").removeClass('hide');
            var obj = JSON.parse(data.responseText);
            $.alert({
                title: 'Information',
                content: obj.error.message,
            });
        }
    });

});

$("document").ready(function () {
    $.localStorage.remove('adult');
    $.localStorage.remove('child');
    $.localStorage.remove('bikes');

});
$(document).on('click', function (e) {
    $('[data-toggle="popover"],[data-original-title]').each(function () {
        //the 'is' for buttons that trigger popups
        //the 'has' for icons within a button that triggers a popup
        if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
            (($(this).popover('hide').data('bs.popover') || {}).inState || {}).click = false  // fix for BS 3.3.6
        }
    });
});
$(function () {
    var $popover = $('.popover-markup>.trigger').popover({
        container: 'body',
        html: true,
        placement: 'top',
        content: function () {
            return $(this).parent().find('.content').html();
        }
    });

    // open popover & inital value in form
    var passengers = [0, 0, 0];
    $('.popover-markup>.trigger').click(function (e) {
        e.stopPropagation();
        $(".popover-content input").each(function (i) {
            $(this).val(passengers[i]);
        });
    });
    // close popover
    $(document).click(function (e) {
        if ($(e.target).is('.demise')) {
            $('.popover-markup>.trigger').popover('hide');
        }
    });
// store form value when popover closed
    $popover.on('hide.bs.popover', function () {
        $(".popover-content input").each(function (i) {
            passengers[i] = $(this).val();
        });
    });
    // spinner(+-btn to change value) & total to parent input
    $(document).on('click', '.number-spinner a', function () {
        var btn = $(this),
            input = btn.closest('.number-spinner').find('input'),
            oldValue = input.val().trim(),
            forSpinner = btn.attr('data-choser');
        var adults = 0;
        var children = 0;
        var bikes = 0;
        var appendString = '';
        if (btn.attr('data-dir') == 'up') {
            if (oldValue < input.attr('max')) {
                oldValue++;
            }
        } else {
            if (oldValue > input.attr('min')) {
                oldValue--;
            }
        }
        input.val(oldValue);
        if ($.localStorage.isSet(forSpinner)) {
            $.localStorage.set(forSpinner, oldValue);
        } else {
            // var newValue = storage.getItem(forSpinner);
            $.localStorage.set(forSpinner, oldValue);
        }
        adults = $.localStorage.get('adult');
        children = $.localStorage.get('child');
        bikes = $.localStorage.get('bikes');
        if (!$.localStorage.isEmpty('adult')) {
            appendString += " Adults: " + adults + ", ";
        }
        if (!$.localStorage.isEmpty('child')) {
            appendString += " Children: " + children + ", ";
        }
        if (!$.localStorage.isEmpty('bikes')) {
            appendString += " Bike: " + bikes;
        }
        $('#passengers').val(appendString);
        $("#adult").val(adults);
        $("#child").val(children);
        $("#bikes").val(bikes);
    });
});
