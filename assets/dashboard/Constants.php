<?php

declare(strict_types=1);

namespace Tirreno\Dashboard;

class Constants extends \Tirreno\Assets\Constants {
    public const USER_DETAILS_TOTAL_LIMITS = [
        'ips'           => 7,
        'isps'          => 5,
        'countries'     => 3,
        'user_agents'   => 4,
        'edits'         => 1,
        'events'        => 100,
        'sessions'      => 20,
    ];

    public const MAX_STRING_LONG_NETNAME_IN_TABLE = 48;
    public const MAX_STRING_SHORT_NETNAME_IN_TABLE = 25;
    public const MAX_STRING_LENGTH_IN_TABLE = 18;
    public const MAX_STRING_USERID_LENGTH_IN_TABLE = 15;
    public const MAX_STRING_USER_SHORT_LENGTH_IN_TABLE = 19;
    public const MAX_STRING_USER_MEDIUM_LENGTH_IN_TABLE = 23;
    public const MAX_STRING_USER_LONG_LENGTH_IN_TABLE = 32;
    public const MAX_STRING_USER_LONG_LENGTH_IN_TILE = 21;
    public const MAX_STRING_USER_NAME_IN_TABLE = 10;
    public const MAX_STRING_LENGTH_IN_TABLE_ON_DASHBOARD = 24;
    public const MAX_STRING_LENGTH_FOR_EMAIL = 14;
    public const MAX_STRING_LENGTH_FOR_PHONE = 17;
    public const MAX_STRING_LENGTH_FULL_COUNTRY = 23;
    public const MAX_STRING_LENGTH_FOR_TILE = 15;
    public const MAX_STRING_DEVICE_OS_LENGTH = 10;
    public const MAX_STRING_LENGTH_URL = 32;
    public const MAX_STRING_LENGTH_ENDPOINT = 21;
    public const MAX_TOOLTIP_URL_LENGTH = 50;
    public const MAX_TOOLTIP_LENGTH = 121;

    public const COLOR_RED    = '#FB6E88';
    public const COLOR_GREEN  = '#25EAB5';
    public const COLOR_YELLOW = '#F5B944';
    public const COLOR_PURPLE = '#BE95EB';

    public const COLOR_LIGHT_GREEN = 'rgba(64,220,97,0.03)';
    public const COLOR_LIGHT_YELLOW = 'rgba(225,224,137,0.03)';
    public const COLOR_LIGHT_RED = 'rgba(255,51,102,0.03)';
    public const COLOR_LIGHT_PURPLE = 'rgba(190,149,235,0.03)';

    public const USER_LOW_TRUST_SCORE_INF    = 0;
    public const USER_LOW_TRUST_SCORE_SUP    = 33;
    public const USER_MEDIUM_TRUST_SCORE_INF = 33;
    public const USER_MEDIUM_TRUST_SCORE_SUP = 67;
    public const USER_HIGH_TRUST_SCORE_INF   = 67;

    public const USER_IPS_CRITICAL_VALUE       = 9;
    public const USER_EVENTS_CRITICAL_VALUE    = 100000;
    public const USER_DEVICES_CRITICAL_VALUE   = 4;
    public const USER_COUNTRIES_CRITICAL_VALUE = 3;

    public const MAX_HOURS_CHART = 96;
    public const MIN_HOURS_CHART = 3;
    public const DAYS_IN_RANGE = 1;
    public const X_AXIS_SERIFS = 8;

    public const ASN_OVERRIDE = [
        '0'     => 'LAN',
        '64496' => 'N/A',
    ];

    public const NO_RULES_MSG = [
        'value'     => 'No rule',
        'tooltip'   => 'User currently doesn\'t correspond to selected rules.',
    ];

    public const UNDEFINED_RULES_MSG = [
        'value'     => 'In queue',
        'tooltip'   => 'Waiting for a score to be calculated.',
    ];

    public const COLOR_MAP = [
        'red'       => [
            'main'  => self::COLOR_RED,
            'light' => self::COLOR_LIGHT_RED,
        ],
        'yellow'    => [
            'main'  => self::COLOR_YELLOW,
            'light' => self::COLOR_LIGHT_YELLOW,
        ],
        'green'     => [
            'main'  => self::COLOR_GREEN,
            'light' => self::COLOR_LIGHT_GREEN,
        ],
        'purple'    => [
            'main'  => self::COLOR_PURPLE,
            'light' => self::COLOR_LIGHT_PURPLE,
        ],
    ];
}
