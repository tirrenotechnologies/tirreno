<?php

/**
 * tirreno ~ open security analytics
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

namespace Utils;

class ErrorCodes {
    public const EVERYTHING_IS_FINE = 600;
    public const CSRF_ATTACK_DETECTED = 601;

    //Signup
    public const EMAIL_DOES_NOT_EXIST = 602;
    public const EMAIL_IS_NOT_CORRECT = 603;
    public const EMAIL_ALREADY_EXIST = 604;
    public const PASSWORD_DOES_NOT_EXIST = 605;
    public const PASSWORD_IS_TOO_SHORT = 606;
    public const ACCOUNT_CREATED = 607;
    public const INSTALL_DIR_EXISTS = 608;

    //Activation
    public const ACTIVATION_KEY_DOES_NOT_EXIST = 610;
    public const ACTIVATION_KEY_IS_NOT_CORRECT = 611;

    //Login
    public const EMAIL_OR_PASSWORD_IS_NOT_CORRECT = 620;

    //Api
    public const API_KEY_ID_DOESNT_EXIST = 630;
    public const API_KEY_ID_INVALID = 631;
    public const OPERATOR_ID_DOES_NOT_EXIST = 632;
    public const OPERATOR_IS_NOT_A_CO_OWNER = 633;
    public const UNKNOWN_ENRICHMENT_ATTRIBUTES = 634;
    public const INVALID_API_RESPONSE = 635;

    //Profile
    public const FIRST_NAME_DOES_NOT_EXIST = 640;
    public const LAST_NAME_DOES_NOT_EXIST = 641;
    public const COUNTRY_DOES_NOT_EXIST = 642;
    public const STREET_DOES_NOT_EXIST = 643;
    public const CITY_DOES_NOT_EXIST = 644;
    public const STATE_DOES_NOT_EXIST = 645;
    public const ZIP_DOES_NOT_EXIST = 646;
    public const TIME_ZONE_DOES_NOT_EXIST = 647;
    public const RETENTION_POLICY_DOES_NOT_EXIST = 648;
    public const INVALID_REMINDER_FREQUENCY = 649;

    //Settings
    public const CURRENT_PASSWORD_DOES_NOT_EXIST = 650;
    public const CURRENT_PASSWORD_IS_NOT_CORRECT = 651;
    public const NEW_PASSWORD_DOES_NOT_EXIST = 652;
    public const PASSWORD_CONFIRMATION_MISSING = 653;
    public const PASSWORDS_ARE_NOT_EQUAL = 654;
    public const EMAIL_IS_NOT_NEW = 655;

    //Password recovering
    public const RENEW_KEY_CREATED = 660;
    public const RENEW_KEY_DOES_NOT_EXIST = 661;
    public const RENEW_KEY_IS_NOT_CORRECT = 662;
    public const RENEW_KEY_WAS_EXPIRED = 663;
    public const ACCOUNT_ACTIVATED = 664;

    //Account messages
    public const THERE_ARE_NO_EVENTS_YET = 670;
    public const THERE_ARE_NO_EVENTS_LAST_DAY = 671;
    public const CUSTOM_ERROR_FROM_DSHB_MESSAGES = 672;

    //Watchlist
    public const USER_ADDED_TO_WATCHLIST = 681;
    public const USER_REMOVED_FROM_WATCHLIST = 682;
    public const USER_FRAUD_FLAG_SET = 683;
    public const USER_FRAUD_FLAG_UNSET = 684;
    public const USER_REVIEWED_FLAG_SET = 685;
    public const USER_REVIEWED_FLAG_UNSET = 686;
    public const USER_DELETION_FAILED = 687;
    public const USER_BLACKLISTING_FAILED = 688;
    public const USER_BLACKLISTING_QUEUED = 689;

    //Change email
    public const EMAIL_CHANGED = 690;
    public const CHANGE_EMAIL_KEY_DOES_NOT_EXIST = 691;
    public const CHANGE_EMAIL_KEY_IS_NOT_CORRECT = 692;
    public const CHANGE_EMAIL_KEY_WAS_EXPIRED = 693;

    //Rules
    public const RULES_SUCCESSFULLY_UPDATED = 800;
    public const INVALID_BLACKLIST_THRESHOLD = 801;
    public const INVALID_REVIEW_QUEUE_THRESHOLD = 802;
    public const INVALID_THRESHOLDS_COMBINATION = 803;

    // REST API
    public const REST_API_KEY_DOES_NOT_EXIST = 900;
    public const REST_API_KEY_IS_NOT_CORRECT = 901;
    public const REST_API_NOT_AUTHORIZED = 902;
    public const REST_API_MISSING_PARAMETER = 903;
    public const REST_API_VALIDATION_ERROR = 904;
    public const REST_API_USER_ALREADY_DELETING = 905;
    public const REST_API_USER_ADDED_FOR_DELETION = 906;

    // Manual check
    public const ENRICHMENT_API_KEY_NOT_EXISTS = 1000;
    public const TYPE_DOES_NOT_EXIST = 1001;
    public const SEARCH_QUERY_DOES_NOT_EXIST = 1002;
    public const ENRICHMENT_API_UNKNOWN_ERROR = 1003;
    public const ENRICHMENT_API_BOGON_IP = 1004;
    public const ENRICHMENT_API_IP_NOT_FOUND = 1005;
    public const RISK_SCORE_UPDATE_UNKNOWN_ERROR = 1006;
    public const ENRICHMENT_API_KEY_OVERUSE = 1007;
    public const ENRICHMENT_API_ATTR_UNAVAILABLE = 1008;
    public const ENRICHMENT_API_IS_NOT_AVAILABLE = 1009;

    //Blacklist
    public const ITEM_REMOVED_FROM_BLACKLIST = 1010;
    public const ITEM_REMOVE_FAIL_FROM_BLACKLIST = 1011;

    //Subscription
    public const SUBSCRIPTION_KEY_INVALID_UPDATE = 1100;

    // Totals
    public const TOTALS_INVALID_TYPE = 1200;

    // Crons
    public const CRON_JOB_MAY_BE_OFF = 1300;
}
