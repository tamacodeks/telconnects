
 <!-- latest jquery-->
 <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.js"></script>
 <!-- Bootstrap js-->
<script src="{{asset('assets/js/bootstrap/bootstrap.bundle.min.js')}}"></script>
<!-- feather icon js-->
<script src="{{asset('assets/js/icons/feather-icon/feather.min.js')}}"></script>
<script src="{{asset('assets/js/icons/feather-icon/feather-icon.js')}}"></script>
<!-- scrollbar js-->
<script src="{{asset('assets/js/scrollbar/simplebar.js')}}"></script>
<script src="{{ asset('assets/js/scrollbar/custom.js') }}?v={{ @filemtime(public_path('assets/js/scrollbar/custom.js')) ?: time() }}"></script>
<!-- Sidebar jquery-->
<script src="{{asset('assets/js/config.js')}}"></script>
<!-- Plugins JS start-->
{{--<script src="{{ asset('assets/js/chart/apex-chart/apex-chart.js') }}"></script>--}}
{{--<script src="{{ asset('assets/js/chart/apex-chart/stock-prices.js') }}"></script>--}}
{{-- V2 sidebar state is handled by the scoped script below. --}}
<script src="{{ asset('assets/js/slick/slick.min.js') }}"></script>
<script src="{{ asset('assets/js/slick/slick.js') }}"></script>
<script src="{{ asset('assets/js/header-slick.js') }}"></script>
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" type="text/css" href="{{asset('assets/css/vendors/select2.css')}}">
<link rel="stylesheet" type="text/css" href="{{asset('assets/css/vendors/date-picker.css')}}">
@if(Route::current()->getName() != 'popover') 
	<script src="{{asset('assets/js/tooltip-init.js')}}"></script>
@endif

<!-- Plugins JS Ends-->
<!-- Theme js-->
<script src="{{ asset('assets/js/script.js') }}?v={{ @filemtime(public_path('assets/js/script.js')) ?: time() }}"></script>
{{-- <script src="{{asset('assets/js/theme-customizer/customizer.js')}}"></script> --}}
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="{{asset('assets/js/select2/select2.full.min.js')}}"></script>

<script src="{{asset('assets/js/datepicker/date-picker/datepicker.js')}}"></script>
<script src="{{asset('assets/js/datepicker/date-picker/datepicker.en.js')}}"></script>
<script src="{{asset('assets/js/datepicker/date-picker/datepicker.custom.js')}}"></script>

<script>
// bootstrap-select asset is not present in this project build; avoid runtime crashes on pages
// that still call `$('.select-picker').selectpicker()`.
if (window.jQuery && !jQuery.fn.selectpicker) {
    jQuery.fn.selectpicker = function () { return this; };
}
</script>
{{-- @if(Route::current()->getName() == 'index') 
	<script src="{{asset('assets/js/layout-change.js')}}"></script>
@endif --}}
@if(session('message'))
    @php
        $flashType = strtolower((string) session('message_type', 'info'));
        $allowedFlashTypes = ['success', 'error', 'warning', 'info', 'question'];
        if (!in_array($flashType, $allowedFlashTypes, true)) {
            $flashType = 'info';
        }
        $flashTitle = $flashType === 'success' ? 'Success!' : ($flashType === 'error' ? 'Error!' : 'Notice');
        $flashMessage = (string) session('message');
    @endphp
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: @json($flashTitle),
                text: @json($flashMessage),
                icon: @json($flashType),
                timer: 3000, // Closes after 3 seconds
                showConfirmButton: false
            });
        });

        function error(){
            Swal.fire({
                title: @json($flashTitle),
                text: @json($flashMessage),
                icon: @json($flashType),
                timer: 3000, // Closes after 3 seconds
                showConfirmButton: false
            });
        }
    </script>
