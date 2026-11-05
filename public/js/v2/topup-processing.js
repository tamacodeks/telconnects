(function () {
    'use strict';

    var form = document.getElementById('topupProcessingForm');
    var selectAll = document.getElementById('topupSelectAll');
    var counter = document.getElementById('topupSelectedCount');

    if (!form || !selectAll || !counter) {
        return;
    }

    function boxes() {
        return Array.prototype.slice.call(form.querySelectorAll('.topup-user-check'));
    }

    function refresh() {
        var items = boxes();
        var selected = items.filter(function (item) { return item.checked; }).length;
        counter.textContent = selected;
        selectAll.checked = items.length > 0 && selected === items.length;
        selectAll.indeterminate = selected > 0 && selected < items.length;
    }

    selectAll.addEventListener('change', function () {
        boxes().forEach(function (item) { item.checked = selectAll.checked; });
        refresh();
    });

    form.addEventListener('change', function (event) {
        if (event.target.classList.contains('topup-user-check')) {
            refresh();
        }
    });

    form.addEventListener('submit', function (event) {
        if (!boxes().some(function (item) { return item.checked; })) {
            event.preventDefault();
            window.alert((window.topupProcessingText || {}).selectUser || 'Select at least one user.');
        }
    });

    refresh();
}());
