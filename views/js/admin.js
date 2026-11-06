document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    var searchInput = document.getElementById('rd-search');
    var noResults = document.getElementById('rd-no-results');
    var items = Array.prototype.slice.call(document.querySelectorAll('[data-rd-item]'));

    if (!searchInput || !noResults) {
        return;
    }

    searchInput.addEventListener('input', function () {
        var term = searchInput.value.trim().toLocaleLowerCase('fr');
        var visible = 0;

        items.forEach(function (item) {
            var matches = item.textContent.toLocaleLowerCase('fr').indexOf(term) !== -1;
            item.hidden = !matches;
            visible += matches ? 1 : 0;
        });

        noResults.hidden = visible > 0;
    });
});
