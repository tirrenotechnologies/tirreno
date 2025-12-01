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

use Sensor\Exception\RateLimitException;
use Sensor\Model\Http\RegularResponse;
use Sensor\Model\Http\Request;
use Sensor\Service\DI;

ini_set('display_errors', '0');

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
} else {
    require __DIR__ . '/../libs/mustangostang/spyc/Spyc.php';
    require __DIR__ . '/../libs/matomo/device-detector/autoload.php';
}

// Register autoloader
spl_autoload_register(function (string $className): void {
    require_once __DIR__ . '/src/' . str_replace(['Sensor\\', '\\'], ['', '/'], $className) . '.php';
});

$requestStartTime = new \DateTime('now');

$di = null;

try {
    $di = new DI();
} catch (Throwable $e) {
    if (str_contains($e->getMessage(), 'DATABASE_URL') || str_contains($e->getMessage(), 'SQLSTATE[08006]')) {
        http_response_code(503);
        exit;
    }

    error_log($e->getMessage());
    http_response_code(500);
    exit;
}

$profiler = $di->getProfiler();
$logger = $di->getLogger();
$logbookManager = $di->getLogbookManager();

$profiler->start('total');

$request = null;
try {
    $isWeb = isset($_SERVER['REQUEST_METHOD']);

    $body = [];
    if (isset($_SERVER['REQUEST_METHOD'])) {
        $body = $_POST;
    } else {
        $body = $argv;
    }

    $apiKeyString = $isWeb ? ($_SERVER['HTTP_API_KEY'] ?? null) : (getopt('', ['apiKey::'])['apiKey'] ?? null);
    $apiKeyDto = $logbookManager->getApiKeyDto($apiKeyString);    // GetApiKeyDto or null
    $logbookManager->setApiKeyDto($apiKeyDto);

    $request = new Request($isWeb ? $_POST : array_slice($argv, 1), $apiKeyString, $_SERVER['HTTP_X_REQUEST_ID'] ?? null, $isWeb);

    $logbookManager->checkRps();

    $controller = $di->getController();
    $response = $controller->index($request, $apiKeyDto);
} catch (Throwable $e) {
    if ($e instanceof PDOException && str_contains($e->getMessage(), 'connect')) {
        $logger->logError($e, 'Unable to connect to database: ' . $e->getMessage());
    } else {
        $logger->logError($e);
    }

    $rateLimit = $e instanceof RateLimitException;

    // get apikey
    $logbookManager->logException(
        $requestStartTime,
        $e->getMessage(),
        $rateLimit,
    );

    if (!$rateLimit) {
        $logbookManager->logIncorrectRequest(
            $request?->body ?? [],
            $e::class . ': ' . $e->getMessage(),
            $request?->traceId ?? null,
        );
    }

    // Log profiler data and queries before exit
    $profiler->finish('total');
    $logger->logProfilerData($profiler->getData());

    http_response_code(!$rateLimit ? 500 : 429);
    exit;
}

$profiler->finish('total');
$logger->logProfilerData($profiler->getData());
// getapikey
$logbookManager->logRequest($requestStartTime, $response);

// response without errors
if ($response instanceof RegularResponse) {
    return;
}

// Response is set only in case of error, so let's log it
$logger->logUserError($response->httpCode, (string) $response);
// getapikey
$logbookManager->logIncorrectRequest(
    $request?->body ?? [],
    (string) $response,
    $request?->traceId ?? null,
);
