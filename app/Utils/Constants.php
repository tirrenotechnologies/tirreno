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

        $f3 = \Base::instance();
        $vars = get_object_vars($this);

        $f3Key = null;
        $f3Value = null;

        foreach ($vars as $key => $value) {
            $f3Key = 'EXTRA_' . $key;

            if (!$f3->exists($f3Key)) {
                continue;
            }

            $f3Value = $f3->get($f3Key);

            if (gettype($f3Value) !== gettype($value)) {
                continue;
            }

            $this->$key = is_array($value) && is_array($f3Value) ? array_merge($value, $f3Value) : $f3Value;
        }
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

    protected array $CHART_MODEL_MAP = [
        'resources'         => \Tirreno\Models\Chart\Resources::class,
        'resource'          => \Tirreno\Models\Chart\Resource::class,
        'users'             => \Tirreno\Models\Chart\Users::class,
        'user'              => \Tirreno\Models\Chart\User::class,
        'isps'              => \Tirreno\Models\Chart\Isps::class,
        'isp'               => \Tirreno\Models\Chart\Isp::class,
        'ips'               => \Tirreno\Models\Chart\Ips::class,
        'ip'                => \Tirreno\Models\Chart\Ip::class,
        'domains'           => \Tirreno\Models\Chart\Domains::class,
        'domain'            => \Tirreno\Models\Chart\Domain::class,
        'userAgents'        => \Tirreno\Models\Chart\UserAgents::class,
        'userAgent'         => \Tirreno\Models\Chart\UserAgent::class,
        'events'            => \Tirreno\Models\Chart\Events::class,
        'emails'            => \Tirreno\Models\Chart\Emails::class,
        'phones'            => \Tirreno\Models\Chart\Phones::class,
        'review-queue'      => \Tirreno\Models\Chart\ReviewQueue::class,
        'country'           => \Tirreno\Models\Chart\Country::class,
        'blacklist'         => \Tirreno\Models\Chart\Blacklist::class,
        'logbook'           => \Tirreno\Models\Chart\Logbook::class,
        'stats'             => \Tirreno\Models\Chart\SessionStat::class,
        'fields'            => \Tirreno\Models\Chart\FieldAuditTrails::class,
        'field'             => \Tirreno\Models\Chart\FieldAuditTrail::class,
    ];

    protected array $LINE_CHARTS = [
        'ips',
        'users',
        'review-queue',
        'events',
        'phones',
        'emails',
        'resources',
        'userAgents',
        'isps',
        'domains',
        'blacklist',
        'logbook',
        'fields',
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

    protected array $ADMIN_PAGES = [
        'AdminIsps',
        'AdminIsp',
        'AdminUsers',
        'AdminUser',
        'AdminIps',
        'AdminIp',
        'AdminDomains',
        'AdminDomain',
        'AdminCountries',
        'AdminCountry',
        'AdminUserAgents',
        'AdminUserAgent',
        'AdminResources',
        'AdminResource',
        'AdminLogbook',
        'AdminHome',
        'AdminApi',
        'AdminReviewQueue',
        'AdminRules',
        'AdminSettings',
        'AdminWatchlist',
        'AdminBlacklist',
        'AdminManualCheck',
        'AdminEvents',
        'AdminFieldAudits',
        'AdminFieldAudit',
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

    protected int $RULE_WEIGHT_POSITIVE   = -20;
    protected int $RULE_WEIGHT_NONE       = 0;
    protected int $RULE_WEIGHT_MEDIUM     = 10;
    protected int $RULE_WEIGHT_HIGH       = 20;
    protected int $RULE_WEIGHT_EXTREME    = 70;

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

        $this->DEFAULT_RULES_ACCOUNT_TAKEOVER = [
            // Medium
            'A03'   => $this->RULE_WEIGHT_MEDIUM,    // New device and new country
            'A04'   => $this->RULE_WEIGHT_MEDIUM,    // New device and new subnet
            'A08'   => $this->RULE_WEIGHT_MEDIUM,    // Browser language changed
            'B01'   => $this->RULE_WEIGHT_MEDIUM,    // Multiple countries
            'B02'   => $this->RULE_WEIGHT_MEDIUM,    // User has changed a password
            'B03'   => $this->RULE_WEIGHT_MEDIUM,    // User has changed an email
            'B21'   => $this->RULE_WEIGHT_MEDIUM,    // Multiple devices in one session
            'D04'   => $this->RULE_WEIGHT_MEDIUM,    // Rare browser device
            'D05'   => $this->RULE_WEIGHT_MEDIUM,    // Rare OS device
            'I03'   => $this->RULE_WEIGHT_MEDIUM,    // IP appears in spam list
            'I09'   => $this->RULE_WEIGHT_MEDIUM,    // Numerous IPs
            // High
            'B04'   => $this->RULE_WEIGHT_HIGH,      // Multiple 5xx errors
            'B05'   => $this->RULE_WEIGHT_HIGH,      // Multiple 4xx errors
            'B19'   => $this->RULE_WEIGHT_HIGH,      // Night time requests
            'B20'   => $this->RULE_WEIGHT_HIGH,      // Multiple countries in one session
            'D01'   => $this->RULE_WEIGHT_HIGH,      // Device is unknown
            // Extreme
            'A01'   => $this->RULE_WEIGHT_EXTREME,   // Multiple login fail
            'A02'   => $this->RULE_WEIGHT_EXTREME,   // Login failed on new device
            'A05'   => $this->RULE_WEIGHT_EXTREME,   // Password change on new device
            'A06'   => $this->RULE_WEIGHT_EXTREME,   // Password change in new country
            'B06'   => $this->RULE_WEIGHT_EXTREME,   // Potentially vulnerable URL
            'E19'   => $this->RULE_WEIGHT_EXTREME,   // Multiple emails changed
            'I01'   => $this->RULE_WEIGHT_EXTREME,   // IP belongs to TOR
            'I04'   => $this->RULE_WEIGHT_EXTREME,   // Shared IP
            'R01'   => $this->RULE_WEIGHT_EXTREME,   // IP in blacklist
        ];

        $this->DEFAULT_RULES_CREDENTIAL_STUFFING = [
            // High
            'A01'   => $this->RULE_WEIGHT_HIGH,      // Multiple login fail
            'A02'   => $this->RULE_WEIGHT_HIGH,      // Login failed on new device
            'B04'   => $this->RULE_WEIGHT_HIGH,      // Multiple 5xx errors
            'B05'   => $this->RULE_WEIGHT_HIGH,      // Multiple 4xx errors
            'B06'   => $this->RULE_WEIGHT_HIGH,      // Potentially vulnerable URL
            'I02'   => $this->RULE_WEIGHT_HIGH,      // IP hosting domain
            'I03'   => $this->RULE_WEIGHT_HIGH,      // IP appears in spam list
            'I06'   => $this->RULE_WEIGHT_HIGH,      // IP belongs to datacenter
            // Extreme
            'R01'   => $this->RULE_WEIGHT_EXTREME,   // IP in blacklist
        ];

        $this->DEFAULT_RULES_CONTENT_SPAM = [
            // High
            'B11'   => $this->RULE_WEIGHT_HIGH,      // New account (1 day)
            'B26'   => $this->RULE_WEIGHT_HIGH,      // Single event sessions
            'E03'   => $this->RULE_WEIGHT_HIGH,      // Suspicious words in email
            'E04'   => $this->RULE_WEIGHT_HIGH,      // Numeric email name
            'E21'   => $this->RULE_WEIGHT_HIGH,      // No vowels in email
            'I02'   => $this->RULE_WEIGHT_HIGH,      // IP hosting domain
            'I03'   => $this->RULE_WEIGHT_HIGH,      // IP appears in spam list
            // Extreme
            'R01'   => $this->RULE_WEIGHT_EXTREME,   // IP in blacklist
            'R02'   => $this->RULE_WEIGHT_EXTREME,   // Email in blacklist
        ];

        $this->DEFAULT_RULES_ACCOUNT_REGISTRATION = [
            // Positive
            'E23'   => $this->RULE_WEIGHT_POSITIVE,  // Educational domain (.edu)
            'E24'   => $this->RULE_WEIGHT_POSITIVE,  // Government domain (.gov)
            'E25'   => $this->RULE_WEIGHT_POSITIVE,  // Military domain (.mil)
            'E26'   => $this->RULE_WEIGHT_POSITIVE,  // iCloud mailbox
            'I08'   => $this->RULE_WEIGHT_POSITIVE,  // IP belongs to Starlink
            'I10'   => $this->RULE_WEIGHT_POSITIVE,  // Only residential IPs
            // Medium
            'D08'   => $this->RULE_WEIGHT_MEDIUM,    // Two or more phone devices
            'D09'   => $this->RULE_WEIGHT_MEDIUM,    // Old browser
            'E07'   => $this->RULE_WEIGHT_MEDIUM,    // Long email username
            'E08'   => $this->RULE_WEIGHT_MEDIUM,    // Long domain name
            'E21'   => $this->RULE_WEIGHT_MEDIUM,    // No vowels in email
            'E22'   => $this->RULE_WEIGHT_MEDIUM,    // No consonants in email
            'I05'   => $this->RULE_WEIGHT_MEDIUM,    // IP belongs to commercial VPN
            'I06'   => $this->RULE_WEIGHT_MEDIUM,    // IP belongs to datacenter
            // High
            'B19'   => $this->RULE_WEIGHT_HIGH,      // Night time requests
            'B21'   => $this->RULE_WEIGHT_HIGH,      // Multiple devices in one session
            'B22'   => $this->RULE_WEIGHT_HIGH,      // Multiple IP addresses in one session
            'B23'   => $this->RULE_WEIGHT_HIGH,      // User's full name contains space or hyphen
            'D01'   => $this->RULE_WEIGHT_HIGH,      // Device is unknown
            'D03'   => $this->RULE_WEIGHT_HIGH,      // Device is bot
            'D04'   => $this->RULE_WEIGHT_HIGH,      // Rare browser device
            'D07'   => $this->RULE_WEIGHT_HIGH,      // Several desktop devices
            'D10'   => $this->RULE_WEIGHT_HIGH,      // Potentially vulnerable User-Agent
            'E01'   => $this->RULE_WEIGHT_HIGH,      // Invalid email format
            'E03'   => $this->RULE_WEIGHT_HIGH,      // Suspicious words in email
            'E04'   => $this->RULE_WEIGHT_HIGH,      // Numeric email name
            'E06'   => $this->RULE_WEIGHT_HIGH,      // Consecutive digits in email
            'I02'   => $this->RULE_WEIGHT_HIGH,      // IP hosting domain
            'I03'   => $this->RULE_WEIGHT_HIGH,      // IP appears in spam list
            'I04'   => $this->RULE_WEIGHT_HIGH,      // Shared IP
            // Extreme
            'B07'   => $this->RULE_WEIGHT_EXTREME,   // User's full name contains digits
            'B18'   => $this->RULE_WEIGHT_EXTREME,   // HEAD request
            'I01'   => $this->RULE_WEIGHT_EXTREME,   // IP belongs to TOR
            'R01'   => $this->RULE_WEIGHT_EXTREME,   // IP in blacklist
            'R03'   => $this->RULE_WEIGHT_EXTREME,   // Phone in blacklist
        ];

        $this->DEFAULT_RULES_FRAUD_PREVENTION = [
            // Positive
            'E23'   => $this->RULE_WEIGHT_POSITIVE,  // Educational domain (.edu)
            'E24'   => $this->RULE_WEIGHT_POSITIVE,  // Government domain (.gov)
            'E25'   => $this->RULE_WEIGHT_POSITIVE,  // Military domain (.mil)
            'E26'   => $this->RULE_WEIGHT_POSITIVE,  // iCloud mailbox
            // Medium
            'D07'   => $this->RULE_WEIGHT_MEDIUM,    // Several desktop devices
            'D08'   => $this->RULE_WEIGHT_MEDIUM,    // Two or more phone devices
            // High
            'B19'   => $this->RULE_WEIGHT_HIGH,      // Night time requests
            'B20'   => $this->RULE_WEIGHT_HIGH,      // Multiple countries in one session
            'B21'   => $this->RULE_WEIGHT_HIGH,      // Multiple devices in one session
            'B22'   => $this->RULE_WEIGHT_HIGH,      // Multiple IP addresses in one session
            'E03'   => $this->RULE_WEIGHT_HIGH,      // Suspicious words in email
            'E04'   => $this->RULE_WEIGHT_HIGH,      // Numeric email name
            'E06'   => $this->RULE_WEIGHT_HIGH,      // Consecutive digits in email
            'E07'   => $this->RULE_WEIGHT_HIGH,      // Long email username
            'E21'   => $this->RULE_WEIGHT_HIGH,      // No vowels in email
            'I02'   => $this->RULE_WEIGHT_HIGH,      // IP hosting domain
            'I03'   => $this->RULE_WEIGHT_HIGH,      // IP appears in spam list
            'I04'   => $this->RULE_WEIGHT_HIGH,      // Shared IP
            'I05'   => $this->RULE_WEIGHT_HIGH,      // IP belongs to commercial VPN
            'I06'   => $this->RULE_WEIGHT_HIGH,      // IP belongs to datacenter
            'I09'   => $this->RULE_WEIGHT_HIGH,      // Numerous IPs
            'P03'   => $this->RULE_WEIGHT_HIGH,      // Shared phone number
            // Extreme
            'I01'   => $this->RULE_WEIGHT_EXTREME,   // IP belongs to TOR
            'R01'   => $this->RULE_WEIGHT_EXTREME,   // IP in blacklist
            'R03'   => $this->RULE_WEIGHT_EXTREME,   // Phone in blacklist
        ];

        $this->DEFAULT_RULES_INSIDER_THREAT = [
            // Extreme
            'B04'   => $this->RULE_WEIGHT_EXTREME,   // Multiple 5xx errors
            'B05'   => $this->RULE_WEIGHT_EXTREME,   // Multiple 4xx errors
            'B06'   => $this->RULE_WEIGHT_EXTREME,   // Potentially vulnerable URL
            'B19'   => $this->RULE_WEIGHT_EXTREME,   // Night time requests
            'D10'   => $this->RULE_WEIGHT_EXTREME,   // Potentially vulnerable User-Agent
            'I01'   => $this->RULE_WEIGHT_EXTREME,   // IP belongs to TOR
        ];

        $this->DEFAULT_RULES_BOT_DETECTION = [
            // Positive
            'I10'   => $this->RULE_WEIGHT_POSITIVE,  // Only residential IPs
            // Medium
            'D02'   => $this->RULE_WEIGHT_MEDIUM,    // Device is Linux
            // High
            'B19'   => $this->RULE_WEIGHT_HIGH,      // Night time requests
            'D01'   => $this->RULE_WEIGHT_HIGH,      // Device is unknown
            'D04'   => $this->RULE_WEIGHT_HIGH,      // Rare browser device
            'D05'   => $this->RULE_WEIGHT_HIGH,      // Rare OS device
            'D09'   => $this->RULE_WEIGHT_HIGH,      // Old browser
            'I01'   => $this->RULE_WEIGHT_HIGH,      // IP belongs to TOR
            'I02'   => $this->RULE_WEIGHT_HIGH,      // IP hosting domain
            'I06'   => $this->RULE_WEIGHT_HIGH,      // IP belongs to datacenter
            // Extreme
            'B04'   => $this->RULE_WEIGHT_EXTREME,   // Multiple 5xx errors
            'B05'   => $this->RULE_WEIGHT_EXTREME,   // Multiple 4xx errors
            'B06'   => $this->RULE_WEIGHT_EXTREME,   // Potentially vulnerable URL
            'B18'   => $this->RULE_WEIGHT_EXTREME,   // HEAD request
            'D03'   => $this->RULE_WEIGHT_EXTREME,   // Device is bot
            'D10'   => $this->RULE_WEIGHT_EXTREME,   // Potentially vulnerable User-Agent
            'I03'   => $this->RULE_WEIGHT_EXTREME,   // IP appears in spam list
        ];

        $this->DEFAULT_RULES_DORMANT_ACCOUNT = [
            // Extreme
            'B09'   => $this->RULE_WEIGHT_EXTREME,   // Dormant account (90 days)
        ];

        $this->DEFAULT_RULES_MULTI_ACCOUNTING = [
            // Medium
            'D07'   => $this->RULE_WEIGHT_MEDIUM,    // Several desktop devices
            'D08'   => $this->RULE_WEIGHT_MEDIUM,    // Two or more phone devices
            'I09'   => $this->RULE_WEIGHT_MEDIUM,    // Numerous IPs
            // High
            'D06'   => $this->RULE_WEIGHT_HIGH,      // Multiple devices per user
            'B22'   => $this->RULE_WEIGHT_HIGH,      // Multiple IP addresses in one session
            // Extreme
            'I04'   => $this->RULE_WEIGHT_EXTREME,   // Shared IP
            'P03'   => $this->RULE_WEIGHT_EXTREME,   // Shared phone number
            'R01'   => $this->RULE_WEIGHT_EXTREME,   // IP in blacklist
            'R02'   => $this->RULE_WEIGHT_EXTREME,   // Email in blacklist
            'R03'   => $this->RULE_WEIGHT_EXTREME,   // Phone in blacklist
        ];

        $this->DEFAULT_RULES_PROMO_ABUSE = [
            // Medium
            'E06'   => $this->RULE_WEIGHT_MEDIUM,    // Consecutive digits in email
            // High
            'B12'   => $this->RULE_WEIGHT_HIGH,      // New account (1 week)
            'D06'   => $this->RULE_WEIGHT_HIGH,      // Multiple devices per user
            'E03'   => $this->RULE_WEIGHT_HIGH,      // Suspicious words in email
            'E04'   => $this->RULE_WEIGHT_HIGH,      // Numeric email name
            'I02'   => $this->RULE_WEIGHT_HIGH,      // IP hosting domain
            'I05'   => $this->RULE_WEIGHT_HIGH,      // IP belongs to commercial VPN
            'I06'   => $this->RULE_WEIGHT_HIGH,      // IP belongs to datacenter
            // Extreme
            'I04'   => $this->RULE_WEIGHT_EXTREME,   // Shared IP
            'P03'   => $this->RULE_WEIGHT_EXTREME,   // Shared phone number
            'R01'   => $this->RULE_WEIGHT_EXTREME,   // IP in blacklist
            'R02'   => $this->RULE_WEIGHT_EXTREME,   // Email in blacklist
        ];

        $this->DEFAULT_RULES_API_PROTECTION = [
            // Medium
            'B24'   => $this->RULE_WEIGHT_MEDIUM,    // Empty referer
            // High
            'D01'   => $this->RULE_WEIGHT_HIGH,      // Device is unknown
            // Extreme
            'B04'   => $this->RULE_WEIGHT_EXTREME,   // Multiple 5xx errors
            'B05'   => $this->RULE_WEIGHT_EXTREME,   // Multiple 4xx errors
            'B06'   => $this->RULE_WEIGHT_EXTREME,   // Potentially vulnerable URL
            'B18'   => $this->RULE_WEIGHT_EXTREME,   // HEAD request
            'D03'   => $this->RULE_WEIGHT_EXTREME,   // Device is bot
            'D10'   => $this->RULE_WEIGHT_EXTREME,   // Potentially vulnerable User-Agent
            'I01'   => $this->RULE_WEIGHT_EXTREME,   // IP belongs to TOR
            'R01'   => $this->RULE_WEIGHT_EXTREME,   // IP in blacklist
        ];

        $this->DEFAULT_RULES_HIGH_RISK_REGIONS = [
            // High
            'C01'   => $this->RULE_WEIGHT_HIGH,      // Nigeria IP address
            'C03'   => $this->RULE_WEIGHT_HIGH,      // China IP address
            'C11'   => $this->RULE_WEIGHT_HIGH,      // Russia IP address
        ];

        $this->RULES_PRESETS['account_takeover']['main']        = $this->DEFAULT_RULES_ACCOUNT_TAKEOVER;
        $this->RULES_PRESETS['credential_stuffing']['main']     = $this->DEFAULT_RULES_CREDENTIAL_STUFFING;
        $this->RULES_PRESETS['content_spam']['main']            = $this->DEFAULT_RULES_CONTENT_SPAM;
        $this->RULES_PRESETS['account_registration']['main']    = $this->DEFAULT_RULES_ACCOUNT_REGISTRATION;
        $this->RULES_PRESETS['fraud_prevention']['main']        = $this->DEFAULT_RULES_FRAUD_PREVENTION;
        $this->RULES_PRESETS['insider_threat']['main']          = $this->DEFAULT_RULES_INSIDER_THREAT;
        $this->RULES_PRESETS['bot_detection']['main']           = $this->DEFAULT_RULES_BOT_DETECTION;
        $this->RULES_PRESETS['dormant_account']['main']         = $this->DEFAULT_RULES_DORMANT_ACCOUNT;
        $this->RULES_PRESETS['multi_accounting']['main']        = $this->DEFAULT_RULES_MULTI_ACCOUNTING;
        $this->RULES_PRESETS['promo_abuse']['main']             = $this->DEFAULT_RULES_PROMO_ABUSE;
        $this->RULES_PRESETS['api_protection']['main']          = $this->DEFAULT_RULES_API_PROTECTION;
        $this->RULES_PRESETS['high_risk_regions']['main']       = $this->DEFAULT_RULES_HIGH_RISK_REGIONS;
    }

    protected array $RULES_PRESETS = [
        'default' => [
            'description'   => 'Default empty rules',
            'main'          => [],
            'additional'    => [],
        ],
        'account_takeover' => [
            'description'   => 'Account takeover',
            'main'          => [],
            'additional'    => [],
        ],
        'credential_stuffing' => [
            'description'   => 'Credential stuffing',
            'main'          => [],
            'additional'    => [],
        ],
        'content_spam' => [
            'description'   => 'Content spam',
            'main'          => [],
            'additional'    => [],
        ],
        'account_registration' => [
            'description'   => 'Account registration',
            'main'          => [],
            'additional'    => [],
        ],
        'fraud_prevention' => [
            'description'   => 'Fraud prevention',
            'main'          => [],
            'additional'    => [],
        ],
        'insider_threat' => [
            'description'   => 'Insider threat',
            'main'          => [],
            'additional'    => [],
        ],
        'bot_detection' => [
            'description'   => 'Bot detection',
            'main'          => [],
            'additional'    => [],
        ],
        'dormant_account' => [
            'description'   => 'Dormant account',
            'main'          => [],
            'additional'    => [],
        ],
        'multi_accounting' => [
            'description'   => 'Multi-accounting',
            'main'          => [],
            'additional'    => [],
        ],
        'promo_abuse' => [
            'description'   => 'Promo abuse',
            'main'          => [],
            'additional'    => [],
        ],
        'api_protection' => [
            'description'   => 'API protection',
            'main'          => [],
            'additional'    => [],
        ],
        'high_risk_regions' => [
            'description'   => 'High-risk regions',
            'main'          => [],
            'additional'    => [],
        ],
    ];

    protected array $DEFAULT_RULES_ACCOUNT_TAKEOVER     = [];
    protected array $DEFAULT_RULES_CREDENTIAL_STUFFING  = [];
    protected array $DEFAULT_RULES_CONTENT_SPAM         = [];
    protected array $DEFAULT_RULES_ACCOUNT_REGISTRATION = [];
    protected array $DEFAULT_RULES_FRAUD_PREVENTION     = [];
    protected array $DEFAULT_RULES_INSIDER_THREAT       = [];
    protected array $DEFAULT_RULES_BOT_DETECTION        = [];
    protected array $DEFAULT_RULES_DORMANT_ACCOUNT      = [];
    protected array $DEFAULT_RULES_MULTI_ACCOUNTING     = [];
    protected array $DEFAULT_RULES_PROMO_ABUSE          = [];
    protected array $DEFAULT_RULES_API_PROTECTION       = [];
    protected array $DEFAULT_RULES_HIGH_RISK_REGIONS    = [];
}
