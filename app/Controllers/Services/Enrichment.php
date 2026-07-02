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

namespace Tirreno\Controllers\Services;

class Enrichment extends \Tirreno\Controllers\Services\Base {
    public function enrichEntityFromRequest(int $apiKey): array {
        $enrichmentKey = tirreno('utils')->apiKeys->getCurrentOperatorEnrichmentKeyString();

        $type       = tirreno('utils')->conversion->getStringRequestParam('type');
        $search     = tirreno('utils')->conversion->getStringRequestParam('search', true);
        $entityId   = tirreno('utils')->conversion->getIntRequestParam('entityId', true);

        return $this->enrichEntity($type, $search, $entityId, $apiKey, $enrichmentKey);
    }

    public function enrichEntity(string $type, ?string $search, ?int $entityId, int $apiKey, ?string $enrichmentKey): array {
        if ($enrichmentKey === null) {
            return ['ERROR_CODE' => tirreno('utils')->errorCodes->ENRICHMENT_API_KEY_NOT_EXISTS];
        }
        set_error_handler([\Tirreno\Utils\ErrorHandler::class, 'exceptionErrorHandler']);
        $search = $search !== null ? ['value' => $search] : null;
        $result = $this->enrichEntityProcess($type, $search, $entityId, $apiKey, $enrichmentKey);
        restore_error_handler();

        return $result;
    }

    private function enrichEntityProcess(string $type, ?array $search, ?int $entityId, int $apiKey, ?string $enrichmentKey): array {
        $processErrorMessage = ['ERROR_CODE' => tirreno('utils')->errorCodes->ENRICHMENT_API_UNKNOWN_ERROR];

        if ($type === 'device') {
            if ($entityId !== null) {
                $device = tirreno('models')->device->getFullDeviceInfoById($entityId, $apiKey);
                if ($device !== []) {
                    $entityId = $device['ua_id'];
                    $type = 'ua';
                } else {
                    return $processErrorMessage;
                }
            } else {
                return $processErrorMessage;
            }
        }

        $attributes = tirreno('models')->apiKeys->enrichableAttributes($apiKey);

        if (!array_key_exists($type, $attributes)) {
            return ['ERROR_CODE' => tirreno('utils')->errorCodes->ENRICHMENT_API_ATTR_UNAVAILABLE];
        }

        $modelDb = null;
        $modelResult = null;
        $extraModel = null;
        switch ($type) {
            case 'ip':
                $modelDb        = tirreno('models')->ip;
                $modelResult    = new \Tirreno\Models\Enrichment\Ip();
                $extraModel     = new \Tirreno\Models\Enrichment\LocalhostIp();
                break;
            case 'email':
                $modelDb        = tirreno('models')->email;
                $modelResult    = new \Tirreno\Models\Enrichment\Email();
                break;
            case 'domain':
                $modelDb        = tirreno('models')->domain;
                $modelResult    = new \Tirreno\Models\Enrichment\DomainFound();
                $extraModel     = new \Tirreno\Models\Enrichment\DomainNotFound();
                break;
            case 'phone':
                $modelDb        = tirreno('models')->phone;
                $modelResult    = new \Tirreno\Models\Enrichment\PhoneValid();
                $extraModel     = new \Tirreno\Models\Enrichment\PhoneInvalid();
                break;
            case 'ua':
                $modelDb        = tirreno('models')->device;
                $modelResult    = new \Tirreno\Models\Enrichment\Device();
                break;
        }

        if ($modelDb === null) {
            return $processErrorMessage;
        }

        $value = $entityId !== null ? $modelDb->extractById($entityId, $apiKey) : $search;

        if ($value === null || $value === []) {
            return $processErrorMessage;
        }

        $apiError = null;

        try {
            [$statusCode, $response,] = $this->enrichEntityByValue($type, $value, $enrichmentKey, $apiKey);
            $error = tirreno('utils')->responseFormats->getErrorResponseFormat();
            $apiError = tirreno('utils')->responseFormats->matchResponse($response[$type] ?? [], $error) ? $response[$type]['error'] : null;
        } catch (\ErrorException $e) {
            return $processErrorMessage;
        }

        if ($statusCode === 403) {
            return ['ERROR_CODE' => tirreno('utils')->errorCodes->ENRICHMENT_API_KEY_OVERUSE];
        }

        if ($type === 'ip') {
            // do not raise on bogon ip
            if ($apiError === tirreno('utils')->constants->ENRICHMENT_IP_IS_NOT_FOUND) {
                return ['ERROR_CODE' => tirreno('utils')->errorCodes->ENRICHMENT_API_IP_NOT_FOUND];
            } elseif ($apiError !== null && $apiError !== tirreno('utils')->constants->ENRICHMENT_IP_IS_BOGON || $statusCode !== 200 || $response[$type] === null) {
                return $processErrorMessage;
            }
        } elseif ($apiError !== null || $statusCode !== 200 || $response[$type] === null) {
            return $processErrorMessage;
        }

        try {
            $modelResult->init($response[$type]);
        } catch (\ErrorException $e) {
            if ($extraModel === null) {
                return $processErrorMessage;
            }
            try {
                $extraModel->init($response[$type]);
                $modelResult = $extraModel;
            } catch (\ErrorException $e) {
                return $processErrorMessage;
            }
        }

        // change value in db only if $entityId was passed
        if ($entityId !== null) {
            try {
                $modelResult->updateEntityInDb($entityId, $apiKey);
            } catch (\ErrorException $e) {
                return $processErrorMessage;
            }
        }

        return [
            $type             => $response[$type],
            'SUCCESS_MESSAGE' => tirreno('storage')->get('enrichment_success_message'),
        ];
    }

