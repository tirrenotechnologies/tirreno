import {fireEvent} from './Event.js?v=2';
import {handleAjaxError} from './ErrorHandler.js?v=2';

export class Constants {
    static init(callback) {
        if (this._loaded) {
            return;
        }

        this.setDefaults();

        const onSuccess = this.onSuccess.bind(this);
        const onError = this.onError.bind(this);
        const token = document.head.querySelector('[name=\'csrf-token\'][content]').content;

        const params = {
            token: token,
        };

        $.ajax({
            url: `${window.app_base}/admin/getConstants`,
            type: 'get',
            data: params,
            error: onError,
            success: onSuccess,
        });
    }

    static onSuccess(data, status) {
        if ('success' !== status || 0 === data.length) {
            return;
        }

        this._loaded = true;

        for (const [key, value] of Object.entries(data)) {
            this[key] = value;

        }
        fireEvent('constantsLoaded');
    }

    static onError(xhr, status, error) {
        fireEvent('constantsLoaded');
        //handleAjaxError(xhr, status, error);
    }

    static setDefaults() {
        Constants._loaded = false;

        Constants.MAX_STRING_LONG_NETNAME_IN_TABLE = 48;
        Constants.MAX_STRING_SHORT_NETNAME_IN_TABLE = 25;
        Constants.MAX_STRING_LENGTH_IN_TABLE = 18;
        Constants.MAX_STRING_USERID_LENGTH_IN_TABLE = 15;
        Constants.MAX_STRING_USER_SHORT_LENGTH_IN_TABLE = 19;
        Constants.MAX_STRING_USER_MEDIUM_LENGTH_IN_TABLE = 23;
        Constants.MAX_STRING_USER_LONG_LENGTH_IN_TABLE = 32;
        Constants.MAX_STRING_USER_LONG_LENGTH_IN_TILE = 21;
        Constants.MAX_STRING_USER_NAME_IN_TABLE = 10;
        Constants.MAX_STRING_LENGTH_IN_TABLE_ON_DASHBOARD = 24;
        Constants.MAX_STRING_LENGTH_FOR_EMAIL = 14;
        Constants.MAX_STRING_LENGTH_FOR_PHONE = 17;
        Constants.MAX_STRING_LENGTH_FULL_COUNTRY = 23;
        Constants.MAX_STRING_LENGTH_FOR_TILE = 15;
        Constants.MAX_STRING_DEVICE_OS_LENGTH = 10;
        Constants.MAX_STRING_LENGTH_URL = 32;
        Constants.MAX_STRING_LENGTH_ENDPOINT = 21;
        Constants.MAX_TOOLTIP_URL_LENGTH = 50;
        Constants.MAX_TOOLTIP_LENGTH = 121;

        Constants.COLOR_RED    = '#FB6E88';
        Constants.COLOR_GREEN  = '#25EAB5';
        Constants.COLOR_YELLOW = '#F5B944';
        Constants.COLOR_PURPLE = '#BE95EB';

        Constants.COLOR_LIGHT_GREEN = 'rgba(64,220,97,0.03)';
        Constants.COLOR_LIGHT_YELLOW = 'rgba(225,224,137,0.03)';
        Constants.COLOR_LIGHT_RED = 'rgba(255,51,102,0.03)';
        Constants.COLOR_LIGHT_PURPLE = 'rgba(190,149,235,0.03)';

        Constants.USER_LOW_TRUST_SCORE_INF    = 0;
        Constants.USER_LOW_TRUST_SCORE_SUP    = 33;
        Constants.USER_MEDIUM_TRUST_SCORE_INF = 33;
        Constants.USER_MEDIUM_TRUST_SCORE_SUP = 67;
        Constants.USER_HIGH_TRUST_SCORE_INF   = 67;

        Constants.USER_IPS_CRITICAL_VALUE       = 9;
        Constants.USER_EVENTS_CRITICAL_VALUE    = Infinity;
        Constants.USER_DEVICES_CRITICAL_VALUE   = 4;
        Constants.USER_COUNTRIES_CRITICAL_VALUE = 3;

        Constants.MAX_HOURS_CHART = 96;
        Constants.MIN_HOURS_CHART = 3;
        Constants.DAYS_IN_RANGE = 1;
        Constants.X_AXIS_SERIFS = 8;

        Constants.ASN_OVERRIDE = {
            '0':        'LAN',
            '64496':    'N/A',
        };

        Constants.COUNTRIES_EXCEPTIONS = [null, undefined, 'N/A', 'AN', 'CS', 'YU'];

        Constants.NORMAL_DEVICES = ['smartphone', 'desktop', 'bot', 'tablet'];

        Constants.PHONE_LANDLINE = [
            'landline',
            'FIXED_LINE',
            'FIXED_LINE_OR_MOBILE',
            'TOLL_FREE',
            'SHARED_COST',
        ];

        Constants.NO_RULES_MSG = {
            value:      'No rule',
            tooltip:    'User currently doesn\'t correspond to selected rules.',
        };

        Constants.UNDEFINED_RULES_MSG = {
            value:      'In queue',
            tooltip:    'Waiting for a score to be calculated.',
        };

        Constants.MIDLINE_HELLIP = '\u22EF';
        Constants.HELLIP = '\u2026';
        Constants.HYPHEN = '\uFF0D';

        Constants.COLOR_MAP = {
            'red':      {
                'main':     Constants.COLOR_RED,
                'light':    Constants.COLOR_LIGHT_RED,
            },
            'yellow':   {
                'main':     Constants.COLOR_YELLOW,
                'light':    Constants.COLOR_LIGHT_YELLOW,
            },
            'green':    {
                'main':     Constants.COLOR_GREEN,
                'light':    Constants.COLOR_LIGHT_GREEN,
            },
            'purple':   {
                'main':     Constants.COLOR_PURPLE,
                'light':    Constants.COLOR_LIGHT_PURPLE,
            },
        };

        Constants.USER_DETAILS_TOTAL_LIMITS = {
            'ips':          7,
            'isps':         5,
            'countries':    3,
            'user_agents':  4,
            'edits':        1,
            'events':       100,
            'sessions':     20,
        };
    }
};
