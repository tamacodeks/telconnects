<script>
    (function ($) {
        var busV2Cities = @json($cities);
        var availableCities = $.map(busV2Cities, function (city) {
            return {
                value: city.id || "",
                label: city.name || "",
                latitude: city.coordinates ? city.coordinates.latitude : "",
                longitude: city.coordinates ? city.coordinates.longitude : ""
            };
        });

        function updatePassengerSummary() {
            var adult = parseInt($("#busV2Adult").val(), 10) || 1;
            var child = parseInt($("#busV2Child").val(), 10) || 0;
            var parts = [adult + " adult" + (adult === 1 ? "" : "s")];

            if (child > 0) {
                parts.push(child + " child" + (child === 1 ? "" : "ren"));
            }

            var summary = parts.join(", ");
            $("#busV2Passengers").val(summary);
            $("#busV2PassengersDisplay").val(summary);
            $("#busV2PassengersDisplay").addClass("is-active");
            $(".bus-v2-stepper").each(function () {
                var target = $($(this).find("button").first().data("target"));
                $(this).find("input").val(target.val());
            });
        }

        function setFieldError(id, message) {
            $(id).text(message || "");
        }

        function validateSearchForm() {
            var hasError = false;
            setFieldError("#busV2FromError", "");
            setFieldError("#busV2ToError", "");
            setFieldError("#busV2DateError", "");
            setFieldError("#busV2PassengersError", "");

            if (!$("#busV2From").val().trim() || !$("#busV2FromId").val().trim()) {
                setFieldError("#busV2FromError", "Select a valid origin from suggestions.");
                hasError = true;
            }

            if (!$("#busV2To").val().trim() || !$("#busV2ToId").val().trim()) {
                setFieldError("#busV2ToError", "Select a valid destination from suggestions.");
                hasError = true;
            }

            if (!$("#busV2DepartureDate").val().trim()) {
                setFieldError("#busV2DateError", "Choose a travel date.");
                hasError = true;
            }

            if (!(parseInt($("#busV2Adult").val(), 10) || 0) && !(parseInt($("#busV2Child").val(), 10) || 0)) {
                setFieldError("#busV2PassengersError", "Add at least one passenger.");
                hasError = true;
            }

            return !hasError;
        }

        function wireAutocomplete(inputSelector, hiddenSelector, latSelector, lonSelector) {
            $(inputSelector).autocomplete({
                minLength: 2,
                source: availableCities,
                select: function (event, ui) {
                    $(inputSelector).val(ui.item.label);
                    $(hiddenSelector).val(ui.item.value);
                    $(latSelector).val(ui.item.latitude || "");
                    $(lonSelector).val(ui.item.longitude || "");
                    return false;
                },
                change: function (event, ui) {
                    if (!ui.item && $(this).val().trim() === "") {
                        $(hiddenSelector).val("");
                        $(latSelector).val("");
                        $(lonSelector).val("");
                    }
                }
            });
        }

        $(function () {
            wireAutocomplete("#busV2From", "#busV2FromId", "#busV2GeoLatFrom", "#busV2GeoLonFrom");
            wireAutocomplete("#busV2To", "#busV2ToId", "#busV2GeoLatTo", "#busV2GeoLonTo");

            $("#busV2DepartureDate").datepicker({
                changeYear: true,
                minDate: new Date(),
                maxDate: "+3M",
                dateFormat: "yy-mm-dd",
                showAnim: "fadeIn"
            });

            updatePassengerSummary();

            $(document).on("click", ".js-bus-v2-stepper", function () {
                var $button = $(this);
                var $target = $($button.data("target"));
                var current = parseInt($target.val(), 10) || 0;
                var step = parseInt($button.data("step"), 10) || 0;
                var min = parseInt($button.data("min"), 10);
                var max = parseInt($button.data("max"), 10);
                var next = current + step;

                if (!isNaN(min) && next < min) {
                    next = min;
                }
                if (!isNaN(max) && next > max) {
                    next = max;
                }

                $target.val(next);
                updatePassengerSummary();
            });

            $("#busV2Swap").on("click", function () {
                var from = $("#busV2From").val();
                var fromId = $("#busV2FromId").val();
                var fromLat = $("#busV2GeoLatFrom").val();
                var fromLon = $("#busV2GeoLonFrom").val();

                $("#busV2From").val($("#busV2To").val());
                $("#busV2FromId").val($("#busV2ToId").val());
                $("#busV2GeoLatFrom").val($("#busV2GeoLatTo").val());
                $("#busV2GeoLonFrom").val($("#busV2GeoLonTo").val());

                $("#busV2To").val(from);
                $("#busV2ToId").val(fromId);
                $("#busV2GeoLatTo").val(fromLat);
                $("#busV2GeoLonTo").val(fromLon);
            });

            $("#busV2SearchForm").on("submit", function () {
                if (!validateSearchForm()) {
                    return false;
                }

                $("#busV2SearchButton")
                    .prop("disabled", true)
                    .html('<i class="fas fa-spinner fa-spin"></i> Searching...');
            });

            $(".bus-v2-filter-select").on("change", function () {
                $("#busV2SortForm").submit();
            });

            $(".bus-v2-birthdate").datepicker({
                dateFormat: "dd.mm.yy",
                changeMonth: true,
                changeYear: true,
                yearRange: "1900:{{ date('Y') }}"
            }).each(function () {
                var kind = $(this).data("passenger-kind");
                $(this).datepicker("option", "maxDate", kind === "adult" ? "-14y" : "-5y");
            });

            $(".bus-v2-expirydate").datepicker({
                dateFormat: "dd.mm.yy",
                changeMonth: true,
                changeYear: true,
                minDate: 0
            });

            $(".bus-v2-citizenship").each(function () {
                var $select = $(this);
                var target = "#" + $select.data("target");
                var initialIso3 = $select.find("option:selected").data("iso3");
                if (initialIso3 && !$(target).val()) {
                    $(target).val(initialIso3);
                }
            }).on("change", function () {
                var target = "#" + $(this).data("target");
                $(target).val($(this).find("option:selected").data("iso3") || "");
            });

            $(".bus-v2-checkout-form").on("submit", function () {
                $(this).find(".bus-v2-submit")
                    .prop("disabled", true)
                    .html('<i class="fas fa-spinner fa-spin"></i> Processing...');
            });
        });
    })(jQuery);
</script>
