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

namespace Controllers\Admin\Rules;

class Navigation extends \Controllers\Base {
    use \Traits\ApiKeys;
    use \Traits\Navigation;

    public function showIndexPage(): void {
        $this->redirectIfUnlogged();

        $pageController = new Page();
        $this->response = new \Views\Frontend();
        $this->response->data = $pageController->getPageParams();
    }

    public function saveRule(): array {
        $params = $this->f3->get('POST');
        $key = explode('_', $params['rule']);
        $ruleUid = end($key);
        $score = $params['value'];

        $dataController = new Data();
        $dataController->saveUserRule($ruleUid, $score);

        return ['success' => true];
    }

    public function checkRule(): array {
        set_time_limit(0);
        ini_set('max_execution_time', 0);

        $params = $this->f3->get('GET');
        $ruleUid = $params['ruleUid'];

        $dataController = new Data();
        [$allUsersCnt, $users] = $dataController->checkRule($ruleUid);
        $proportion = $dataController->getRuleProportion($allUsersCnt, count($users));
        $dataController->saveRuleProportion($ruleUid, $proportion);

        return [
            'users'                 => array_slice($users, 0, \Utils\Constants::get('RULE_CHECK_USERS_PASSED_TO_CLIENT')),
            'count'                 => count($users),
            'proportion'            => $proportion,
            'proportion_updated_at' => date('Y-m-d H:i:s'),
        ];
    }
}