@endif
<script>
(function ($) {
  function totalOptions($el){
    return $el.find('option').filter(function(){ return $(this).val() !== ''; }).length;
  }
  function updateSummary($el){
    var count = ($el.val() || []).length;
    var total = totalOptions($el);
    var text  = !count ? ($el.data('placeholder') || 'Select options')
             : (count === total ? ('All ('+total+')') : (count+' selected'));
    $el.next('.select2-container').find('.select2-selection__rendered').attr('data-summary', text);
  }

  function getDropdown($sel){
    var s2 = $sel.data('select2');
    return (s2 && (s2.$dropdown || (s2.dropdown && s2.dropdown.$dropdown))) || $('.select2-container--open .select2-dropdown').last();
  }

  function ensureToolbar($sel){
    var $dropdown = getDropdown($sel);
    var $ul = $dropdown.find('.select2-results > .select2-results__options');
    if (!$ul.length || $ul.data('has-toolbar')) return;

    var $bar = $(
      '<li class="select2-actions" role="presentation">' +
        '<button type="button" class="s2-btn" data-action="select-all">Select</button>' +
        '<button type="button" class="s2-btn s2-btn--outline" data-action="clear-all">Unselect</button>' +
      '</li>'
    );
    $ul.prepend($bar).data('has-toolbar', true);

    $ul.off('click.s2actions')
      .on('click.s2actions','[data-action="select-all"]', function(e){
        e.preventDefault();
        $sel.find('option[value!=""]').prop('selected', true);
        $sel.trigger('change');
      })
      .on('click.s2actions','[data-action="clear-all"]', function(e){
        e.preventDefault();
        $sel.val(null).trigger('change');
      });

    // If Select2 re-renders options, our bar can disappear — watch and re-add.
    var mo = new MutationObserver(function(){
      if (!$ul.find('.select2-actions').length) { $ul.data('has-toolbar', false); ensureToolbar($sel); }
    });
    mo.observe($ul[0], { childList:true });
  }

  $('.select2[multiple]').each(function(){
    var $sel = $(this);
    if ($sel.data('select2')) return;

    $sel.select2({
      placeholder: $sel.data('placeholder') || 'Select options',
      allowClear: true,
      width: '100%',
      closeOnSelect: false
    });

    // Add toolbar once dropdown is fully rendered
    $sel.on('select2:open', function(){
      // Wait one tick to let Select2 render results
      setTimeout(function(){ ensureToolbar($sel); }, 0);
    });

    $sel.on('change select2:clear select2:select select2:unselect', function(){
      updateSummary($sel);
    });

    updateSummary($sel);
  });
})(jQuery);
</script>

<script>

// With Placeholder (single-select only; multi-selects are initialized above)
$(".select2").not("[multiple]").each(function () {
    var $sel = $(this);
    if ($sel.data("select2")) return;
    $sel.select2({
        placeholder: $sel.data("placeholder") || "Please Select"
    });
});

// Bootstrap Datepicker init
$('.minMax').datepicker({                                 // D/M/Y
    language: 'en',                                          // force English
    startDate: new Date(), 
    endDate: new Date(), 
    minDate: new Date(new Date().setMonth(new Date().getMonth() - 4)), // Allows past 4 months
    maxDate: new Date(), // Restricts selection to today
    autoclose: true,
    format: 'yy-mm-dd'
});
// Bootstrap Datepicker init
$('.futureDate').datepicker({                                 // D/M/Y
    language: 'en',                                          // force English
    startDate: new Date(), 
    endDate: new Date(new Date().setMonth(new Date().getMonth() + 4)), // Allows past 4 months
    minDate: new Date(), 
    maxDate: new Date(new Date().setMonth(new Date().getMonth() + 4)), // Allows past 4 months
    autoclose: true,
    format: 'yy-mm-dd'
});


