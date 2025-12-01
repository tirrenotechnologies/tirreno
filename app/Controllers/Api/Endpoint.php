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

namespace Controllers\Api;

abstract class Endpoint {
    public const API_KEY = 'Api-Key';

    protected $f3;

    protected \Views\Json $response;
    protected string $responseType;
    protected int $error;
    protected array $validationErrors = [];
    protected \DateTime $startTime;

    private string $apiKeyString;
    protected \Models\ApiKeys $apiKey;
    protected \Interfaces\ApiKeyAccessAuthorizationInterface $authorizationModel;

    protected array $body = [];

    protected array|null $data = null;

    public function __construct() {
        $this->f3 = \Base::instance();
        $this->f3->set('ONERROR', function (): void {
            $this->handleInternalServerError();
        });
        \Utils\Database::initConnect(false);

        $this->response = new \Views\Json();
        $this->responseType = \Utils\Constants::get('SINGLE_RESPONSE_TYPE');
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
            $errorMessage = $this->f3->get($errorI18nCode);
            $this->response->data = [
                'code' => $this->error,
                'message' => $errorMessage,
            ];
            $this->data = null;
        }

        if (!isset($this->error) || (!in_array($this->error, [400, 401, 403]))) {
            $this->saveLogbook();
        }

        if (($this->data !== null)) {
            $this->response->data = $this->data;
        }

        echo $this->response->render();
    }

    protected function identify(): void {
        $headers = $this->f3->get('HEADERS') ?? [];

        if (array_key_exists(self::API_KEY, $headers) && is_string($headers[self::API_KEY])) {
            $this->apiKeyString = $headers[self::API_KEY];

            return;
        }

        $this->setError(400, \Utils\ErrorCodes::REST_API_KEY_DOES_NOT_EXIST);
    }

    protected function authenticate(): void {
        $model = new \Models\ApiKeys();
        $apiKey = $model->getKeyIdByHash($this->apiKeyString);

        if ($apiKey) {
            $this->apiKey = $apiKey;

            return;
        }

        $this->setError(401, \Utils\ErrorCodes::REST_API_KEY_IS_NOT_CORRECT);
    }

    protected function authorize(string $subjectId): void {
        if (!isset($this->apiKey) || isset($this->error)) {
            exit;
        }

        $apiKeyId = $this->apiKey->id;
        $hasAccess = $this->authorizationModel->checkAccessByExternalId($subjectId, $apiKeyId);

        if ($hasAccess) {
            return;
        }

        $this->setError(403, \Utils\ErrorCodes::REST_API_NOT_AUTHORIZED);
    }

    protected function getBodyProp(string $key, string $paramType = 'string'): string|int|array|null {
        $value = $this->body[$key] ?? null;

        if (isset($value)) {
            settype($value, $paramType);
        }

        return $value;
    }

    protected function saveLogbook(): void {
        $model = new \Models\Logbook();
        $model->add(
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            $this->f3->PATH,
            null,
            !isset($this->error) ? \Utils\Constants::get('LOGBOOK_ERROR_TYPE_SUCCESS') : \Utils\Constants::get('LOGBOOK_ERROR_TYPE_CRITICAL_ERROR'),
            !isset($this->error) ? null : json_encode(['Undefined error']),
            json_encode($this->body),
            $this->formatStartTime(),
            $this->apiKey->id,
        );
    }

    protected function formatStartTime(): string {
        $milliseconds = intval(intval($this->startTime->format('u')) / 1000);

        return $this->startTime->format('Y-m-d H:i:s') . '.' . sprintf('%03d', $milliseconds);
    }

    protected function setError(int $statusCode, int $errorCode): void {
        $this->f3->status($statusCode);
        $this->error = $errorCode;
        $this->afterRoute();
        exit;
    }

    private function parseBody(): void {
        $body = $this->f3->get('BODY');
        $this->body = json_decode($body, true) ?? [];
    }

    private function handleInternalServerError(): void {
        $errorData = \Utils\ErrorHandler::getErrorDetails($this->f3);
        \Utils\ErrorHandler::saveErrorInformation($this->f3, $errorData);

        $this->setError(500, 500);
    }
}
