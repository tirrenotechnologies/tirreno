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
spl_autoload_register(fn (string $c) => @include_once __DIR__ . '/src/' . str_replace(['Sensor\\', '\\'], ['', '/'], $c) . '.php');

$requestStartTime = new \DateTime('now', new \DateTimeZone('UTC'));

$di = new DI();
$profiler = $di->getProfiler();
$logger = $di->getLogger();
$logbookManager = $di->getLogbookManager();
$profiler->start('total');

$request = null;
try {
    $apiKeyString = $_SERVER['HTTP_API_KEY'] ?? null;
    $apiKeyDto = $logbookManager->getApiKeyDto($apiKeyString);    // GetApiKeyDto or null
    $logbookManager->setApiKeyDto($apiKeyDto);

    $request = new Request($_POST, $apiKeyString, $_SERVER['HTTP_X_REQUEST_ID'] ?? null);

    $controller = $di->getController();
    $response = $controller->index($request, $apiKeyDto);
} catch (Throwable $e) {
    if ($e instanceof PDOException && str_contains($e->getMessage(), 'connect')) {
        $logger->logError($e, 'Unable to connect to database: ' . $e->getMessage());
    } else {
        $logger->logError($e);
    }
    // get apikey
    $logbookManager->logException(
        $requestStartTime,
        $request?->body['eventTime'],
        $e->getMessage(),
    );
    $logbookManager->logIncorrectRequest(
        $request?->body ?? [],
        $e::class . ': ' . $e->getMessage(),
        $request?->traceId ?? null,
    );

    // Log profiler data and queries before exit
    $profiler->finish('total');
    $logger->logProfilerData($profiler->getData());

    http_response_code(500);
    exit;
}

$profiler->finish('total');
$logger->logProfilerData($profiler->getData());
// getapikey
$logbookManager->logRequest($requestStartTime, $request?->body['eventTime'], $response);

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
