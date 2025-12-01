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

namespace Controllers\Pages;

class Signup extends Base {
    public $page = 'Signup';

    public function getPageParams() {
        $model = new \Models\Operator();
        if (count($model->getAll())) {
            $this->f3->error(404);
        }

        $pageParams = [
            'HTML_FILE'     => 'signup.html',
            'TIMEZONES'     => \Utils\TimeZones::timeZonesList(),
        ];

        if ($this->isPostRequest()) {
            \Utils\Updates::syncUpdates();

            $params = $this->extractRequestParams(['token', 'email', 'password', 'timezone']);
            $errorCode = \Utils\Validators::validateSignup($params);

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

    private function addDefaultApiKey(int $operatorId): int {
        $data = [
            'quote' => $this->f3->get('DEFAULT_API_KEY_QUOTE'),
            'operator_id' => $operatorId,
            'skip_enriching_attributes' => \json_encode(array_keys(\Utils\Constants::get('ENRICHING_ATTRIBUTES'))),
            'skip_blacklist_sync' => true,
        ];

        $model = new \Models\ApiKeys();

        return $model->add($data);
    }

    protected function addDefaultRules(int $apiKey): void {
        $model = new \Models\OperatorsRules();
        $defaultRules = \Utils\Constants::get('DEFAULT_RULES');

        if (\Utils\Variables::getEmailPhoneAllowed()) {
            $defaultRules = array_merge($defaultRules, \Utils\Constants::get('DEFAULT_RULES_EXTENSION'));
        }

        foreach ($defaultRules as $key => $value) {
            $model->updateRule($key, $value, $apiKey);
        }
    }

    private function addUser(array $data): \Models\Operator {
        $model = new \Models\Operator();
        $model->add($data);

        return $model;
    }

    private function sendActivationEmail(\Models\Operator $operatorModel): void {
        $url = \Utils\Variables::getHostWithProtocolAndBase();

        $toName = $operatorModel->firstname;
        $toAddress = $operatorModel->email;
        $activationKey = $operatorModel->activation_key;

        $subject = $this->f3->get('Signup_activation_email_subject');
        $message = $this->f3->get('Signup_activation_email_body');

        $activationUrl = sprintf('%s/account-activation/%s', $url, $activationKey);
        $message = sprintf($message, $activationUrl);

        \Utils\Mailer::send($toName, $toAddress, $subject, $message);
    }
}
