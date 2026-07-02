import {BaseTiles} from './BaseTiles.js?v=0.10.0';
import {Constants} from '../utils/Constants.js?v=0.10.0';
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
} from '../DataRenderers.js?v=0.10.0';
import {replaceChildren} from '../utils/Functions.js?v=0.10.0';

const URL   = `${window.app_base}/loadUserDetails`;

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

        replaceChildren(tile.querySelector('#signup-date'), renderDateWithTimestampTooltip(record.created));
        replaceChildren(tile.querySelector('#lastseen'), renderDateWithTimestampTooltip(record.lastseen));
        replaceChildren(tile.querySelector('#latest-decision'), renderDateWithTimestampTooltip(record.latest_decision));
        replaceChildren(tile.querySelector('#review-status'), renderUserReviewedStatus(record));
        replaceChildren(tile.querySelector('#firstname'), renderUserFirstname(record));
        replaceChildren(tile.querySelector('#lastname'), renderUserLastname(record));
        replaceChildren(tile.querySelector('#userid'), renderUserId(record.userid));
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
            replaceChildren(tile.querySelector(id), (renderUserCounter(record[el], limits[el], false, true)));
        }
    }

    updateAverageDetails(data) {
        const na_tile = false;
        const tile = document.querySelector('#user-behaviour-tile');

        const today = data.todayDetails;
        const yesterday = data.yesterdayDetails;

        if (!tile) {
            return;
        }

        this.removeLoaderBackground(tile);

        const useHyphenOld = !yesterday.session_cnt;
        const useHyphenNew = !today.session_cnt;

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
            replaceChildren(tile.querySelector(id), renderTotalFrameCmp(
                yesterday[el], today[el], useHyphenOld, useHyphenNew
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

        replaceChildren(tile.querySelector('#datacenter'), renderBoolean(record.withdc));
        replaceChildren(tile.querySelector('#vpn'), renderBoolean(record.withvpn));
        replaceChildren(tile.querySelector('#tor'), renderBoolean(record.withtor));
        replaceChildren(tile.querySelector('#apple-relay'), renderBoolean(record.withar));
        replaceChildren(tile.querySelector('#ip-shared'), renderBoolean(record.sharedips));
        replaceChildren(tile.querySelector('#spam-list'), renderBoolean(record.spamlist));
        replaceChildren(tile.querySelector('#blacklisted'), renderBoolean(record.fraud_detected));
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
