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
            $_SERVER['REMOTE_ADDR'],
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
    ): LogbookEntity {
        return new LogbookEntity(
            $apiKeyId,
            $_SERVER['REMOTE_ADDR'],
            null,
            3,
            $errorText,
            $this->getRawRequest(),
            $this->formatStarted($startedTime),
        );
    }

    private function getRawRequest(): string {
        return json_encode(array_intersect_key($_POST, array_flip(Request::ACCEPTABLE_FIELDS)));
    }

    private function formatStarted(\DateTime $startedTime): string {
        $milliseconds = (int) ($startedTime->format('u') / 1000);

        return $startedTime->format('Y-m-d H:i:s') . '.' . sprintf('%03d', $milliseconds);
    }
}
