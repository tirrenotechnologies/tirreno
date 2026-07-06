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

class Variables {
    public static function getDB(): ?string {
        return getenv('DATABASE_URL') ?: tirreno('storage')->get('DATABASE_URL');
    }

    public static function getConfigFile(): string {
        return getenv('CONFIG_FILE') ?: 'local/config.local.ini';
    }

    public static function getHosts(): array {
        $env = getenv('SITE');
        $conf = tirreno('storage')->get('SITE');

        return $env ? explode(',', $env) : (is_array($conf) ? $conf : [$conf]);
    }

    public static function getHost(): string {
        return self::getHosts()[0];
    }

    public static function getAdminEmail(): ?string {
        return static::get('ADMIN_EMAIL');
    }

    public static function getMailLogin(): ?string {
        return static::get('MAIL_LOGIN');
    }

    public static function getMailPassword(): ?string {
        return static::get('MAIL_PASS');
    }

    public static function getEnrichmentApi(): string {
        return static::get('ENRICHMENT_API');
    }

    public static function getPepper(): string {
        return static::get('PEPPER');
    }

    public static function getLogbookLimit(): int {
        return static::getInt('LOGBOOK_LIMIT');
    }

    public static function getCheckRuleUsersLimit(): int {
        return static::getInt('CHECK_RULE_USERS_LIMIT');
    }

    public static function getRecalculateTotalsOnVisit(): bool {
        return static::getBool('RECALCULATE_TOTALS_ON_VISIT', false);
    }

    public static function getForgotPasswordAllowed(): bool {
        return static::getBool('ALLOW_FORGOT_PASSWORD', false);
    }

    public static function getEmailPhoneAllowed(): bool {
        return static::getBool('ALLOW_EMAIL_PHONE', false);
    }

    public static function getDebug(): bool {
        return static::getBool('DEBUG', false);
    }

    public static function getDebugLevel(): int {
        return tirreno('utils')->conversion->intValCheckEmpty(static::get('DEBUG'), 0) ?? 0;
    }

    public static function getLogToStderr(): bool {
        return static::getBool('LOG_TO_STDERR', false);
    }

    public static function getForceHttps(): bool {
        return static::getBool('FORCE_HTTPS', true);
    }

    public static function getHostWithProtocol(): string {
        $host = self::getHost();

        if (!str_starts_with($host, '[') && tirreno('utils')->conversion->filterIpGetType($host) === 6) {
            $host = '[' . $host . ']';
        }

        return (self::getForceHttps() ? 'https://' : 'http://') . $host;
    }

    public static function getHostWithProtocolAndBase(): string {
        return self::getHostWithProtocol() . tirreno('storage')->get('BASE');
    }

    public static function getAccountOperationQueueBatchSize(): int {
        return static::getInt('ACCOUNT_OPERATION_QUEUE_BATCH_SIZE');
    }

    public static function getNewEventsBatchSize(): int {
        return static::getInt('NEW_EVENTS_BATCH_SIZE');
    }

    public static function getRuleUsersBatchSize(): int {
        return static::getInt('RULE_USERS_BATCH_SIZE');
    }

    public static function getUserQueueEventsLimit(): int {
        return static::getInt('USER_QUEUE_EVENTS_LIMIT');
    }

    public static function getAvailableTimezones(): array {
        return array_intersect_key(tirreno('storage')->get('timezones'), array_flip(\DateTimeZone::listIdentifiers()));
    }

    public static function completedConfig(): bool {
        return
            (getenv('SITE') || tirreno('storage')->get('SITE')) &&
            (getenv('PEPPER') || tirreno('storage')->get('PEPPER')) &&
            (getenv('ENRICHMENT_API') || tirreno('storage')->get('ENRICHMENT_API')) &&
            (getenv('DATABASE_URL') || tirreno('storage')->get('DATABASE_URL'));
    }

    protected static function get(string $var, bool $useConstant = false): mixed {
        return getenv($var) ?: tirreno('storage')->get($var) ?: ($useConstant ? tirreno('utils')->constants->$var : null);
    }

    protected static function getInt(string $var): int {
        return tirreno('utils')->conversion->intValCheckEmpty(static::get($var, true), tirreno('utils')->constants->$var);
    }

    protected static function getBool(string $var, bool $default = false): bool {
        return tirreno('utils')->conversion->filterBool(static::get($var) ?? 'false') ?? $default;
    }
}
