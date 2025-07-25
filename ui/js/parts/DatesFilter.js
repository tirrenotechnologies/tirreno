import {fireEvent} from './utils/Event.js?v=2';
import {
    formatStringTime,
    addDays,
    addHours,
} from './utils/Date.js?v=2';
import {debounce} from './utils/Functions.js?v=2';
import {DAYS_IN_RANGE} from './utils/Constants.js?v=2';

export class DatesFilter {
    constructor() {
        this.setupXhrPool();
        this.offset = (this.offsetField) ? parseInt(this.offsetField.value, 10) : 0;
        this.ajaxCount = 0;
        if (this.isDateFilterUnavailable) {
            return true;
        }

        if (this.dateToLocalField !== null && this.dateFromLocalField !== null) {
            // visible fields change should set invisible fields
            this.onTimestampFieldChange = this.onTimestampFieldChange.bind(this);
            const debouncedOnTimestampFieldChange = debounce(this.onTimestampFieldChange);

            this.dateToLocalField.addEventListener('change', debouncedOnTimestampFieldChange, false);
            this.dateFromLocalField.addEventListener('change', debouncedOnTimestampFieldChange, false);

            this.setDefaultLocalDates();
            this.setDefaultDates();
        } else if (!this.dateFromField.value && !this.dateToField.value) {
            this.setDefaultDates();
        }

        const onDateFilterChange = this.onDateFilterChange.bind(this);

        this.dateToField.addEventListener('change', onDateFilterChange, false);
        this.dateFromField.addEventListener('change', onDateFilterChange, false);

        const onIntervalLinkClick = this.onIntervalLinkClick.bind(this);
        this.intervalLinks.forEach(item => item.addEventListener('click', onIntervalLinkClick, false));

        const onDateFilterChangedCaught = this.onDateFilterChangedCaught.bind(this);
        window.addEventListener('dateFilterChangedCaught', onDateFilterChangedCaught, false);

        const onDateFilterChangedCompleted = this.onDateFilterChangedCompleted.bind(this);
        window.addEventListener('dateFilterChangedCompleted', onDateFilterChangedCompleted, false);
    }

    setupXhrPool() {
        // https://gist.github.com/msankhala/3fa2844c1fbad1f4c0185a8e3ef09aed
        // Stop all ajax request by http://tjrus.com/blog/stop-all-active-ajax-requests
        $.xhrPool = []; // array of uncompleted requests
        $.xhrPool.abortAll = function() { // our abort function
            $(this).each(function(idx, jqXHR) {
                jqXHR.abort();
            });
            $.xhrPool.length = 0;
        };

        $.ajaxSetup({
            beforeSend: function(jqXHR) { // before jQuery send the request we will push it to our array
                $.xhrPool.push(jqXHR);
            },
            complete: function(jqXHR) { // when some of the requests completed it will splice from the array
                const index = $.xhrPool.indexOf(jqXHR);
                if (index > -1) {
                    $.xhrPool.splice(index, 1);
                }
            }
        });
    }

    setDefaultDates() {
        this.setDateRangeFromNow(DAYS_IN_RANGE * 24);
    }

    setDefaultLocalDates() {
        let dateTo = new Date();
        dateTo = new Date(dateTo.getTime() + (dateTo.getTimezoneOffset() * 60 + this.offset) * 1000); // now time in op tz

        const dateFrom = addDays(dateTo, -DAYS_IN_RANGE); // dateFrom in op tz
        dateFrom.setHours(24, 0, 0, 0);

        this.dateToLocalField.value   = formatStringTime(dateTo);
        this.dateFromLocalField.value = formatStringTime(dateFrom);
    }

