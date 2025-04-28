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

namespace Controllers\Admin\Enrichment;

class Data extends \Controllers\Base {
    public function enrichEntity(string $type, ?string $search, ?int $entityId, int $apiKey, ?string $subscriptionKeyString): array {
        if ($subscriptionKeyString === null) {
            return ['ERROR_CODE' => \Utils\ErrorCodes::ENRICHMENT_API_KEY_DOES_NOT_EXIST];
        }
        set_error_handler([\Utils\ErrorHandler::class, 'exceptionErrorHandler']);
        $search = $search !== null ? ['value' => $search] : null;
        $result = $this->enrichEntityProcess($type, $search, $entityId, $apiKey, $subscriptionKeyString);
        restore_error_handler();

        return $result;
    }

    private function enrichEntityProcess(string $type, ?array $search, ?int $entityId, int $apiKey, ?string $subscriptionKeyString): array {
        $processErrorMessage = ['ERROR_CODE' => \Utils\ErrorCodes::ENRICHMENT_API_UNKNOWN_ERROR];

        if ($type === 'device') {
            if ($entityId !== null) {
                $model = new \Models\Device();
                $device = $model->getFullDeviceInfoById($entityId, $apiKey);
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

        $model = new \Models\ApiKeys();
        $attributes = $model->enrichableAttributes($apiKey);

        if (!array_key_exists($type, $attributes)) {
            return ['ERROR_CODE' => \Utils\ErrorCodes::ENRICHMENT_API_ATTRIBUTE_IS_UNAVAILABLE];
        }

        $modelDb = null;
        $modelResult = null;
        $extraModel = null;
        switch ($type) {
            case 'ip':
                $modelDb        = new \Models\Ip();
                $modelResult    = new \Models\Enrichment\Ip();
                $extraModel     = new \Models\Enrichment\LocalhostIp();
                break;
            case 'email':
                $modelDb        = new \Models\Email();
                $modelResult    = new \Models\Enrichment\Email();
                break;
            case 'domain':
                $modelDb        = new \Models\Domain();
                $modelResult    = new \Models\Enrichment\DomainFound();
                $extraModel     = new \Models\Enrichment\DomainNotFound();
                break;
            case 'phone':
                $modelDb        = new \Models\Phone();
                $modelResult    = new \Models\Enrichment\PhoneValid();
                $extraModel     = new \Models\Enrichment\PhoneInvalid();
                break;
            case 'ua':
                $modelDb        = new \Models\Device();
                $modelResult    = new \Models\Enrichment\Device();
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
            [$statusCode, $response,] = $this->enrichEntityByValue($type, $value, $subscriptionKeyString);
            $error = \Utils\ApiResponseFormats::getErrorResponseFormat();
            $apiError = \Utils\ApiResponseFormats::matchResponse($response[$type] ?? [], $error) ? $response[$type]['error'] : null;
        } catch (\ErrorException $e) {
            return $processErrorMessage;
        }

        if ($statusCode === 403) {
            return ['ERROR_CODE' => \Utils\ErrorCodes::ENRICHMENT_API_KEY_OVERUSE];
        }

        if ($type === 'ip') {
            // do not raise on bogon ip
            if ($apiError === \Utils\Constants::get('ENRICHMENT_IP_IS_NOT_FOUND')) {
                return ['ERROR_CODE' => \Utils\ErrorCodes::ENRICHMENT_API_IP_NOT_FOUND];
            } elseif ($apiError !== null && $apiError !== \Utils\Constants::get('ENRICHMENT_IP_IS_BOGON')) {
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
            'SUCCESS_MESSAGE' => $this->f3->get('AdminEnrichment_success_message'),
        ];
    }

    private function validateResponse(string $requestType, int $statusCode, ?array $result, string $errorMessage): bool|string|int {
        if (!is_array($result)) {
            return \Utils\ErrorCodes::ENRICHMENT_API_UNKNOWN_ERROR;
        }

        if ($statusCode === 200 && is_array($result) && is_array($result[$requestType])) {
            return false;
        }

        $details = $result['detail'] ?? null;
        if ($details) {
            if (is_array($details)) {
                $messages = array_map(function ($detail) {
                    if (isset($detail['msg']) && $detail['msg'] !== null && $detail['msg'] !== '') {
                        return $detail['msg'];
                    }
                }, $details);
                $messages = implode('; ', $messages);
            } else {
                $messages = $details;
            }
        }

        if (strlen($errorMessage) > 0) {
            \Utils\Logger::log('Enrichment API web error', $errorMessage);
        }

        if (!isset($messages) || strlen($messages) < 1) {
            return \Utils\ErrorCodes::ENRICHMENT_API_UNKNOWN_ERROR;
        }

        return $messages;
    }

    private function enrichEntityByValue(string $type, array $value, string $subscriptionKeyString): array {
        $apiUrl = \Utils\Variables::getEnrichtmentApi();
        $postFields = [
            $type => array_key_exists('hash', $value) ? $value : $value['value'],
        ];

        $options = [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $subscriptionKeyString,
                'User-Agent: ' . $this->f3->get('USER_AGENT'),
            ],
            'content' => \json_encode($postFields),
        ];

        /** @var array{request: array<string>, body: string, headers: array<string>, engine: string, cached: bool, error: string} $result */
        $result = \Web::instance()->request(
            url: sprintf('%s/query', $apiUrl),
            options: $options,
        );

        $matches = [];
        preg_match('/^HTTP\/(\d+)(?:\.\d)? (\d{3})/', $result['headers'][0], $matches);

        $jsonResponse = json_decode($result['body'], true);
        $statusCode = (int) ($matches[2] ?? 0);

        $errorMessages = $this->validateResponse($type, $statusCode, $jsonResponse, $result['error']);

        return [$statusCode, $jsonResponse, $errorMessages];
    }

    public function getNotCheckedEntitiesCount(int $apiKey): array {
        $model = new \Models\ApiKeys();
        $models = $model->enrichableAttributes($apiKey);
        $result = [];

        foreach ($models as $type => $model) {
            $result[$type] = (new $model())->countNotChecked($apiKey);
        }

        return $result;
    }

    public function getNotCheckedExists(int $apiKey): bool {
        $model = new \Models\ApiKeys();
        $models = $model->enrichableAttributes($apiKey);

        foreach ($models as $type => $model) {
            if ((new $model())->notCheckedExists($apiKey)) {
                return true;
            }
        }

        return false;
    }

    public function getNotCheckedEntitiesByUserId(int $userId, int $apiKey): array {
        $model = new \Models\ApiKeys();
        $models = $model->enrichableAttributes($apiKey);
        $result = [];

        foreach ($models as $type => $model) {
            $result[$type] = (new $model())->notCheckedForUserId($userId, $apiKey);
        }

        return $result;
    }
}