$(document).on("click.v2HeaderLanguageChange", ".v2-language-toggle-option, .more_lang .lang", function (event) {
    event.preventDefault(); // Prevent default page reload on click

    let $selectedOption = $(this);
    let selectedLang = String($selectedOption.data("value") || "").toLowerCase();
    const allowedLangs = ["en", "fr"];
    if (!allowedLangs.includes(selectedLang)) {
        Swal.fire({
            title: "Error!",
            text: "Unsupported language.",
            icon: "error"
        });
        return;
    }

    const isMobileLanguageToggle = $selectedOption.hasClass("v2-language-toggle-option")
        && window.matchMedia
        && window.matchMedia("(max-width: 575px)").matches;

    if (isMobileLanguageToggle && ($selectedOption.hasClass("active") || $selectedOption.attr("aria-pressed") === "true")) {
        const $nextOption = $selectedOption.siblings(".v2-language-toggle-option").first();
        if ($nextOption.length) {
            $selectedOption = $nextOption;
            selectedLang = String($selectedOption.data("value") || "").toLowerCase();
        }
    }

    if ($selectedOption.hasClass("active") || $selectedOption.attr("aria-pressed") === "true") {
        return;
    }

    if ($selectedOption.hasClass("v2-language-toggle-option")) {
        const $toggle = $selectedOption.closest(".v2-language-toggle");
        const previousLang = String($toggle.find(".v2-language-toggle-option.active").data("value") || "").toLowerCase();

        $toggle.addClass("is-loading");
        $toggle.find(".v2-language-toggle-option")
            .removeClass("active selected")
            .attr("aria-pressed", "false");
        $selectedOption
            .addClass("active selected")
            .attr("aria-pressed", "true");

        Swal.fire({
            title: "Changing Language...",
            text: "Please wait while we apply your language preference.",
            icon: "info",
            allowOutsideClick: false,
            showConfirmButton: false,
            timerProgressBar: true,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.get(@json(url('lang')) + "/" + encodeURIComponent(selectedLang), function (response, status) {
            if (status === "success") {
                Swal.fire({
                    title: "Success!",
                    text: "Language has been changed.",
                    icon: "success",
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            }
        }).fail(function () {
            $toggle.find(".v2-language-toggle-option")
                .removeClass("active selected")
                .attr("aria-pressed", "false");
            $toggle.find('.v2-language-toggle-option[data-value="' + previousLang + '"]')
                .addClass("active selected")
                .attr("aria-pressed", "true");

            Swal.fire({
                title: "Error!",
                text: "Failed to change language. Please try again.",
                icon: "error"
            });
        }).always(function () {
            $toggle.removeClass("is-loading");
        });
        return;
    }

    // Show loading alert
    Swal.fire({
        title: "Changing Language...",
        text: "Please wait while we apply your language preference.",
        icon: "info",
        allowOutsideClick: false,
        showConfirmButton: false,
        timerProgressBar: true,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Send request to change language
    $.get(@json(url('lang')) + "/" + encodeURIComponent(selectedLang), function (response, status) {
        if (status === "success") {
            Swal.fire({
                title: "Success!",
                text: "Language has been changed.",
                icon: "success",
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                location.reload(); // Reload after success alert
            });
        }
    }).fail(function () {
        Swal.fire({
            title: "Error!",
            text: "Failed to change language. Please try again.",
            icon: "error"
        });
    });
});
$(".sidebar-list").hover(
            function () {
                if ($(this).closest(".sidebar-wrapper[data-v2-sidebar]").length) {
                    return;
                }
                $(this).find(".dynamic-icon").css({
                    "transform": "rotate(10deg) scale(1.2)",
                    "transition": "transform 0.3s ease-in-out"
                });
            },
            function () {
                if ($(this).closest(".sidebar-wrapper[data-v2-sidebar]").length) {
                    return;
                }
                $(this).find(".dynamic-icon").css({
                    "transform": "rotate(0deg) scale(1)"
                });
            }
        );

// V2 sidebar submenu behavior:
// Keep arrows aligned, preserve active child open state, and prevent parent links
// with children from navigating away on click.
(function ($) {
    function setArrow($link, isOpen) {
        var $trailing = $link.children(".v2-sidebar-trailing");
        var $arrows = $trailing.length ? $trailing.children(".according-menu") : $link.children(".according-menu");
        if (!$arrows.length) {
            $arrows = $('<span class="according-menu" aria-hidden="true"></span>');
            if ($trailing.length) {
                $arrows.appendTo($trailing);
            } else {
                $arrows.appendTo($link);
            }
        }

        if ($arrows.length > 1) {
            $arrows.slice(1).remove();
            $arrows = $trailing.length ? $trailing.children(".according-menu").first() : $link.children(".according-menu").first();
        }

        $arrows
            .attr("aria-hidden", "true")
            .html('<i class="fa fa-angle-' + (isOpen ? "down" : "right") + '"></i>');
        $link.attr("aria-expanded", String(isOpen));
    }

    function openSubmenu($link, $submenu, animate) {
        $submenu.stop(true, true)
            .css({ height: "", overflow: "" })
            .data("v2-open", true)
            .attr("data-v2-open", "1")
            .addClass("is-open")
            .show();
        $link.addClass("active");
        setArrow($link, true);
        $link.parent(".sidebar-list").addClass("active");
    }

    function closeSubmenu($link, $submenu, animate) {
        $submenu.stop(true, true)
            .css({ height: "", overflow: "" })
            .data("v2-open", false)
            .removeAttr("data-v2-open")
            .removeClass("is-open")
            .hide();
        $link.removeClass("active");
        setArrow($link, false);
        $link.parent(".sidebar-list").removeClass("active");
    }

    function refreshOpenSubmenus($sidebar) {
        $sidebar.find(".sidebar-submenu").each(function () {
            var $submenu = $(this);
            var $link = $submenu.prev("a.sidebar-title");
            if ($link.attr("aria-expanded") === "true" || $submenu.data("v2-open") === true) {
                openSubmenu($link, $submenu, false);
            }
        });
    }

    function syncSidebarToggleState() {
        var $sidebar = $(".sidebar-wrapper[data-v2-sidebar]");
        if (!$sidebar.length) return;

        if (!$sidebar.hasClass("close_icon") || $(window).width() <= 991) {
            $sidebar.removeClass("is-hover-expanded");
        }

        refreshOpenSubmenus($sidebar);
        $(".toggle-sidebar").attr("aria-expanded", String(!$sidebar.hasClass("close_icon")));
    }

    function disableV2HeaderTooltips() {
        var $controls = $(".page-header[data-v2-header] button, .page-header[data-v2-header] a, .sidebar-wrapper[data-v2-sidebar] .back-btn");
        var tooltipIds = [];

        $controls.each(function () {
            var describedBy = String($(this).attr("aria-describedby") || "");
            if (describedBy) {
                describedBy.split(/\s+/).forEach(function (id) {
                    if (id) {
                        tooltipIds.push(id);
                    }
                });
            }

            try {
                if (window.bootstrap && bootstrap.Tooltip) {
                    var instance = bootstrap.Tooltip.getInstance(this);
                    if (instance) {
                        instance.dispose();
                    }
                }
            } catch (e) {}

            try {
                if (window.jQuery && jQuery.fn.tooltip) {
                    jQuery(this).tooltip("dispose");
                }
            } catch (e) {
                try {
                    if (window.jQuery && jQuery.fn.tooltip) {
                        jQuery(this).tooltip("destroy");
                    }
                } catch (ignored) {}
            }
        });

        $controls
            .attr("data-v2-no-tooltip", "true")
            .removeAttr("title data-bs-original-title data-original-title aria-describedby");

        tooltipIds.forEach(function (id) {
            var element = document.getElementById(id);
            if (element && $(element).hasClass("tooltip")) {
                $(element).remove();
            }
        });
    }

    function syncSidebarOverlay($sidebar) {
        var isMobile = $(window).width() <= 991;
        var isOpen = $sidebar.length && !$sidebar.hasClass("close_icon");
        var $overlay = $(".bg-overlay");

        if (isMobile && isOpen) {
            if (!$overlay.length) {
                $('<div class="bg-overlay active"></div>').appendTo("body");
            } else {
                $overlay.addClass("active");
            }
            return;
        }

        $overlay.remove();
    }

    function setSidebarCollapsed(collapsed) {
        var $sidebar = $(".sidebar-wrapper[data-v2-sidebar]");
        var $header = $(".page-header");
        if (!$sidebar.length) return;

        $sidebar.toggleClass("close_icon", collapsed);
        $header.toggleClass("close_icon", collapsed);

        if (collapsed) {
            $sidebar.removeClass("is-hover-expanded");
        }

        syncSidebarOverlay($sidebar);
        syncSidebarToggleState();
    }

    function closeMobileSidebar() {
        if ($(window).width() > 991) return;

        setSidebarCollapsed(true);
    }

    function syncSidebarHoverPreview() {
        var $sidebar = $(".sidebar-wrapper[data-v2-sidebar]");
        if (!$sidebar.length) return;

        function enterSidebarPreview() {
            var $current = $(this);
            if ($(window).width() > 991 && $current.hasClass("close_icon")) {
                $current.addClass("is-hover-expanded");
            }
        }

        function leaveSidebarPreview() {
            $(this).removeClass("is-hover-expanded");
        }

        $sidebar.off(".v2SidebarPeek")
            .on("mouseenter.v2SidebarPeek mouseover.v2SidebarPeek pointerenter.v2SidebarPeek focusin.v2SidebarPeek", enterSidebarPreview)
            .on("mouseleave.v2SidebarPeek pointerleave.v2SidebarPeek", leaveSidebarPreview)
            .on("focusout.v2SidebarPeek", function (event) {
                if (!this.contains(event.relatedTarget)) {
                    $(this).removeClass("is-hover-expanded");
                }
            });
    }

    function initV2SidebarMenus() {
        var $sidebar = $(".sidebar-wrapper[data-v2-sidebar] .sidebar-main");
        if (!$sidebar.length) return;

        var $titles = $sidebar.find(".sidebar-links .sidebar-list > a.sidebar-title");
        $titles.each(function () {
            var $link = $(this);
            var $submenu = $link.next(".sidebar-submenu");

            // Remove accidental arrow/toggle behavior from non-dropdown items.
            if (!$submenu.length) {
                $link.removeClass("sidebar-title");
                $link.find("> .according-menu, > .v2-sidebar-trailing > .according-menu").remove();
                return;
            }

            setArrow($link, $link.attr("aria-expanded") === "true" || $link.hasClass("active") || $submenu.is(":visible"));
        });

        // Override theme default click handlers for v2 with deterministic state.
        $sidebar.off("click.v2Sidebar", ".sidebar-links .sidebar-list > a.sidebar-title");
        $titles.off("click.v2Sidebar").on("click.v2Sidebar", function (e) {
            var $link = $(this);
            var $submenu = $link.next(".sidebar-submenu");
            if (!$submenu.length) return;

            e.preventDefault();
            e.stopImmediatePropagation();

            var $wrapper = $link.closest(".sidebar-wrapper[data-v2-sidebar]");
            if ($(window).width() > 991 && $wrapper.hasClass("close_icon")) {
                $wrapper.addClass("is-hover-expanded");
            }

            var isOpen = $link.attr("aria-expanded") === "true";
            var $otherSubmenus = $sidebar.find(".sidebar-links .sidebar-submenu").not($submenu);

            $otherSubmenus.each(function () {
                var $s = $(this);
                var $l = $s.prev("a.sidebar-title");
                closeSubmenu($l, $s, false);
            });

            if (isOpen) {
                closeSubmenu($link, $submenu, false);
            } else {
                openSubmenu($link, $submenu, false);
            }
        });

        $sidebar.off("click.v2SidebarClose").on("click.v2SidebarClose", "a.link-nav, .sidebar-submenu a", function () {
            closeMobileSidebar();
        });

        // Respect active server-side/current-route state on initial load.
        $sidebar.find(".sidebar-links .sidebar-submenu").each(function () {
            var $submenu = $(this);
            var $link = $submenu.prev("a.sidebar-title");
            var shouldOpen = $link.hasClass("active") || $submenu.find("a.active").length > 0 || $submenu.parent(".sidebar-list").hasClass("active");
            if (shouldOpen) {
                openSubmenu($link, $submenu, false);
            } else {
                closeSubmenu($link, $submenu, false);
            }
        });
    }

    $(function () {
        disableV2HeaderTooltips();
        initV2SidebarMenus();
        syncSidebarHoverPreview();
        if ($(window).width() <= 991) {
            setSidebarCollapsed(true);
        }
        syncSidebarToggleState();
        $(document).off("click.v2SidebarToggle").on("click.v2SidebarToggle", ".toggle-sidebar", function (event) {
            event.preventDefault();
            disableV2HeaderTooltips();
            var $sidebar = $(".sidebar-wrapper[data-v2-sidebar]");
            setSidebarCollapsed(!$sidebar.hasClass("close_icon"));
        });
        $(document).off("click.v2SidebarBack").on("click.v2SidebarBack", ".sidebar-wrapper[data-v2-sidebar] .back-btn, .bg-overlay", function (event) {
            event.preventDefault();
            disableV2HeaderTooltips();
            setSidebarCollapsed(true);
        });
        $(window).on("resize.v2SidebarToggleState", function () {
            disableV2HeaderTooltips();
            syncSidebarHoverPreview();
            setSidebarCollapsed($(window).width() <= 991);
            syncSidebarToggleState();
        });
    });
})(jQuery);

function AppConfirmDelete(url, title, dialog) {
    try {
        var parsedUrl = new URL(String(url || ''), window.location.origin);
        if (parsedUrl.origin !== window.location.origin || !/^https?:$/.test(parsedUrl.protocol)) {
            Swal.fire("Error!", "Unsafe delete URL blocked.", "error");
            return;
        }
        url = parsedUrl.pathname + parsedUrl.search + parsedUrl.hash;
    } catch (e) {
        Swal.fire("Error!", "Invalid delete URL.", "error");
        return;
    }

    Swal.fire({
        title: title,
        text: dialog,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: url,
                type: 'POST', // Laravel DELETE needs POST with `_method`
                data: {
                    _method: 'DELETE',
                    _token: $('meta[name="csrf-token"]').attr('content') // CSRF Token
                },
                success: function (response) {
                    Swal.fire({
                        title: "Deleted!",
                        text: "The item has been deleted.",
                        icon: "success",
                        confirmButtonColor: "#3085d6"
                    }).then(() => {
                        location.reload(); // Refresh page or update table dynamically
                    });
                },
                error: function () {
                    Swal.fire("Error!", "Something went wrong.", "error");
                }
            });
        }
    });
}
</script>

@if(Route::currentRouteName() == 'index')
<script>
	new WOW().init();
</script>


@endif
