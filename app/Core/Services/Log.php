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

namespace Tirreno\Core\Services;

class Log {
    private function logObj(string $logFileVar = 'LOG_FILE'): \Log {
        return new \Log(tirreno('storage')->get($logFileVar));
    }

    public function log(?string $title, string|array $message): void {
        $logger = $this->logObj();

        if (is_array($message)) {
            $message = var_export($message, true);
        }

        if ($title) {
            $message = $this->logLine($title, $message);
        }

        $logger->write($message);
    }

    public function logSql(string $title, string $message): void {
        $logger = $this->logObj('LOG_SQL_FILE');
        $logDelim = tirreno('storage')->get('LOG_DELIMITER');
        $logger->write($this->logLine($title, $message, $logDelim));
    }

    public function logSqlIfPossible(): void {
        $printSqlToLog = tirreno('storage')->get('PRINT_SQL_LOG_AFTER_EACH_SCRIPT_CALL');
        if ($printSqlToLog) {
            $path = tirreno('request')->getPath();

            $log = tirreno('utils')->database->getDb()->log();
            if ($log) {
                $this->logSql($path, $log);
            }
        }
    }

    public function debug(string $msg, mixed ...$args): void {
        if (!tirreno('utils')->variables->getDebug()) {
            return;
        }

        $msg = $this->logLine('DEBUG', sprintf($msg, ...$args));
        $this->logObj()->write($msg);

        if (tirreno('utils')->variables->getLogToStderr()) {
            error_log($msg);
        }
    }

    public function info(string $msg, mixed ...$args): void {
        $msg = $this->logLine('INFO', sprintf($msg, ...$args));
        $this->logObj()->write($msg);

        if (tirreno('utils')->variables->getLogToStderr()) {
            error_log($msg);
        }
    }

    public function error(string $msg, mixed ...$args): void {
        $msg = $this->logLine('ERROR', sprintf($msg, ...$args));
        $this->logObj()->write($msg);

        error_log($msg);
    }

    public function logbookRequest(
        string $endpoint,
        ?string $started,
        ?string $ip,
        ?int $eventId,
        ?string $errorText,
        ?string $raw,
        int $apiKey,
        int $errorType = 0,
        ?string $ended = null,
    ): void {
        tirreno('entities')->logbook->addRecord($endpoint, $started, $ip, $eventId, $errorText, $raw, $apiKey, $errorType, $ended);
    }

    private function logLine(string $title, string $message, string $delim = ''): string {
        return '[' . getmypid() . '] ' . $title . ': ' . $message . $delim;
    }
}
