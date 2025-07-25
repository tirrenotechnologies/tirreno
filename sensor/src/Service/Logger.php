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

declare(strict_types=1);

namespace Sensor\Service;

class Logger {
    /** @var array{sql: string, params: array<string, string>}[] */
    private array $queries = [];

    public function __construct(
        private bool $printDebug,
    ) {
    }

    private function fflush(string $msg, string $flow) {
        $msg .= PHP_EOL;
        $out = fopen('php://' . $flow, 'w');
        fputs($out, $msg);
        fclose($out);
    }

    public function logWarning(string $description, \Throwable $e = null): void {
        $info = $e !== null ? ': ' . $this->getDebugInfo($e) : '';
        $this->fflush(sprintf('Warning: %s %s', $description, $info), 'stdout');
    }

    public function logError(\Throwable $e, string $description = null): void {
        $this->fflush(sprintf('Error: %s', $description ?? $this->getDebugInfo($e)), 'stderr');
    }

    public function logUserError(int $httpCode, string $message): void {
        $this->fflush(sprintf('Error %d: %s', $httpCode, $message), 'stderr');
    }

    /**
     * @param array<string, float|null> $data
     */
    public function logProfilerData(array $data): void {
        return;
        $this->fflush('Profiler: ' . json_encode($data), 'stdout');
        $cnt = count($this->queries);
        if ($cnt > 0) {
            $msg = sprintf('SQL Queries [%d]:\n', $cnt);

            for ($i = 0; $i < $cnt; $i++) {
                $query = $this->queries[$i];
                $msg .= sprintf('Query [%d]: %s; params: %s', $i, $query['sql'], json_encode($query['params'])) . PHP_EOL;
            }
            $this->fflush($msg, 'stdout');
        }
    }

    public function logDebug(string $info): void {
        if ($this->printDebug) {
            $this->fflush($info, 'stdout');
        }
    }

    /**
     * @param array<string, string> $params
     */
    public function logQuery(string $query, array $params): void {
        /** @var string $query */
        $query = preg_replace('/\s+/', ' ', $query);
        $this->queries[] = ['sql' => $query, 'params' => $params];
    }

    private function getDebugInfo(\Throwable $e): string {
        return json_encode([
            'class' => $e::class,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], \JSON_THROW_ON_ERROR);
    }
}
