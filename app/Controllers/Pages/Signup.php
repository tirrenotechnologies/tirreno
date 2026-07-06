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

namespace Tirreno\Controllers\Pages;

class Signup extends \Tirreno\Controllers\Pages\Base {
    public string $page = 'signup';
    protected bool $allowGuest = true;

    protected function isAllowed(): bool {
        if (count(tirreno('models')->operator->getAll())) {
            tirreno('response')->error(404);
        }

        return parent::isAllowed();
    }

    protected function proceedPostRequest(): array {
        $this->assertCanEdit();

        $pageParams = [];

        tirreno('utils')->updates->syncUpdates();

        $params = tirreno('utils')->render->extractRequestParams(['token', 'email', 'password', 'timezone', 'rules-preset']);
        $errorCode = tirreno('utils')->validators->validateSignup($params);

        $pageParams['ERROR_CODE'] = $errorCode;

        if ($errorCode) {
            $pageParams['VALUES'] = $params;
        } else {
            $operatorId = $this->addUser($params);

            $apiKey = $this->addDefaultApiKey($operatorId);
            tirreno('controllers')->rules->applyRulesPresetById($params['rules-preset'], tirreno('utils')->constants->PRIMARY_RULES_SET_ID, $apiKey);
            //$this->sendActivationEmail($operatorId);
            $pageParams['SUCCESS_CODE'] = tirreno('utils')->errorCodes->ACCOUNT_CREATED;
        }

        return $pageParams;
    }

    public function getPageParams(): array {
        $this->assertCanView();

        $pageParams = [
            'HTML_FILE'         => 'signup.html',
            'TIMEZONES'         => tirreno('utils')->timezones->timezonesList(),
            'RULES_PRESETS'     => tirreno('assets')->rulesPresets->getPresets(),
            'BASE_PRESET_ID'    => tirreno('utils')->constants->BASE_RULE_PRESET_ID,
            'INTERNAL_PAGE'     => false,
        ];

        $postParams = tirreno('request')->isPost() ? $this->proceedPostRequest() : [];

        return array_merge($pageParams, $postParams);
    }

    private function addDefaultApiKey(int $operatorId): int {
        $skipEnrichingAttr = json_encode(array_keys(tirreno('utils')->constants->ENRICHING_ATTRIBUTES));

        return tirreno('models')->apiKeys->insertRecord($skipEnrichingAttr, true, $operatorId);
    }

    protected function addUser(array $data): int {
        $operatorId = tirreno('models')->operator->insertRecord($data['password'], $data['email'], $data['timezone']);
        tirreno('utils')->operatorAccess->addOperatorRoleById(tirreno('utils')->constants->GUEST_ROLE_ID, $operatorId);
        tirreno('utils')->operatorAccess->addOperatorRoleById(tirreno('utils')->constants->OPERATOR_ROLE_ID, $operatorId);

        return $operatorId;
    }

    /*private function sendActivationEmail(int $operatorId): void {
        $operator = tirreno('entities')->operator->getById($operatorId);
        $url = tirreno('utils')->variables->getHostWithProtocolAndBase();

        $toName = $operator->firstname;
        $toAddress = $operator->email;
        $activationKey = $operator->activationKey;

        $subject = tirreno('storage')->get('Signup_activation_email_subject');
        $message = tirreno('storage')->get('Signup_activation_email_body');

        $activationUrl = sprintf('%s/account-activation/%s', $url, $activationKey);
        $message = sprintf($message, $activationUrl);

        \Tirreno\Utils\Mailer::send($toName, $toAddress, $subject, $message);
    }*/
}
