import {BaseTiles} from './BaseTiles.js?v=2';
import {Constants} from '../utils/Constants.js?v=2';
import {
    renderDateWithTimestampTooltip,
    renderBoolean,
    renderUserCounter,
    renderReputation,
    renderUserId,
    renderUserFirstname,
    renderUserLastname,
    renderUserReviewedStatus,
    renderTotalFrameCmp,
} from '../DataRenderers.js?v=2';

const URL   = `${window.app_base}/admin/loadUserDetails`;

export class UserTiles extends BaseTiles {
    updateTiles(data) {
        this.updateIdDetails(data);
        this.updateTotalsDetails(data);
        this.updateIpDetails(data);
        this.updateAverageDetails(data);
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

    updateTotalsDetails(data) {
        const tile = document.querySelector('#user-total-tile');

        if (!tile) {
            return;
        }

        const record = data.totalDetails;
        const limits = Constants.USER_DETAILS_TOTAL_LIMITS;
        this.removeLoaderBackground(tile);

        const map = [
            ['#ips',            'ips'],
            ['#isps',           'isps'],
            ['#countries',      'countries'],
            ['#user-agents',    'user_agents'],
            ['#edits',          'edits'],
            ['#events',         'events'],
            ['#sessions',       'sessions'],
        ];

        for (const [id, el] of map) {
            tile.querySelector(id).replaceChildren(renderUserCounter(record[el], limits[el], false, true));
        }
    }

    updateAverageDetails(data) {
        const na_tile = false;
        const tile = document.querySelector('#user-behaviour-tile');

        const record = data.dayDetails;
        const week = data.weekDetails;

        if (!tile) {
            return;
        }

        this.removeLoaderBackground(tile);

        const useHyphenOld = !week.session_cnt;
        const useHyphenNew = !record.session_cnt;

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
            tile.querySelector(id).replaceChildren(renderTotalFrameCmp(
                week[el], record[el], useHyphenOld, useHyphenNew
            ));
        }
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
