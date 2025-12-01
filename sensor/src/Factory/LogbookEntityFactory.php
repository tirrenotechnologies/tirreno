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

namespace Sensor\Factory;

use Sensor\Entity\LogbookEntity;
use Sensor\Model\Http\ErrorResponse;
use Sensor\Model\Http\RegularResponse;
use Sensor\Model\Http\Request;
use Sensor\Model\Http\ValidationFailedResponse;
use Sensor\Repository\ApiKeyRepository;

class LogbookEntityFactory {
    public function create(
        int $apiKeyId,
        \DateTime $startedTime,
        RegularResponse|ErrorResponse $response,
    ): LogbookEntity {
        $eventId = null;
        if ($response instanceof RegularResponse) {
            $errorText = $response->validationErrors();
            $errorType = $errorText !== null
                ? LogbookEntity::ERROR_TYPE_VALIDATION_ERROR
                : LogbookEntity::ERROR_TYPE_SUCCESS
            ;
            $eventId = $response->eventId;
        } elseif ($response instanceof ValidationFailedResponse) {
            $errorType = LogbookEntity::ERROR_TYPE_CRITICAL_VALIDATION_ERROR;
            $errorText = json_encode([$response->jsonSerialize()]);
        } elseif ($response instanceof ErrorResponse) {
            $errorType = LogbookEntity::ERROR_TYPE_CRITICAL_ERROR;
            $errorText = json_encode([$response->jsonSerialize()]);
        } else {
            $errorType = LogbookEntity::ERROR_TYPE_CRITICAL_ERROR;
            $errorText = json_encode(['Undefined error']);
        }

        return new LogbookEntity(
            $apiKeyId,
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            $eventId,
            $errorType,
            $errorText,
            $this->getRawRequest(),
            $this->formatStarted($startedTime),
        );
    }

    public function createFromException(
        int $apiKeyId,
        \DateTime $startedTime,
        string $errorText,
        bool $rateLimit,
    ): LogbookEntity {

        return new LogbookEntity(
            $apiKeyId,
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            null,
            $rateLimit ? LogbookEntity::ERROR_TYPE_RATE_LIMIT_EXCEEDED : LogbookEntity::ERROR_TYPE_CRITICAL_ERROR,
            $errorText,
            $this->getRawRequest(),
            $this->formatStarted($startedTime),
        );
    }

    private function getRawRequest(): string {
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $body = $_POST;
        } else {
            $long = [];
            foreach (Request::ACCEPTABLE_FIELDS as $key) {
                $long[] = $key . '::';
            }
            $body = getopt('', $long) ?: [];
        }

        return json_encode(array_intersect_key($body, array_flip(Request::ACCEPTABLE_FIELDS))) ?: json_encode($body);
    }

    private function formatStarted(\DateTime $startedTime): string {
        $milliseconds = intval(intval($startedTime->format('u')) / 1000);

        return $startedTime->format('Y-m-d H:i:s') . '.' . sprintf('%03d', $milliseconds);
    }
}
