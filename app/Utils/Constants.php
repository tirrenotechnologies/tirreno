<?php

/**
 * tirreno ~ open-source security framework
 * Copyright (c) Tirreno Technologies Sàrl (https://www.tirreno.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Tirreno Technologies Sàrl (https://www.tirreno.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.tirreno.com Tirreno(tm)
 */

declare(strict_types=1);

namespace Tirreno\Utils;

class Constants {
    private static array $instances = [];

    final private function __construct() {
    }

    public static function get(): static {
        $cls = static::class;

        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
            self::$instances[$cls]->init();
        }

        return self::$instances[$cls];
    }

    protected function init(): void {
        $this->additional();

        $vars = get_object_vars($this);

        $storageKey = null;
        $storageValue = null;

        $assetsConstants = tirreno('assets')->serverConstants->getConstantsObj();
        $assetsConstants = $assetsConstants ? $assetsConstants::listConstants() : [];

        foreach ($vars as $key => $value) {
            $storageValue = $assetsConstants[$key] ?? null;
            if ($this->addValue($key, $storageValue)) {
                continue;
            }

            $storageKey = 'EXTRA_' . $key;

            if (!tirreno('router')->exists($storageKey)) {
                continue;
            }

            $storageValue = tirreno('storage')->get($storageKey);
            $this->addValue($key, $storageValue);
        }
    }

    protected function addValue(string $key, mixed $value): bool {
        if ($value === null || gettype($value) !== gettype($this->$key)) {
            return false;
        }

        // append if value is sequential array
        $append = is_array($value) && array_keys($value) === range(0, count($value) - 1);
        $this->$key = $append ? array_merge($value, $this->$key) : $value;

        return true;
    }

    public function __get(string $name): string|array|int {
        if (!property_exists($this, $name)) {
            throw new \LogicException('Undefined constant: ' . $name);
        }

        return $this->$name;
    }

    public function __set(string $name, mixed $value): void {
        throw new \LogicException('Constants are read-only');
    }

    protected int $RULE_EVENT_CONTEXT_LIMIT                  = 25;
    protected int $RULE_CHECK_USERS_PASSED_TO_CLIENT         = 25;
    protected int $RULE_USERS_BATCH_SIZE                     = 3500;
    protected int $RULE_EMAIL_MAXIMUM_LOCAL_PART_LENGTH      = 17;
    protected int $RULE_EMAIL_MAXIMUM_DOMAIN_LENGTH          = 22;
    protected int $RULE_MAXIMUM_NUMBER_OF_404_CODES          = 4;
    protected int $RULE_MAXIMUM_NUMBER_OF_500_CODES          = 4;
    protected int $RULE_MAXIMUM_NUMBER_OF_LOGIN_ATTEMPTS     = 3;
    protected int $RULE_LOGIN_ATTEMPTS_WINDOW                = 8;
    protected int $RULE_NEW_DEVICE_MAX_AGE_IN_SECONDS        = 60 * 60 * 3;
    protected array $RULE_REGULAR_OS_NAMES                   = ['Windows', 'Android', 'Mac', 'iOS'];
    protected array $RULE_REGULAR_BROWSER_NAMES              = [
        'Chrome'            => 90,
        'Chrome Mobile'     => 90,
        'Firefox'           => 78,
        'Opera'             => 70,
        'Safari'            => 13,
        'Mobile Safari'     => 13,
        'Samsung Browser'   => 12,
        'Internet Explorer' => 12,
        'Microsoft Edge'    => 90,
        'Chrome Mobile iOS' => 90,
        'Android Browser'   => 81,
        'Chrome Webview'    => 90,
        'Google Search App' => 90,
        'Yandex Browser'    => 20,
    ];

    protected array $DEVICE_TYPES    = [
        'bot',
        'desktop',
        'smartphone',
        'tablet',
        'other',
        'unknown',
    ];

    protected int $CHECK_RULE_USERS_LIMIT = 1000;
    protected int $USER_QUEUE_EVENTS_LIMIT = 100000;
    protected int $LOGBOOK_LIMIT  = 1000;

    protected int $SECONDS_IN_WEEK    = 60 * 60 * 24 * 7;
    protected int $SECONDS_IN_DAY     = 60 * 60 * 24;
    protected int $SECONDS_IN_HOUR    = 60 * 60;
    protected int $SECONDS_IN_MINUTE  = 60;

    protected int $NIGHT_RANGE_SECONDS_START  = 0;        // midnight
    protected int $NIGHT_RANGE_SECONDS_END    = 18000;    // 5 AM

    protected int $COUNTRY_CODE_NIGERIA       = 160;
    protected int $COUNTRY_CODE_INDIA         = 104;
    protected int $COUNTRY_CODE_CHINA         = 47;
    protected int $COUNTRY_CODE_BRAZIL        = 31;
    protected int $COUNTRY_CODE_PAKISTAN      = 168;
    protected int $COUNTRY_CODE_INDONESIA     = 105;
    protected int $COUNTRY_CODE_VENEZUELA     = 243;
    protected int $COUNTRY_CODE_SOUTH_AFRICA  = 199;
    protected int $COUNTRY_CODE_PHILIPPINES   = 175;
    protected int $COUNTRY_CODE_ROMANIA       = 182;
    protected int $COUNTRY_CODE_RUSSIA        = 183;
    protected int $COUNTRY_CODE_AUSTRALIA     = 14;
    protected int $COUNTRY_CODE_UAE           = 236;
    protected int $COUNTRY_CODE_JAPAN         = 113;

    protected array $COUNTRY_CODES_NORTH_AMERICA    = [238, 40];
    protected array $COUNTRY_CODES_EUROPE           = [77, 2, 15, 22, 35, 57, 60, 61, 62, 71, 78, 85, 88, 102, 108, 111, 122, 128, 129, 136, 155, 177, 178, 182, 195, 196, 203, 215];

    protected int $EVENT_REQUEST_TYPE_HEAD    = 3;

    protected int $ACCOUNT_OPERATION_QUEUE_CLEAR_COMPLETED_AFTER_DAYS = 7;
    protected int $ACCOUNT_OPERATION_QUEUE_AUTO_UNCLOG_AFTER_SEC      = 60 * 30;
    protected int $ACCOUNT_OPERATION_QUEUE_EXECUTE_TIME_SEC           = 60 * 3;
    protected int $ACCOUNT_OPERATION_QUEUE_BATCH_SIZE                 = 2500;
    protected int $NEW_EVENTS_BATCH_SIZE                              = 15000;

    protected int $USER_LOW_SCORE_INF     = 0;
    protected int $USER_LOW_SCORE_SUP     = 33;
    protected int $USER_MEDIUM_SCORE_INF  = 33;
    protected int $USER_MEDIUM_SCORE_SUP  = 67;
    protected int $USER_HIGH_SCORE_INF    = 67;

    protected string $UNAUTHORIZED_USERID    = 'N/A';

    protected string $ENRICHMENT_IP_IS_BOGON     = 'IP is bogon';
    protected string $ENRICHMENT_IP_IS_NOT_FOUND = 'Value is not found';

    protected string $MAIL_FROM_NAME = 'Analytics';
    protected string $MAIL_HOST      = 'smtp.eu.mailgun.org';
    protected string $MAIL_SEND_BIN  = '/usr/sbin/sendmail';

    protected string $PAGE_TITLE_POSTFIX = '| tirreno';

    protected int $PAGE_VIEW_EVENT_TYPE_ID = 1;
    protected int $PAGE_EDIT_EVENT_TYPE_ID = 2;
    protected int $PAGE_DELETE_EVENT_TYPE_ID = 3;
    protected int $PAGE_SEARCH_EVENT_TYPE_ID = 4;
    protected int $ACCOUNT_LOGIN_EVENT_TYPE_ID = 5;
    protected int $ACCOUNT_LOGOUT_EVENT_TYPE_ID = 6;
    protected int $ACCOUNT_LOGIN_FAIL_EVENT_TYPE_ID = 7;
    protected int $ACCOUNT_REGISTRATION_EVENT_TYPE_ID = 8;
    protected int $ACCOUNT_EMAIL_CHANGE_EVENT_TYPE_ID = 9;
    protected int $ACCOUNT_PASSWORD_CHANGE_EVENT_TYPE_ID = 10;
    protected int $ACCOUNT_EDIT_EVENT_TYPE_ID = 11;
    protected int $PAGE_ERROR_EVENT_TYPE_ID = 12;
    protected int $FIELD_EDIT_EVENT_TYPE_ID = 13;

    protected int $SUPERUSER_ROLE_ID = 1;
    protected int $GUEST_ROLE_ID = 2;
    protected int $OPERATOR_ROLE_ID = 3;

    protected int $PAGE_VIEW_PERMISSION_ID = 1;
    protected int $PAGE_EDIT_PERMISSION_ID = 2;
    protected int $PAGE_DELETE_PERMISSION_ID = 3;
    protected int $PAGE_PUBLISH_PERMISSION_ID = 4;
    protected int $USER_ADMIN_PERMISSION_ID = 5;

    protected int $GUEST_OPERATOR_ID = 37;
    protected int $DAEMON_OPERATOR_ID = 42;
    protected int $RESERVED_OPERATOR_IDS = 100;

    protected string $BASE_RULE_PRESET_ID = 'default';

    protected array $EVENTS_CHARTS = [
        'resource',
        'user',
        'isp',
        'ip',
        'domain',
        'userAgent',
        'events',
        'phones',
        'country',
        'field',
    ];

    protected array $CHART_RESOLUTION = [
        'day'       => 60 * 60 * 24,
        'hour'      => 60 * 60,
        'minute'    => 60,
    ];

    protected array $TOP_TEN_MODELS_MAP = [
        'mostActiveUsers'           => \Tirreno\Models\TopTen\UsersByEvents::class,
        'mostActiveCountries'       => \Tirreno\Models\TopTen\CountriesByUsers::class,
        'mostActiveUrls'            => \Tirreno\Models\TopTen\ResourcesByUsers::class,
        'ipsWithTheMostUsers'       => \Tirreno\Models\TopTen\IpsByUsers::class,
        'usersWithMostLoginFail'    => \Tirreno\Models\TopTen\UsersByLoginFail::class,
        'usersWithMostIps'          => \Tirreno\Models\TopTen\UsersByIps::class,
    ];

    protected array $RULES_TOTALS_MODELS = [
        \Tirreno\Models\Phone::class,
        \Tirreno\Models\Ip::class,
        \Tirreno\Models\Session::class,
        \Tirreno\Models\User::class,
    ];

    protected array $REST_TOTALS_MODELS = [
        'isp'       => \Tirreno\Models\Isp::class,
        'resource'  => \Tirreno\Models\Resource::class,
        'domain'    => \Tirreno\Models\Domain::class,
        'device'    => \Tirreno\Models\Device::class,
        'country'   => \Tirreno\Models\Country::class,
        'field'     => \Tirreno\Models\FieldAudit::class,
    ];

    protected array $ENRICHING_ATTRIBUTES = [
        'ip'        => \Tirreno\Models\Ip::class,
        'email'     => \Tirreno\Models\Email::class,
        'domain'    => \Tirreno\Models\Domain::class,
        'phone'     => \Tirreno\Models\Phone::class,
        //'ua'        => \Tirreno\Models\Device::class,
    ];

    protected array $INTERNAL_PAGES = [
        'isps',
        'isp',
        'users',
        'user',
        'ips',
        'ip',
        'domains',
        'domain',
        'countries',
        'country',
        'userAgents',
        'userAgent',
        'resources',
        'resource',
        'logbook',
        'home',
        'api',
        'reviewQueue',
        'rules',
        'settings',
        'watchlist',
        'blacklist',
        'manualCheck',
        'events',
        'fieldAudits',
        'fieldAudit',
    ];

    protected array $IP_TYPES = [
        'Blacklisted',
        'Spam list',
        'Localhost',
        'TOR',
        'Starlink',
        'AppleRelay',
        'VPN',
        'Datacenter',
        'Unknown',
        'Residential',
    ];

    protected array $ALERT_EVENT_TYPES = [];

    protected array $EDITING_EVENT_TYPES = [];

    protected array $NORMAL_EVENT_TYPES = [];

    protected array $FAILED_LOGBOOK_EVENT_TYPES = [
        'critical_validation_error',
        'critical_error',
        'rate_limit_exceeded',
    ];

    protected array $ISSUED_LOGBOOK_EVENT_TYPES = [
        'validation_error',
    ];

    protected array $NORMAL_LOGBOOK_EVENT_TYPES = [
        'success',
    ];

    protected int $LOGBOOK_ERROR_TYPE_SUCCESS                    = 0;
    protected int $LOGBOOK_ERROR_TYPE_VALIDATION_ERROR           = 1;
    protected int $LOGBOOK_ERROR_TYPE_CRITICAL_VALIDATION_ERROR  = 2;
    protected int $LOGBOOK_ERROR_TYPE_CRITICAL_ERROR             = 3;
    protected int $LOGBOOK_ERROR_TYPE_RATE_LIMIT_EXCEEDED        = 4;

    protected array $ENTITY_TYPES = [
        'IP',
        'Email',
        'Phone',
    ];

    protected string $BASE_ERROR_EMAIL_SUBJECT          = 'Error %s occurred';
    protected string $BASE_ERROR_EMAIL_BODY_TEMPLATE    = (
        '<p>Error occurred at: %s</p>
        <p>Host: %s</p>
        <p>Message: </p>%s
        <p>Trace: </p>%s
        '
    );

    protected string $RISK_SCORE_QUEUE_ACTION_TYPE   = 'calculate_risk_score';
    protected string $BLACKLIST_QUEUE_ACTION_TYPE    = 'blacklist';
    protected string $DELETE_USER_QUEUE_ACTION_TYPE  = 'delete';
    protected string $ENRICHMENT_QUEUE_ACTION_TYPE   = 'enrichment';

    protected string $WAITING_QUEUE_STATUS_TYPE      = 'waiting';
    protected string $EXECUTING_QUEUE_STATUS_TYPE    = 'executing';
    protected string $COMPLETED_QUEUE_STATUS_TYPE    = 'completed';
    protected string $FAILED_QUEUE_STATUS_TYPE       = 'failed';

    protected string $DAILY_NOTIFICATION_REMINDER    = 'daily';
    protected string $WEEKLY_NOTIFICATION_REMINDER   = 'weekly';
    protected string $NO_NOTIFICATION_REMINDER       = 'off';

    protected array $NOTIFICATION_REMINDER_TYPES = [];

    protected string $SINGLE_RESPONSE_TYPE           = 'single';
    protected string $COLLECTION_RESPONSE_TYPE       = 'collection';

    protected int $PRIMARY_RULES_SET_ID = 1;

    protected int $RULE_WEIGHT_POSITIVE   = -20;
    protected int $RULE_WEIGHT_NONE       = 0;
    protected int $RULE_WEIGHT_MEDIUM     = 10;
    protected int $RULE_WEIGHT_HIGH       = 20;
    protected int $RULE_WEIGHT_EXTREME    = 70;

    protected array $RULE_WEIGHT_MAP      = [];

    protected function additional(): void {
        $this->ALERT_EVENT_TYPES = [
            $this->PAGE_DELETE_EVENT_TYPE_ID,
            $this->PAGE_ERROR_EVENT_TYPE_ID,
            $this->ACCOUNT_LOGIN_FAIL_EVENT_TYPE_ID,
            $this->ACCOUNT_EMAIL_CHANGE_EVENT_TYPE_ID,
            $this->ACCOUNT_PASSWORD_CHANGE_EVENT_TYPE_ID,
        ];

        $this->EDITING_EVENT_TYPES = [
            $this->PAGE_EDIT_EVENT_TYPE_ID,
            $this->ACCOUNT_REGISTRATION_EVENT_TYPE_ID,
            $this->ACCOUNT_EDIT_EVENT_TYPE_ID,
            $this->FIELD_EDIT_EVENT_TYPE_ID,
        ];

        $this->NORMAL_EVENT_TYPES = [
            $this->PAGE_VIEW_EVENT_TYPE_ID,
            $this->PAGE_SEARCH_EVENT_TYPE_ID,
            $this->ACCOUNT_LOGIN_EVENT_TYPE_ID,
            $this->ACCOUNT_LOGOUT_EVENT_TYPE_ID,
        ];

        $this->NOTIFICATION_REMINDER_TYPES = [
            $this->DAILY_NOTIFICATION_REMINDER,
            $this->WEEKLY_NOTIFICATION_REMINDER,
            $this->NO_NOTIFICATION_REMINDER,
        ];

        $this->RULE_WEIGHT_MAP = [
            'positive'  => $this->RULE_WEIGHT_POSITIVE,
            'none'      => $this->RULE_WEIGHT_NONE,
            'medium'    => $this->RULE_WEIGHT_MEDIUM,
            'high'      => $this->RULE_WEIGHT_HIGH,
            'extreme'   => $this->RULE_WEIGHT_EXTREME,
        ];
    }
}
