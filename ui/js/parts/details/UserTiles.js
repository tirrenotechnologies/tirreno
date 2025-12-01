import {BaseTiles} from './BaseTiles.js?v=2';
import {
    renderDateWithTimestampTooltip,
    renderBoolean,
    renderUserCounter,
    renderReputation,
    renderUserId,
    renderUserFirstname,
    renderUserLastname,
    renderUserReviewedStatus,
} from '../DataRenderers.js?v=2';

const URL   = `${window.app_base}/admin/loadUserDetails`;

export class UserTiles extends BaseTiles {
    updateTiles(data) {
        this.updateIdDetails(data);
        this.updateIpDetails(data);
        this.updateDayDetails(data);
        this.updateWeekDetails(data);
    }

    updateIdDetails(data) {
        const tile = document.querySelector('#user-id-tile');

        if (!tile) {
            return;
        }

        const record = data.userDetails;
        this.removeLoaderBackground(tile);

        tile.querySelector('#signup-date').replaceChildren(renderDateWithTimestampTooltip(record.created));
        tile.querySelector('#lastseen').replaceChildren(renderDateWithTimestampTooltip(record.lastseen));
        tile.querySelector('#latest-decision').replaceChildren(renderDateWithTimestampTooltip(record.latest_decision));
        tile.querySelector('#review-status').replaceChildren(renderUserReviewedStatus(record));
        tile.querySelector('#firstname').replaceChildren(renderUserFirstname(record));
        tile.querySelector('#lastname').replaceChildren(renderUserLastname(record));
        tile.querySelector('#userid').replaceChildren(renderUserId(record.userid));
    }

    updateIpDetails(data) {
        const tile = document.querySelector('#user-ip-tile');

        if (!tile) {
            return;
        }

        const record = data.ipDetails;
        this.removeLoaderBackground(tile);

        tile.querySelector('#datacenter').replaceChildren(renderBoolean(record.withdc));
        tile.querySelector('#vpn').replaceChildren(renderBoolean(record.withvpn));
        tile.querySelector('#tor').replaceChildren(renderBoolean(record.withtor));
        tile.querySelector('#apple-relay').replaceChildren(renderBoolean(record.withar));
        tile.querySelector('#ip-shared').replaceChildren(renderBoolean(record.sharedips));
        tile.querySelector('#spam-list').replaceChildren(renderBoolean(record.spamlist));
        tile.querySelector('#blacklisted').replaceChildren(renderBoolean(record.fraud_detected));
    }

    updateDayDetails(data) {
        this.updateDateRangeDetails(data.dayDetails, '#day-behaviour-tile', false);
    }

    updateWeekDetails(data) {
        this.updateDateRangeDetails(data.weekDetails, '#week-behaviour-tile', false);
    }

    updateDateRangeDetails(record, tileId, na_tile = true) {
        const tile = document.querySelector(tileId);

        if (!tile) {
            return;
        }

        const limits = record.limits;

        const span = document.createElement('span');
        span.className = 'nolight';
        span.textContent = na_tile ? 'N/A' : '0';

        this.removeLoaderBackground(tile);

        if (record.session_cnt === 0) {
            tile.querySelector('#failed-login-count').replaceChildren(span.cloneNode(true));
            tile.querySelector('#password-reset-count').replaceChildren(span.cloneNode(true));
            tile.querySelector('#auth-error-count').replaceChildren(span.cloneNode(true));
            tile.querySelector('#off-hours-login-count').replaceChildren(span.cloneNode(true));
            tile.querySelector('#avg-event-count').replaceChildren(span.cloneNode(true));
            tile.querySelector('#login-count').replaceChildren(span.cloneNode(true));
            tile.querySelector('#session-count').replaceChildren(span.cloneNode(true));
        } else {
            const map = [
                ['#failed-login-count',     'failed_login_cnt'],
                ['#password-reset-count',   'password_reset_cnt'],
                ['#auth-error-count',       'auth_error_cnt'],
                ['#off-hours-login-count',  'off_hours_login_cnt'],
                ['#avg-event-count',        'avg_event_cnt'],
                ['#login-count',            'login_cnt'],
                ['#session-count',          'session_cnt'],
            ];

            for (const [id, el] of map) {
                tile.querySelector(id).replaceChildren(renderUserCounter(record[el], limits[el] || 1));
            }
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
