<?php

/**
 * Tirreno ~ Open source user analytics
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

namespace Utils;

class Constants {
    public static function get(string $key): array|string {
        $const = __CLASS__ . '::' . $key;
        if (!defined($const)) {
            trigger_error('Undefined constant: ' . $key, E_USER_ERROR);
        }

        $value = constant($const);

        $f3 = \Base::instance();
        $f3key = 'EXTRA_' . $key;
        if ($f3->exists($f3key)) {
            $value = is_array($value) ? array_merge($value, $f3->get($f3key)) : $f3->get($f3key);
        }

        return $value;
    }

    // TODO: rewrite context so event amount limit will not be needed
    public const RULE_EVENT_CONTEXT_LIMIT               = 25;
    public const RULE_CHECK_USERS_PASSED_TO_CLIENT      = 25;
    public const RULE_USERS_BATCH_SIZE                  = 3500;
    public const RULE_EMAIL_MAXIMUM_LOCAL_PART_LENGTH   = 17;
    public const RULE_EMAIL_MAXIMUM_DOMAIN_LENGTH       = 22;
    public const RULE_MAXIMUM_NUMBER_OF_404_CODES       = 4;
    public const RULE_MAXIMUM_NUMBER_OF_500_CODES       = 4;
    public const RULE_MAXIMUM_NUMBER_OF_LOGIN_ATTEMPTS  = 3;
    public const RULE_LOGIN_ATTEMPTS_WINDOW             = 8;
    public const RULE_NEW_DEVICE_MAX_AGE_IN_MINUTES     = 60 * 3;
    public const RULE_REGULAR_OS_NAMES                  = ['Windows', 'Android', 'Mac', 'iOS'];
    public const RULE_REGULAR_BROWSER_NAMES             = [
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

    public const LOGBOOK_LIMIT = 1000;

    public const NIGHT_RANGE_SECONDS_START  = 0;        // midnight
    public const NIGHT_RANGE_SECONDS_END    = 18000;    // 5 AM

    public const COUNTRY_CODE_NIGERIA       = 160;
    public const COUNTRY_CODE_INDIA         = 104;
    public const COUNTRY_CODE_CHINA         = 47;
    public const COUNTRY_CODE_BRAZIL        = 31;
    public const COUNTRY_CODE_PAKISTAN      = 168;
    public const COUNTRY_CODE_INDONESIA     = 105;
    public const COUNTRY_CODE_VENEZUELA     = 243;
    public const COUNTRY_CODE_SOUTH_AFRICA  = 199;
    public const COUNTRY_CODE_PHILIPPINES   = 175;
    public const COUNTRY_CODE_ROMANIA       = 182;
    public const COUNTRY_CODE_RUSSIA        = 183;
    public const COUNTRY_CODE_AUSTRALIA     = 14;
    public const COUNTRY_CODE_UAE           = 236;
    public const COUNTRY_CODE_JAPAN         = 113;

    public const COUNTRY_CODES_NORTH_AMERICA    = [238, 40];
    public const COUNTRY_CODES_EUROPE           = [77, 2, 15, 22, 35, 57, 60, 61, 62, 71, 78, 85, 88, 102, 108, 111, 122, 128, 129, 136, 155, 177, 178, 182, 195, 196, 203, 215];

    public const EVENT_TYPE_ID_ACCOUNT_LOGIN_FAIL       = 7;
    public const EVENT_TYPE_ID_ACCOUNT_EMAIL_CHANGE     = 9;
    public const EVENT_TYPE_ID_ACCOUNT_PASSWORD_CHANGE  = 10;

    public const EVENT_REQUEST_TYPE_HEAD    = 3;

    public const ACCOUNT_OPERATION_QUEUE_CLEAR_COMPLETED_AFTER_DAYS = 7;
    public const ACCOUNT_OPERATION_QUEUE_AUTO_UNCLOG_AFTER_MINUTES  = 60 * 2;
    public const ACCOUNT_OPERATION_QUEUE_EXECUTE_TIME_SEC           = 60 * 3;
    public const ACCOUNT_OPERATION_QUEUE_BATCH_SIZE                 = 2500;
    public const NEW_EVENTS_BATCH_SIZE                              = 15000;

    public const USER_LOW_SCORE_INF     = 0;
    public const USER_LOW_SCORE_SUP     = 33;
    public const USER_MEDIUM_SCORE_INF  = 33;
    public const USER_MEDIUM_SCORE_SUP  = 67;
    public const USER_HIGH_SCORE_INF    = 67;

    public const ENRICHMENT_IP_IS_BOGON     = 'IP is bogon';
    public const ENRICHMENT_IP_IS_NOT_FOUND = 'Value is not found';

    public const MAIL_FROM_NAME = 'Analytics';
    public const MAIL_HOST      = 'smtp.eu.mailgun.org';
    public const MAIL_SEND_BIN  = '/usr/sbin/sendmail';

    public const PAGE_TITLE_POSTFIX = '| tirreno';

    public const DEFAULT_RULES = [
        // Positive
        'E20'  => -20,
        'E23'  => -20,
        'E24'  => -20,
        'E25'  => -20,
        'I07'  => -20,
        'I08'  => -20,
        'I10'  => -20,
        // Medium
        'B01'  => 10,
        'B04'  => 10,
        'B05'  => 10,
        'B07'  => 10,
        'C01'  => 10,
        'C02'  => 10,
        'C03'  => 10,
        'C04'  => 10,
        'C05'  => 10,
        'C06'  => 10,
        'C07'  => 10,
        'C08'  => 10,
        'C09'  => 10,
        'C10'  => 10,
        'C11'  => 10,
        'D04'  => 10,
        'D08'  => 10,
        'E06'  => 10,
        'E07'  => 10,
        'E08'  => 10,
        'E09'  => 10,
        'E10'  => 10,
        'E12'  => 10,
        'E15'  => 10,
        //'E18'  => 10,
        'E21'  => 10,
        'E22'  => 10,
        'I05'  => 10,
        'I06'  => 10,
        'I09'  => 10,
        'P01'  => 10,
        // High
        'D01'  => 20,
        'D02'  => 20,
        'D03'  => 20,
        'D05'  => 20,
        'D06'  => 20,
        'D07'  => 20,
        'E03'  => 20,
        'E04'  => 20,
        'E05'  => 20,
        'E16'  => 20,
        'I02'  => 20,
        'I03'  => 20,
        'I04'  => 20,
        'P03'  => 20,
        // Extreme
        'B06'  => 70,
        'E01'  => 70,
        'E02'  => 70,
        'E11'  => 70,
        'E13'  => 70,
        'E14'  => 70,
        'E17'  => 70,
        'E19'  => 70,
        'I01'  => 70,
        'R01'  => 70,
        'R02'  => 70,
        'R03'  => 70,
    ];

    public const CHART_MODEL_MAP = [
        'resources'     => \Models\Chart\Resources::class,
        'resource'      => \Models\Chart\Resource::class,
        'users'         => \Models\Chart\Users::class,
        'user'          => \Models\Chart\User::class,
        'isps'          => \Models\Chart\Isps::class,
        'isp'           => \Models\Chart\Isp::class,
        'ips'           => \Models\Chart\Ips::class,
        'ip'            => \Models\Chart\Ip::class,
        'domains'       => \Models\Chart\Domains::class,
        'domain'        => \Models\Chart\Domain::class,
        'bots'          => \Models\Chart\Bots::class,
        'bot'           => \Models\Chart\Bot::class,
        'events'        => \Models\Chart\Events::class,
        'emails'        => \Models\Chart\Emails::class,
        'phones'        => \Models\Chart\Phones::class,
        'review-queue'  => \Models\Chart\ReviewQueue::class,
        'country'       => \Models\Chart\Country::class,
        'blacklist'     => \Models\Chart\Blacklist::class,
        'logbook'       => \Models\Chart\Logbook::class,
    ];

    public const LINE_CHARTS = [
        'ips',
        'users',
        'review-queue',
        'events',
        'phones',
        'emails',
        'resources',
        'bots',
        'isps',
        'domains',
        'blacklist',
        'logbook'
    ];

    public const CHART_RESOLUTION = [
        'day'       => 60 * 60 * 24,
        'hour'      => 60 * 60,
        'minute'    => 60,
    ];

    public const TOP_TEN_MODELS_MAP = [
        'mostActiveUsers'           => \Models\TopTen\UsersByEvents::class,
        'mostActiveCountries'       => \Models\TopTen\CountriesByUsers::class,
        'mostActiveUrls'            => \Models\TopTen\ResourcesByUsers::class,
        'ipsWithTheMostUsers'       => \Models\TopTen\IpsByUsers::class,
        'usersWithMostLoginFail'    => \Models\TopTen\UsersByLoginFail::class,
        'usersWithMostIps'          => \Models\TopTen\UsersByIps::class,
    ];

    public const RULES_TOTALS_MODELS = [
        \Models\Phone::class,
        \Models\Ip::class,
        \Models\Session::class,
        \Models\User::class,
    ];

    public const REST_TOTALS_MODELS = [
        'isp'       => \Models\Isp::class,
        'resource'  => \Models\Resource::class,
        'domain'    => \Models\Domain::class,
        'device'    => \Models\Device::class,
        'country'   => \Models\Country::class,
    ];

    public const ENRICHING_ATTRIBUTES = [
        'ip'        => \Models\Ip::class,
        'email'     => \Models\Email::class,
        'domain'    => \Models\Domain::class,
        'phone'     => \Models\Phone::class,
        //'ua'        => \Models\Device::class,
    ];

    public const ADMIN_PAGES = [
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
        'AdminBots',
        'AdminBot',
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
    ];

    public const IP_TYPES = [
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

    public const ALERT_EVENT_TYPES = [
        'page_delete',
        'page_error',
        'account_login_fail',
        'account_email_change',
        'account_password_change',
    ];

    public const EDITING_EVENT_TYPES = [
        'page_edit',
        'account_registration',
        'account_edit',
    ];

    public const NORMAL_EVENT_TYPES = [
        'page_view',
        'page_search',
        'account_login',
        'account_logout',
    ];

    public const FAILED_LOGBOOK_EVENT_TYPES = [
        'critical_validation_error',
        'critical_error',
    ];

    public const ISSUED_LOGBOOK_EVENT_TYPES = [
        'validation_error',
    ];

    public const NORMAL_LOGBOOK_EVENT_TYPES = [
        'success',
    ];

    public const ENTITY_TYPES = [
        'IP',
        'Email',
        'Phone',
    ];
}
