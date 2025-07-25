import {handleAjaxError} from './utils/ErrorHandler.js?v=2';
import {padZero} from './utils/Date.js?v=2';

export class SearchLine {
    constructor() {
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
                window.open(`/${suggestion.entityId}/${suggestion.id}`, '_self');
            },

            onSearchStart: function(params) {
                params.query = params.query.trim();
            },
            onSearchError: handleAjaxError,
        });

        setInterval(this.updateTime.bind(this), 1000);
    }

    updateTime() {
        let [time, tz] = this.timeInput.placeholder.split(' ');
        let [h, m, s] = time.split(':').map(x => parseInt(x, 10));
        let d = this.dayInput.placeholder;

        s += 1;
        if (s >= 60) {
            s = 0;
            m += 1;
        }

        if (m >= 60) {
            m = 0;
            h += 1;
        }

        if (h >= 24) {
            h = 0;
            d = parseInt(d, 10) + 1;

            if (d >= 366) {
                const now = new Date();
                const year = now.getFullYear() - (now.getMonth() === 0 ? 1 : 0);
                const isLeap = (year % 4 === 0 && year % 100 !== 0) || (year % 400 === 0);

                if (d > 366 || (d === 366 && !isLeap)) {
                    d = 0;
                }
            }

            this.dayInput.placeholder = (d < 10 ? '00' : (d < 100 ? '0' : '')) + d.toString();
        }

        h = padZero(h);
        m = padZero(m);
        s = padZero(s);

        this.timeInput.placeholder = `${h}:${m}:${s} ${tz}`;
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

    get timeInput() {
        return document.getElementById('clock-time');
    }

    get dayInput() {
        return document.getElementById('clock-day');
    }

    get queryTypeLinks() {
        return this.queryTypeControl.querySelectorAll('A');
    }

    get queryTypeControl() {
        return document.querySelector('nav.filtersForm.search');
    }
}