    onTimestampFieldChange(e) {
        // get value (with offset)
        // set normal input exluding offset
        e.preventDefault();

        const input = e.target;

        $.xhrPool.abortAll();

        const value = input.value;
        const name  = input.name;

        let target = null;

        if (name === 'date_from_local') {
            target = this.dateFromField;
        } else if (name === 'date_to_local') {
            target = this.dateToField;
        }

        let dt = new Date(value);
        dt = new Date(dt.getTime() - this.offset * 1000); // shift fom op tz to utc

        target.value = formatStringTime(dt);

        this.onDateFilterChange(e);
        return false;
    }

    onDateFilterChange() {
        $.xhrPool.abortAll();
        fireEvent('dateFilterChanged');
    }

    getValue() {
        const data = {
            dateTo: null,
            dateFrom: null
        };

        if (this.isDateFilterUnavailable) {
            return data;
        }

        data['dateTo']   = this.dateToField.value;
        data['dateFrom'] = this.dateFromField.value;

        /*const rangeWasChanged = (1 == this.dateFromField.dataset.changed) || (1 == this.dateToField.dataset.changed);
        if (rangeWasChanged) {
            data['keepDates'] = 1;
        }*/

        return data;
    }

    onIntervalLinkClick(e) {
        e.preventDefault();

        const link = e.target;
        if (link.classList.contains('active') || link.classList.contains('blocked')) {
            return false;
        }

        this.updateDisabled(true);

        $.xhrPool.abortAll();

        const value = parseInt(link.dataset.value, 10) || 0;

        if (value === 0) {
            this.clearDateRange();
        } else {
            this.setDateRangeFromNow(value);
        }

        this.intervalLinks.forEach(item => item.classList.remove('active'));
        link.classList.add('active');

        this.onDateFilterChange();

        return false;
    }

    updateDisabled(disabled) {
        this.intervalLinks.forEach(item => {
            if (disabled) {
                if (!item.classList.contains('active')) {
                    item.setAttribute('tabindex', '-1');
                    item.classList.add('blocked');
                    item.removeAttribute('href');
                }
            } else {
                item.setAttribute('tabindex', '0');
                item.classList.remove('blocked');
                item.setAttribute('href', 'javascript:void(0);');
            }
        });
    }

    // with op tz and utc shift for calculation request
    setDateRangeFromNow(hoursDiff) {
        let dateTo = new Date();
        dateTo = new Date(dateTo.getTime() + (dateTo.getTimezoneOffset() * 60 + this.offset) * 1000); // now time in op tz
        let dateFrom = addHours(dateTo, -hoursDiff); // dateFrom in op tz
        // floor to not miss data in group
        if (hoursDiff < 24 && hoursDiff > -24) {
            dateFrom.setMinutes(0, 0, 0);
        } else {
            dateFrom.setHours(24, 0, 0, 0);
        }

        dateTo = new Date(dateTo.getTime() - (this.offset * 1000)); // dateTo at utc
        dateFrom = new Date(dateFrom.getTime() - (this.offset * 1000)); // dateFrom at utc

        this.dateToField.value = formatStringTime(dateTo);
        this.dateFromField.value = formatStringTime(dateFrom);
    }

    clearDateRange() {
        this.dateToField.value   = null;
        this.dateFromField.value = null;
    }

    onDateFilterChangedCaught() {
        this.ajaxCount++;
    }

    onDateFilterChangedCompleted() {
        this.ajaxCount--;
        if (this.ajaxCount <= 0) {
            this.updateDisabled(false);
        }
    }

    get isDateFilterUnavailable() {
        return this.dateFromField === null || this.dateToField === null;
    }

    get intervalLinks() {
        return this.navbar.querySelectorAll('a');
    }

    get navbar() {
        return document.querySelector('nav.filtersForm.daterange');
    }

    get offsetField() {
        return document.querySelector('input[name="offset"]');
    }

    get dateToField() {
        return document.querySelector('input[name="date_to"]');
    }

    get dateFromField() {
        return document.querySelector('input[name="date_from"]');
    }

    get dateToLocalField() {
        return document.querySelector('input[name="date_to_local"]');
    }

    get dateFromLocalField() {
        return document.querySelector('input[name="date_from_local"]');
    }
}
