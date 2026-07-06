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

namespace Tirreno\Controllers\Api;

abstract class Endpoint {
    public const API_KEY = 'Api-Key';

    protected \Tirreno\Views\Json $response;
    protected string $responseType;
    protected int $error;
    protected int $statusCode;
    protected array $validationErrors = [];
    protected \DateTime $startTime;

    private string $apiKeyString;
    protected int $apiKeyId;
    //protected \Tirreno\Interfaces\ApiKeyAccessAuthorizationInterface $authorizationModel;

    protected array $body = [];

    protected array|null $data = null;

    public function __construct() {
        tirreno('storage')->set('ONERROR', function (): void {
            $this->handleInternalServerError();
        });
        tirreno('utils')->database->initConnect(false);

        $this->response = new \Tirreno\Views\Json();
        $this->responseType = tirreno('utils')->constants->SINGLE_RESPONSE_TYPE;
    }

    public function beforeRoute(): void {
        $this->startTime = new \DateTime();
        $this->identify();
        $this->authenticate();
        $this->parseBody();
    }

    public function afterRoute(): void {
        if (isset($this->error)) {
            $errorI18nCode = sprintf('error_%s', $this->error);
            $errorMessage = tirreno('storage')->get($errorI18nCode);
            $this->response->data = [
                'code' => $this->error,
                'message' => $errorMessage,
            ];
            $this->data = null;
        }

        if (!isset($this->error) || !isset($this->statusCode) || (!in_array($this->statusCode, [400, 401, 403]))) {
            $this->saveLogbook();
        }

        if (($this->data !== null)) {
            $this->response->data = $this->data;
        }

        echo $this->response->render();
    }

    protected function identify(): void {
        $headers = tirreno('request')->getHeaders();

        if (array_key_exists(self::API_KEY, $headers) && is_string($headers[self::API_KEY])) {
            $this->apiKeyString = $headers[self::API_KEY];

            return;
        }

        $this->setError(400, tirreno('utils')->errorCodes->REST_API_KEY_DOES_NOT_EXIST);
    }

    protected function authenticate(): void {
        $apiKeyId = tirreno('models')->apiKeys->getKeyIdByHash($this->apiKeyString);

        if ($apiKeyId) {
            $this->apiKeyId = $apiKeyId;

            return;
        }

        $this->setError(401, tirreno('utils')->errorCodes->REST_API_KEY_IS_NOT_CORRECT);
    }

    /*protected function authorize(string $subjectId): void {
        if (!isset($this->apiKeyId) || isset($this->error)) {
            exit;
        }

        $hasAccess = $this->authorizationModel->checkAccessByExternalId($subjectId, $this->apiKeyId);

        if ($hasAccess) {
            return;
        }

        $this->setError(403, tirreno('utils')->errorCodes->REST_API_NOT_AUTHORIZED);
    }*/

    protected function getBodyProp(string $key, string $paramType = 'string'): string|int|array|null {
        $value = $this->body[$key] ?? null;

        if (isset($value)) {
            settype($value, $paramType);
        }

        return $value;
    }

    protected function saveLogbook(): void {
        tirreno('entities')->logbook->addRecord(
            tirreno('request')->getPath(),
            $this->formatStartTime(),                                           //$started,
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',                             //$ip,
            null,                                                               //$eventId,
            !isset($this->error) ? null : json_encode(['Undefined error']),     //$errorText,
            json_encode($this->body),                                           //$raw,
            $this->apiKeyId,
            !isset($this->error) ? tirreno('utils')->constants->LOGBOOK_ERROR_TYPE_SUCCESS : tirreno('utils')->constants->LOGBOOK_ERROR_TYPE_CRITICAL_ERROR,
            //$ended,
        );
    }

    protected function formatStartTime(): string {
        $milliseconds = intval(intval($this->startTime->format('u')) / 1000);

        return $this->startTime->format('Y-m-d H:i:s') . '.' . sprintf('%03d', $milliseconds);
    }

    protected function setError(int $statusCode, int $errorCode): void {
        tirreno('router')->status($statusCode);
        $this->statusCode = $statusCode;
        $this->error = $errorCode;
        $this->afterRoute();
        exit;
    }

    private function parseBody(): void {
        $body = tirreno('request')->getBody();
        $this->body = json_decode($body, true) ?? [];
    }

    private function handleInternalServerError(): void {
        $errorData = tirreno('utils')->errorHandler->getErrorDetails();
        tirreno('utils')->errorHandler->saveErrorInformation($errorData);

        $this->setError(500, 500);
    }
}
