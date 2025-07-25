import {BaseTiles} from './BaseTiles.js?v=2';
import {
    renderDate,
    renderUserCounter,
    renderReputation,
    renderUserId,
    renderUserFirstname,
    renderUserLastname,
    renderUserReviewedStatus,
} from '../DataRenderers.js?v=2';

const URL   = '/admin/loadUserDetails';

export class UserTiles extends BaseTiles {
    updateTiles(data) {
        this.updateIdDetails(data);
        this.updateDayDetails(data);
        this.updateWeekDetails(data);
        this.updateMonthDetails(data);
    }

    updateIdDetails(data) {
        const tile = document.querySelector('#user-id-tile');

        if (!tile) {
            return;
        }

        const record = data.userDetails;
        this.removeLoaderBackground(tile);

        tile.querySelector('#signup-date').replaceChildren(renderDate(record.created));
        tile.querySelector('#lastseen').replaceChildren(renderDate(record.lastseen));
        tile.querySelector('#latest-decision').replaceChildren(renderDate(record.latest_decision));
        tile.querySelector('#review-status').replaceChildren(renderUserReviewedStatus(record));
        tile.querySelector('#firstname').replaceChildren(renderUserFirstname(record));
        tile.querySelector('#lastname').replaceChildren(renderUserLastname(record));
        tile.querySelector('#userid').replaceChildren(renderUserId(record.userid));
    }

    updateDayDetails(data) {
        const limits = {
            median_event_cnt:   20,
            login_cnt:          3,
            session_cnt:        5,
        };

        this.updateDateRangeDetails(data.dayDetails, '#day-behaviour-tile', limits);
    }

    updateWeekDetails(data) {
        const limits = {
            median_event_cnt:   20,
            login_cnt:          10,
            session_cnt:        25,
        };

        this.updateDateRangeDetails(data.weekDetails, '#week-behaviour-tile', limits);
    }

    updateMonthDetails(data) {
        const limits = {
            median_event_cnt:   20,
            login_cnt:          40,
            session_cnt:        100,
        };

        this.updateDateRangeDetails(data.monthDetails, '#month-behaviour-tile', limits);
    }

    updateDateRangeDetails(record, tileId, limits) {
        const tile = document.querySelector(tileId);

        if (!tile) {
            return;
        }

        const span = document.createElement('span');
        span.className = 'nolight';
        span.textContent = 'N/A';

        this.removeLoaderBackground(tile);

        if (record.session_cnt === 0) {
            tile.querySelector('#failed-login-count').replaceChildren(span.cloneNode(true));
            tile.querySelector('#password-reset-count').replaceChildren(span.cloneNode(true));
            tile.querySelector('#auth-error-count').replaceChildren(span.cloneNode(true));
            tile.querySelector('#off-hours-login-count').replaceChildren(span.cloneNode(true));
            tile.querySelector('#median-event-count').replaceChildren(span.cloneNode(true));
            tile.querySelector('#login-count').replaceChildren(span.cloneNode(true));
            tile.querySelector('#session-count').replaceChildren(span.cloneNode(true));
        } else {
            tile.querySelector('#failed-login-count').replaceChildren(renderUserCounter(record.failed_login_cnt, limits.failed_login_cnt || 1));
            tile.querySelector('#password-reset-count').replaceChildren(renderUserCounter(record.password_reset_cnt, limits.password_reset_cnt || 1));
            tile.querySelector('#auth-error-count').replaceChildren(renderUserCounter(record.auth_error_cnt, limits.auth_error_cnt || 1));
            tile.querySelector('#off-hours-login-count').replaceChildren(renderUserCounter(record.off_hours_login_cnt, limits.off_hours_login_cnt || 1));
            tile.querySelector('#median-event-count').replaceChildren(renderUserCounter(record.median_event_cnt, limits.median_event_cnt || 1));
            tile.querySelector('#login-count').replaceChildren(renderUserCounter(record.login_cnt, limits.login_cnt || 1));
            tile.querySelector('#session-count').replaceChildren(renderUserCounter(record.session_cnt, limits.session_cnt || 1));
        }
    }

    removeLoaderBackground(tile) {
        const backgrounds = tile.querySelectorAll('.loading-background');
        for (let i = 0; i < backgrounds.length; i++) {
            backgrounds[i].classList.remove('loading-background');
        }
    }

    get elems() {
        return [];
    }

    get url() {
        return URL;
    }
}
