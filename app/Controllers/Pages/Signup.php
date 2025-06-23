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

namespace Controllers\Pages;

class Signup extends Base {
    public $page = 'Signup';

    public function getPageParams() {
        $model = new \Models\Operator();
        if (count($model->getAll())) {
            $this->f3->error(404);
        }

        $pageParams = [
            'HTML_FILE' => 'signup.html',
        ];

        if ($this->isPostRequest()) {
            $params = $this->f3->get('POST');
            $errorCode = $this->validate($params);

            $pageParams['ERROR_CODE'] = $errorCode;

            if ($errorCode) {
                $pageParams['VALUES'] = $params;
            } else {
                $operatorModel = $this->addUser($params);

                $operatorId = $operatorModel->id;
                $apiKey = $this->addDefaultApiKey($operatorId);
                $this->addDefaultRules($apiKey);

                //$this->sendActivationEmail($operatorModel);
                $pageParams['SUCCESS_CODE'] = \Utils\ErrorCodes::ACCOUNT_CREATED;
            }
        }

        return parent::applyPageParams($pageParams);
    }

    private function addDefaultApiKey($operatorId) {
        $data = [
            'quote' => $this->f3->get('DEFAULT_API_KEY_QUOTE'),
            'operator_id' => $operatorId,
            'skip_enriching_attributes' => \json_encode(array_keys(\Utils\Constants::get('ENRICHING_ATTRIBUTES'))),
            'skip_blacklist_sync' => true,
        ];

        $model = new \Models\ApiKeys();

        return $model->add($data);
    }

    private function addDefaultRules(int $apiKey): void {
        $model = new \Models\OperatorsRules();
        $defaultRules = \Utils\Constants::get('DEFAULT_RULES');

        foreach ($defaultRules as $key => $value) {
            $model->updateRule($key, $value, $apiKey);
        }
    }

    private function addUser($data) {
        $model = new \Models\Operator();
        $model->add($data);

        return $model;
    }

    private function sendActivationEmail(\Models\Operator $operatorModel): void {
        $url = \Utils\Variables::getSiteWithProtocol();

        $toName = $operatorModel->firstname;
        $toAddress = $operatorModel->email;
        $activationKey = $operatorModel->activation_key;

        $subject = $this->f3->get('Signup_activation_email_subject');
        $message = $this->f3->get('Signup_activation_email_body');

        $activationUrl = sprintf('%s/account-activation/%s', $url, $activationKey);
        $message = sprintf($message, $activationUrl);

        \Utils\Mailer::send($toName, $toAddress, $subject, $message);
    }

    private function validate(array $params): int|false {
        $errorCode = \Utils\Access::CSRFTokenValid($params, $this->f3);
        if ($errorCode) {
            return $errorCode;
        }

        $email = $params['email'];
        $password = $params['password'];

        if (!$email) {
            return \Utils\ErrorCodes::EMAIL_DOES_NOT_EXIST;
        }

        $audit = \Audit::instance();
        if (!$audit->email($email, true)) {
            return \Utils\ErrorCodes::EMAIL_IS_NOT_CORRECT;
        }

        $operatorsModel = new \Models\Operator();
        $operator = $operatorsModel->getByEmail($email);
        if ($operator) {
            return \Utils\ErrorCodes::EMAIL_ALREADY_EXIST;
        }

        if (!$password) {
            return \Utils\ErrorCodes::PASSWORD_DOES_NOT_EXIST;
        }

        $passwordLegth = strlen($password);
        $minPasswordLegth = $this->f3->get('MIN_PASSWORD_LENGTH');
        if ($passwordLegth < $minPasswordLegth) {
            return \Utils\ErrorCodes::PASSWORD_IS_TO_SHORT;
        }

        $timezone = $params['timezone'] ?? null;
        $timezones = $this->f3->get('timezones');
        if (!$timezone || !array_key_exists($timezone, $timezones)) {
            return \Utils\ErrorCodes::TIME_ZONE_DOES_NOT_EXIST;
        }

        return false;
    }
}
