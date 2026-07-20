/* global $ */
(function () {
    var baseUrl = window.ccv2BaseUrl || "";
    var state = {
        providerId: null,
        cardId: null,
        cardInfo: null,
        selectedCard: null
    };
    var currencySymbols = {
        EUR: "\u20ac",
        USD: "$",
        GBP: "\u00a3"
    };
    var messageFallbacks = {
        "myservice.unable_to_print": "Unable to print this card right now.",
        "myservice.no_card_found": "No card stock available for this selection.",
        "myservice.err_no_balance": "Not enough balance to print this card.",
        "myservice.contact_admin": "Please contact an administrator for this card.",
        "common.access_violation": "You do not have access to print this card.",
        "common.service_not_avail": "This service is not available right now.",
        "common.parent_rule_failed": "Your account limit does not allow this print."
    };

    function getCode(resp) {
        if (!resp || !resp.data) {
            return 0;
        }
        return parseInt(resp.data.code, 10);
    }

    function firstMessageValue(value) {
        var key;
        var found;

        if (value === null || typeof value === "undefined" || value === "") {
            return "";
        }

        if ($.isArray(value)) {
            for (var i = 0; i < value.length; i += 1) {
                found = firstMessageValue(value[i]);
                if (found) {
                    return found;
                }
            }
            return "";
        }

        if (typeof value === "object") {
            if (value.message) {
                found = firstMessageValue(value.message);
                if (found) {
                    return found;
                }
            }

            for (key in value) {
                if (Object.prototype.hasOwnProperty.call(value, key)) {
                    found = firstMessageValue(value[key]);
                    if (found) {
                        return found;
                    }
                }
            }
            return "";
        }

        return $.trim(String(value));
    }

    function cleanMessage(message, fallback) {
        var text = firstMessageValue(message) || fallback || "";

        return messageFallbacks[text] || text;
    }

    function showNotice(message, state) {
        var text = cleanMessage(message, "");

        $("#ccv2NoticeText").text(text);
        $("#ccv2Notice")
            .removeClass("is-warning is-error")
            .addClass(state === "error" ? "is-error" : "is-warning")
            .toggleClass("hide", !text);
    }

    function responseMessage(resp, fallback) {
        return cleanMessage(resp && resp.data && resp.data.message, fallback);
    }

    function setActionHelp(message, state) {
        var text = cleanMessage(message, "");

        $("#ccv2ActionHelp")
            .removeClass("is-muted is-warning is-error is-success")
            .addClass(state ? "is-" + state : "is-muted")
            .toggleClass("hide", !text)
            .text(text);
    }

    function setPrintButton(enabled, title) {
        $("#ccv2PrintBtn")
            .prop("disabled", !enabled)
            .attr("title", title || "");
    }

    function setPrintUnavailable(message, state) {
        setPrintButton(false, message);
        setActionHelp(message, state || "muted");
    }

    function normalizeItems(items) {
        return $.isArray(items) ? items : [];
    }

    function normalizeAmount(value) {
        if (value === null || typeof value === "undefined" || value === "") {
            return null;
        }

        var cleaned = String(value).replace(/[^\d,.-]/g, "");
        var hasComma = cleaned.indexOf(",") !== -1;
        var hasDot = cleaned.indexOf(".") !== -1;

        if (hasComma && hasDot) {
            cleaned = cleaned.lastIndexOf(",") > cleaned.lastIndexOf(".")
                ? cleaned.replace(/\./g, "").replace(",", ".")
                : cleaned.replace(/,/g, "");
        } else if (hasComma) {
            cleaned = cleaned.replace(",", ".");
        }

        var amount = parseFloat(cleaned);

        return isFinite(amount) ? amount : null;
    }

    function formatCurrency(value, currency) {
        var amount = normalizeAmount(value);
        if (amount === null) {
            return "";
        }

        var code = String(currency || "EUR").toUpperCase();
        var symbol = currencySymbols[code] || code + " ";

        return symbol + amount.toFixed(2);
    }

    function hasRealImage(item) {
        return !!(item && item.image && (item.has_image === true || item.has_image === 1 || item.has_image === "1"));
    }

    function getInitials(name) {
        var words = $.trim(name || "")
            .replace(/[^a-zA-Z0-9 ]/g, " ")
            .split(/\s+/)
            .filter(Boolean);
        var initials = $.map(words.slice(0, 2), function (word) {
            return word.charAt(0);
        }).join("");

        return (initials || "CC").toUpperCase();
    }

    function createFallback(name, extraClass) {
        return $("<span />", {
            "class": "ccv2-media-fallback " + (extraClass || ""),
            "aria-hidden": "true"
        }).text(getInitials(name));
    }

    function createItemMedia(item, fallbackClass) {
        if (!hasRealImage(item)) {
            return createFallback(item && item.name, fallbackClass);
        }

        return $("<img />")
            .attr("src", item.image)
            .attr("alt", "")
            .on("error.ccv2", function () {
                $(this).replaceWith(createFallback(item.name, fallbackClass));
            });
    }

    function createSelectedBadge(label) {
        return $("<span />", {
            "class": "ccv2-selected-badge",
            "aria-label": (label || "Item") + " selected",
            "title": (label || "Item") + " selected"
        }).append($("<i />", { "class": "fa fa-check ccv2-selected-check", "aria-hidden": "true" }));
    }

    function createStockBadge(item) {
        var status = item.stock_status || "unknown";
        var label = item.stock_label || "";
        if (!label) {
            return $();
        }

        var title = label;
        if (status === "available" && item.stock_count) {
            title += " (" + item.stock_count + " in stock)";
        }

        return $("<div />", {
            "class": "ccv2-stock ccv2-stock--" + status,
            "title": title
        })
            .append($("<span />", { "class": "ccv2-stock-dot", "aria-hidden": "true" }))
            .append($("<span />").text(label));
    }

    function updatePreviewMedia(card) {
        var $image = $("#ccv2CardImg");
        var $fallback = $("#ccv2CardFallback");

        if (!hasRealImage(card)) {
            $image.addClass("hide").removeAttr("src").attr("alt", "");
            $fallback.text(getInitials(card && card.name)).removeClass("hide");
            return;
        }

        $fallback.addClass("hide");
        $image
            .removeClass("hide")
            .off("error.ccv2")
            .on("error.ccv2", function () {
                $image.addClass("hide").removeAttr("src").attr("alt", "");
                $fallback.text(getInitials(card.name)).removeClass("hide");
            })
            .attr("src", card.image)
            .attr("alt", "");
    }

    function updatePreviewCard(card) {
        card = card || {};

        updatePreviewMedia(card);
        $("#ccv2CardName").text(card.name || "Select a card");
        $("#ccv2CardDesc").text(card.description || "Card details will appear here.");
        $("#ccv2CardAccess").text(card.access_number || "-");
        $("#ccv2CardValidity").text(card.validity || "-");
        $("#ccv2CardComment1").text(card.comment_1 || "");
        $("#ccv2CardComment2").text(card.comment_2 || "");
    }

    function setPrintFields(mode, values) {
        values = values || {};

        var pinText = values.pin || "Select a card";
        if (mode === "checking") {
            pinText = values.pin || "Checking stock...";
        } else if (mode === "ready") {
            pinText = values.pin || "Ready to print";
        } else if (mode === "failure") {
            pinText = values.pin || "No printable PIN available";
        }

        $("#ccv2PinBlock")
            .removeClass("is-empty is-checking is-ready is-failure is-printed")
            .addClass("is-" + mode);

        $("#ccv2CardPin").text(pinText);
        $(".ccv2-print-meta").toggleClass("hide", mode !== "printed");
        $("#ccv2CardSerial").text(mode === "printed" ? (values.serial || "-") : "-");
        $("#ccv2CardDate").text(mode === "printed" ? (values.date || "-") : "-");
    }

    function showSingleWarning(message, severity) {
        showNotice(message, severity || "warning");
        setActionHelp("", "");
    }

    function setUnavailablePreview(message, retryAllowed) {
        var card = state.cardInfo && state.cardInfo.card ? state.cardInfo.card : state.selectedCard;

        updatePreviewCard(card || { name: "Calling Cards", has_image: false });
        setPrintFields("failure");
        $("#ccv2ReprintBtn").addClass("hide");
        setPrintButton(!!retryAllowed, retryAllowed ? "Try printing this card again." : message);
        showSingleWarning(message + " Select another card or contact an administrator if stock should be available.", "warning");
    }

    function printFrame() {
        var contents = $("#ccv2PrintableCard").html();
        var ticketCss = [
            "@page{margin:6mm;}",
            "body{margin:0;background:#fff;color:#111;font-family:Arial,sans-serif;font-size:12px;line-height:1.35;}",
            ".hide{display:none!important;}",
            ".ccv2-printable-card{width:76mm;max-width:100%;margin:0 auto;color:#111;}",
            ".ccv2-card-header{text-align:center;padding-bottom:6px;}",
            ".ccv2-card-header img{display:block;max-width:120px;max-height:70px;width:auto;height:auto;margin:0 auto 6px;object-fit:contain;}",
            ".ccv2-card-header img.hide,.ccv2-item img:not([src]),.ccv2-card-header img:not([src]){display:none!important;}",
            ".ccv2-media-fallback{display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;margin:0 auto 6px;border:1px solid #ddd;border-radius:8px;background:#f4f6f8;color:#444;font-weight:700;}",
            ".ccv2-card-title{margin:6px 0 3px;font-size:18px;line-height:1.15;font-weight:700;text-align:center;}",
            ".ccv2-card-desc{margin:0;padding:6px 0;border-top:1px dashed #333;color:#111;font-size:12px;text-align:center;}",
            ".ccv2-meta{display:flex;justify-content:space-between;gap:10px;padding:5px 0;border-top:1px dashed #333;font-size:12px;}",
            ".ccv2-meta span:last-child{text-align:right;font-weight:700;}",
            ".ccv2-pin-block{padding:7px 0;border-top:1px dashed #333;text-align:center;}",
            ".ccv2-pin-label{font-size:12px;font-weight:700;text-transform:uppercase;}",
            ".ccv2-pin{margin-top:3px;color:#0049cc;font-size:22px;line-height:1.1;font-weight:800;letter-spacing:.02em;word-break:break-word;}",
            ".ccv2-print-meta{display:flex;}",
            ".ccv2-ticket-logo{display:flex;justify-content:center;margin-top:14px;padding-top:10px;border-top:1px dashed #333;}",
            ".ccv2-ticket-logo img{display:block;width:180px;max-width:82%;height:auto;object-fit:contain;}"
        ].join("");
        var frame = $("<iframe />");
        frame[0].name = "ccv2Frame";
        frame.css({ position: "absolute", top: "-100000px" });
        $("body").append(frame);
        var frameDoc = frame[0].contentWindow
            ? frame[0].contentWindow
            : frame[0].contentDocument.document
                ? frame[0].contentDocument.document
                : frame[0].contentDocument;
        frameDoc.document.open();
        frameDoc.document.write("<html><head><title>Calling Cards</title><style>" + ticketCss + "</style></head><body>");
        frameDoc.document.write('<div class="ccv2-printable-card">' + contents + "</div>");
        frameDoc.document.write("</body></html>");
        frameDoc.document.close();
        setTimeout(function () {
            window.frames["ccv2Frame"].focus();
            window.frames["ccv2Frame"].print();
            frame.remove();
        }, 400);
    }

    function renderProviders(items) {
        items = normalizeItems(items);
        if (!items.length) {
            $("#ccv2Providers").html('<div class="ccv2-empty">No providers available.</div>');
            return;
        }

        var nodes = $.map(items, function (item) {
            return $("<div />", { "class": "ccv2-item ccv2-animate" })
                .attr("data-id", item.id || "")
                .attr("role", "button")
                .attr("tabindex", "0")
                .attr("aria-pressed", "false")
                .append(createSelectedBadge("Provider"))
                .append(createItemMedia(item, "ccv2-media-fallback--item"))
                .append($("<div />", { "class": "ccv2-item-name" }).text(item.name || "Unnamed provider"))[0];
        });
        $("#ccv2Providers").empty().append(nodes);
    }

    function renderCards(items) {
        items = normalizeItems(items);
        if (!items.length) {
            $("#ccv2Cards").html('<div class="ccv2-empty">No cards available for this provider.</div>');
            return;
        }

        var nodes = $.map(items, function (item) {
            var price = formatCurrency(item.face_value, item.currency);

            var $item = $("<div />", { "class": "ccv2-item ccv2-animate" })
                .attr("data-id", item.id || "")
                .attr("data-is-card", item.is_card || 0)
                .attr("data-stock-status", item.stock_status || "")
                .attr("role", "button")
                .attr("tabindex", "0")
                .attr("aria-pressed", "false")
                .data("ccv2Item", item)
                .append(createSelectedBadge("Card"))
                .append(createItemMedia(item, "ccv2-media-fallback--item"))
                .append($("<div />", { "class": "ccv2-item-name" }).text(item.name || "Unnamed card"))
                .append($("<div />", { "class": "ccv2-item-price" }).text(price))
                .append(createStockBadge(item));

            return $item[0];
        });
        $("#ccv2Cards").empty().append(nodes);
    }

    function updatePreview(cardInfo) {
        var card = cardInfo.card || {};
        updatePreviewCard(card);
        setPrintFields("ready");
        setPrintButton(true, "Print this card.");
        setActionHelp("Card stock is available. Click Print to complete and print the PIN.", "success");
        $("#ccv2ReprintBtn").addClass("hide");
    }

    function resetPreview(helpMessage) {
        updatePreviewCard({ name: "Calling Cards", has_image: false });
        setPrintFields("empty");
        setPrintUnavailable(helpMessage || "Select a provider and card to enable printing.", "muted");
        $("#ccv2ReprintBtn").addClass("hide");
        state.cardInfo = null;
        state.cardId = null;
        state.selectedCard = null;
        showNotice("");
    }

    function fetchProviders() {
        $.getJSON(baseUrl + "/calling-cards-v2/data/providers")
            .done(function (resp) {
                if (getCode(resp) !== 200) {
                    showNotice(responseMessage(resp, "Unable to load providers."), "error");
                    setActionHelp("Provider list did not load. Refresh the page or try again.", "error");
                    return;
                }
                renderProviders(resp.data.result);
                showNotice("");
                setActionHelp("Select a provider, then choose a card to enable printing.", "muted");
            })
            .fail(function () {
                showNotice("Unable to load providers.", "error");
                setActionHelp("Provider list did not load. Refresh the page or try again.", "error");
            });
    }

    function fetchCards(providerId) {
        resetPreview("Select a card from this provider to enable printing.");
        $.getJSON(baseUrl + "/calling-cards-v2/data/cards/" + providerId)
            .done(function (resp) {
                if (getCode(resp) !== 200) {
                    showNotice(responseMessage(resp, "Unable to load cards."), "error");
                    setActionHelp("Cards did not load for this provider. Select the provider again or refresh.", "error");
                    return;
                }
                renderCards(resp.data.result.cards || []);
                showNotice("");
                setActionHelp("Select a card from this provider to check stock.", "muted");
            })
            .fail(function () {
                showNotice("Unable to load cards.", "error");
                setActionHelp("Cards did not load for this provider. Select the provider again or refresh.", "error");
            });
    }

    function fetchCardInfo(cardId) {
        $.ajax({
            url: baseUrl + "/calling-cards-v2/data/card-info",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            data: { card_id: cardId }
        })
            .done(function (resp) {
                if (getCode(resp) !== 200) {
                    var message = responseMessage(resp, "Unable to fetch card.");
                    state.cardInfo = null;
                    setUnavailablePreview(message, false);
                    return;
                }
                state.cardInfo = resp.data.result;
                updatePreview(state.cardInfo);
                showNotice("");
            })
            .fail(function () {
                state.cardInfo = null;
                setUnavailablePreview("Unable to check stock for this card.", false);
            });
    }

    function doPrint() {
        if (!state.cardInfo) {
            setPrintUnavailable("Select a card before printing.", "warning");
            return;
        }
        var service = state.cardInfo.service;
        var payload = {};
        var url = "";
        if (service === "mycard") {
            url = baseUrl + "/print_mycallingcard";
            payload = {
                pin_id: state.cardInfo.print.pin_id,
                telecom_provider_id: state.cardInfo.print.telecom_provider_id,
                face_value: state.cardInfo.print.face_value
            };
        } else if (service === "bimedia") {
            url = baseUrl + "/print_callingcard";
            payload = {
                cc_id: state.cardInfo.card.cc_id,
                ccp_id: state.cardInfo.card.ccp_id,
                bimedia_service: "true"
            };
        } else if (service === "aleda") {
            url = baseUrl + "/calling-cards/print/aleda";
            payload = {
                cc_id: state.cardInfo.card.cc_id,
                ccp_id: state.cardInfo.card.ccp_id,
                aleda_service: "true"
            };
        } else {
            url = baseUrl + "/print_card_activated";
            payload = {
                cc_id: state.cardInfo.card.cc_id,
                ccp_id: state.cardInfo.card.ccp_id
            };
        }

        setPrintButton(false, "Printing in progress.");
        setActionHelp("Printing in progress. Keep this page open until the PIN appears.", "muted");

        $.ajax({
            url: url,
            type: "POST",
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            data: payload
        })
            .done(function (resp) {
                if (getCode(resp) !== 200) {
                    var message = responseMessage(resp, "Unable to print.");
                    setPrintFields("failure");
                    $("#ccv2ReprintBtn").addClass("hide");
                    showSingleWarning(message + " Try again, or select another card if the problem continues.", "error");
                    setPrintButton(true, "Try printing this card again.");
                    return;
                }
                var result = resp.data.result || {};
                setPrintFields("printed", {
                    pin: result.pin || "-",
                    serial: result.serial || "-",
                    date: result.time_printed || "-"
                });
                setPrintButton(false, "PIN already printed. Use Print Again to reprint the receipt.");
                $("#ccv2ReprintBtn").removeClass("hide");
                setActionHelp("PIN printed. Use Print Again only if you need another receipt copy.", "success");
                showNotice("");
                printFrame();
            })
            .fail(function () {
                setPrintFields("failure");
                $("#ccv2ReprintBtn").addClass("hide");
                showSingleWarning("Print failed. Check the connection and try again, or choose another card.", "error");
                setPrintButton(true, "Try printing this card again.");
            });
    }

    $(document).on("click", "#ccv2Providers .ccv2-item", function () {
        $("#ccv2Providers .ccv2-item").removeClass("is-active").attr("aria-pressed", "false");
        $(this).addClass("is-active").attr("aria-pressed", "true");
        state.providerId = $(this).data("id");
        state.selectedCard = null;
        fetchCards(state.providerId);
        $("#ccv2Cards").html('<div class="ccv2-empty">Loading cards...</div>');
    });

    $(document).on("click", "#ccv2Cards .ccv2-item", function () {
        $("#ccv2Cards .ccv2-item").removeClass("is-active").attr("aria-pressed", "false");
        $(this).addClass("is-active").attr("aria-pressed", "true");
        state.cardId = $(this).data("id");
        state.selectedCard = $(this).data("ccv2Item") || null;
        updatePreviewCard(state.selectedCard);
        setPrintFields("checking");
        setPrintUnavailable("Checking card stock...", "muted");
        showNotice("");
        fetchCardInfo(state.cardId);
    });

    $(document).on("keydown", ".ccv2-item", function (event) {
        if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            $(this).trigger("click");
        }
    });

    $("#ccv2PrintBtn").on("click", function () {
        doPrint();
    });

    $("#ccv2ReprintBtn").on("click", function () {
        printFrame();
    });

    fetchProviders();
})();
