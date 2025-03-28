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

class Variables {
    private static function getF3(): \Base {
        return \Base::instance();
    }

    public static function getDB(): string {
        return getenv('DATABASE_URL') ?: self::getF3()->get('DATABASE_URL');
    }

    public static function getConfigFile(): string {
        return getenv('CONFIG_FILE') ?: 'config.local.ini';
    }

    public static function getSite(): string {
        return getenv('SITE') ?: self::getF3()->get('SITE');
    }

    public static function getAdminEmail(): ?string {
        return getenv('ADMIN_EMAIL') ?: self::getF3()->get('ADMIN_EMAIL');
    }

    public static function getMailLogin(): ?string {
        return getenv('MAIL_LOGIN') ?: self::getF3()->get('MAIL_LOGIN');
    }

    public static function getMailPassword(): ?string {
        return getenv('MAIL_PASS') ?: self::getF3()->get('MAIL_PASS');
    }

    public static function getEnrichtmentApi(): string {
        return getenv('ENRICHMENT_API') ?: self::getF3()->get('ENRICHMENT_API');
    }

    public static function getPepper(): string {
        return getenv('PEPPER') ?: self::getF3()->get('PEPPER');
    }

    public static function getForceHttps(): bool {
        // set 'false' string if FORCE_HTTPS wasn't set due to filter_var() issues
        $variable = getenv('FORCE_HTTPS') ?: self::getF3()->get('FORCE_HTTPS') ?? 'false';

        return filter_var($variable, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
    }

    public static function getSiteWithProtocol(): string {
        return (self::getForceHttps() ? 'https://' : 'http://') . self::getSite();
    }

    public static function getAccountOperationQueueBatchSize(): int {
        return getenv('ACCOUNT_OPERATION_QUEUE_BATCH_SIZE') ? intval(getenv('ACCOUNT_OPERATION_QUEUE_BATCH_SIZE')) : \Utils\Constants::get('ACCOUNT_OPERATION_QUEUE_BATCH_SIZE');
    }

    public static function getNewEventsBatchSize(): int {
        return getenv('NEW_EVENTS_BATCH_SIZE') ? intval(getenv('NEW_EVENTS_BATCH_SIZE')) : \Utils\Constants::get('NEW_EVENTS_BATCH_SIZE');
    }

    public static function getRuleUsersBatchSize(): int {
        return getenv('RULE_USERS_BATCH_SIZE') ? intval(getenv('RULE_USERS_BATCH_SIZE')) : \Utils\Constants::get('RULE_USERS_BATCH_SIZE');
    }

    public static function completedConfig(): bool {
        return
            (getenv('SITE') || self::getF3()->get('SITE')) &&
            (getenv('PEPPER') || self::getF3()->get('PEPPER')) &&
            (getenv('ENRICHMENT_API') || self::getF3()->get('ENRICHMENT_API')) &&
            (getenv('DATABASE_URL') || self::getF3()->get('DATABASE_URL'));
    }
}
