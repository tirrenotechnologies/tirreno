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

class Rules extends \Tirreno\Controllers\Pages\Base {
    public string $page = 'rules';

    protected function proceedPostRequest(): array {
        $this->assertCanEdit();

        return match (tirreno('utils')->conversion->getStringRequestParam('cmd')) {
            'changeThresholdValues' => tirreno('controllers')->rules->changeThresholdValues(),
            'refreshRules'          => tirreno('controllers')->rules->refreshRules(),
            'applyRulesPreset'      => tirreno('controllers')->rules->applyRulesPreset(),
            default => []
        };
    }

    public function getPageParams(): array {
        $this->assertCanView();

        $postParams = tirreno('request')->isPost() ? $this->proceedPostRequest() : [];

        [$isOwner, $apiKeys] = tirreno('utils')->apiKeys->getOperatorApiKeys($this->operator->id);

        $pageParams = [
            'LOAD_DATATABLE'        => true,
            'LOAD_AUTOCOMPLETE'     => true,
            'HTML_FILE'             => 'rules.html',
            'JS'                    => 'rules.js',
            'RULES_PRESETS'         => tirreno('assets')->rulesPresets->getPresets(),
            'BASE_PRESET_ID'        => tirreno('utils')->constants->BASE_RULE_PRESET_ID,
            'IS_OWNER'              => $isOwner,
            'API_KEYS'              => $apiKeys,
            'INTERNAL_PAGE'         => true,
        ];

        return array_merge($pageParams, $postParams);
    }

    public function getList(): array {
        $this->assertCanView();

        return $this->apiKey ? $this->controller->getList($this->apiKey) : [];
    }

    public function saveRule(): array {
        $this->assertCanEdit();

        $ruleUid    = tirreno('utils')->conversion->getStringRequestParam('rule');
        $score      = tirreno('utils')->conversion->getIntRequestParam('value');

        $this->controller->saveUserRule($ruleUid, $score, $this->apiKey);

        return ['success' => true];
    }

    public function checkRule(): array {
        $this->assertCanView();

        set_time_limit(0);
        ini_set('max_execution_time', '0');

        $ruleUid    = tirreno('utils')->conversion->getStringRequestParam('ruleUid');

        [$allUsersCnt, $users] = $this->controller->checkRule($ruleUid, $this->apiKey);
        $proportion = $this->controller->getRuleProportion($allUsersCnt, count($users));

        return [
            'users'                 => array_slice($users, 0, tirreno('utils')->constants->RULE_CHECK_USERS_PASSED_TO_CLIENT),
            'count'                 => count($users),
            'section'               => $allUsersCnt,
            'proportion'            => $proportion,
            'proportion_updated_at' => date('Y-m-d H:i:s'),
        ];
    }
}