    private function validateResponse(string $requestType, int $statusCode, ?array $result, string $errorMessage): bool|string|int {
        if (!is_array($result)) {
            return tirreno('utils')->errorCodes->ENRICHMENT_API_UNKNOWN_ERROR;
        }

        if ($statusCode === 200 && is_array($result[$requestType])) {
            return false;
        }

        $details = $result['detail'] ?? null;
        if ($details) {
            if (is_array($details)) {
                $messages = array_map(function ($detail) {
                    if (isset($detail['msg']) && $detail['msg'] !== '') {
                        return $detail['msg'];
                    }
                }, $details);
                $messages = implode('; ', $messages);
            } else {
                $messages = $details;
            }
        }

        if (strlen($errorMessage) > 0) {
            tirreno('utils')->logger->log('Enrichment API web error', $errorMessage);
        }

        if (!isset($messages) || strlen($messages) < 1) {
            return tirreno('utils')->errorCodes->ENRICHMENT_API_UNKNOWN_ERROR;
        }

        return $messages;
    }

    private function enrichEntityByValue(string $type, array $value, string $enrichmentKey, int $apiKey): array {
        $postFields = [
            $type => $value['value'],
        ];
        $response = tirreno('utils')->network->sendApiRequest($postFields, '/query', 'POST', $enrichmentKey, $apiKey);
        $code = $response->code;
        $result = $response->body;

        $statusCode = $code ?? 0;
        $errorMessage = $response->error ?? '';
        $errorMessages = $this->validateResponse($type, $statusCode, $result, $errorMessage);

        return [$statusCode, $result, $errorMessages];
    }

    public function getNotCheckedEntitiesCount(int $apiKey): array {
        $models = tirreno('models')->apiKeys->enrichableAttributes($apiKey);
        $result = [];

        foreach ($models as $type => $model) {
            $result[$type] = (new $model())->countNotChecked($apiKey);
        }

        return $result;
    }

    public function getNotCheckedExists(int $apiKey): bool {
        $models = tirreno('models')->apiKeys->enrichableAttributes($apiKey);

        foreach ($models as $model) {
            if ((new $model())->notCheckedExists($apiKey)) {
                return true;
            }
        }

        return false;
    }

    public function getNotCheckedEntitiesByUserId(int $userId, int $apiKey): array {
        $models = tirreno('models')->apiKeys->enrichableAttributes($apiKey);
        $result = [];

        foreach ($models as $type => $model) {
            $result[$type] = (new $model())->notCheckedForUserId($userId, $apiKey);
        }

        return $result;
    }
}
