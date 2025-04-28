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

namespace Controllers\Api;

abstract class Endpoint {
    use \Traits\Db;
    use \Traits\Debug;

    public const API_KEY = 'Api-Key';

    protected $f3;

    protected \Views\Json $response;
    protected \Type\ResponseType $responseType;
    protected int $error;
    protected array $validationErrors = [];

    private string $apiKeyString;
    protected \Models\ApiKeys $apiKey;
    protected \Interfaces\ApiKeyAccessAuthorizationInterface $authorizationModel;

    private array $body = [];

    protected array|null $data = null;

    public function __construct() {
        $this->f3 = \Base::instance();
        $this->f3->set('ONERROR', function (): void {
            $this->handleInternalServerError();
        });
        $this->connectToDb(false);

        $this->response = new \Views\Json();
        $this->responseType = new \Type\ResponseType(\Type\ResponseType::Single);
    }

    public function beforeRoute(): void {
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
