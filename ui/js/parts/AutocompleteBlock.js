import {handleAjaxError} from './utils/ErrorHandler.js?v=2';

export class AutocompleteBlock {

    constructor() {
        /**
        const onTypeLinkClick = this.onTypeLinkClick.bind(this);
        this.queryTypeLinks.forEach(link => link.addEventListener('click', onTypeLinkClick, false));
        **/

        const token = document.head.querySelector('[name=\'csrf-token\'][content]').content;

        const url = `/admin/search?token=${token}`;
        $('#auto-complete').autocomplete({
            serviceUrl: url,
            deferRequestBy: 300,
            minChars: 3,
            groupBy: 'category',
            showNoSuggestionNotice: true,
            noSuggestionNotice: 'Sorry, no matching results',

            onSelect: function(suggestion) {
                const url = `/${suggestion.entityId}/${suggestion.id}`;
                window.open(url, '_self');
            },

            onSearchStart: function(params) {
                params.query = params.query.trim();
            },
            onSearchError: handleAjaxError,
        });
    }

    onTypeLinkClick(e) {
        e.preventDefault();

        this.queryTypeLinks.forEach(link => link.classList.remove('active'));
        e.target.classList.add('active');

        return false;
    }

    getActiveQueryTypeItem() {
        const activeLink = this.queryTypeControl.querySelector('a.active');
        const activeType = activeLink.dataset.value;

        return activeType;
    }

    get queryTypeLinks() {
        return this.queryTypeControl.querySelectorAll('A');
    }

    get queryTypeControl() {
        return document.querySelector('nav.filtersForm.search');
    }
}
