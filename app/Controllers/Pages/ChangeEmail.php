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

class ChangeEmail extends Base {
    public $page = 'ChangeEmail';

    public function getPageParams(): array {
        $pageParams = [
            'HTML_FILE' => 'changeEmail.html',
        ];

        $errorCode = \Utils\Validators::validateChangeEmailPage($this->f3->get('PARAMS'));
        $pageParams['SUCCESS_CODE'] = $errorCode;

        if (!$errorCode) {
            //logout
            $this->f3->clear('SESSION');
            session_commit();

            //change email
            $changeEmailModel = new \Models\ChangeEmail();
            $changeEmailModel->getByRenewKey($this->f3->get('PARAMS.renewKey'));

            $newEmail = $changeEmailModel->email;
            $operatorId = $changeEmailModel->operator_id;

            $changeEmailModel->deactivate();

            $params = [
                'id' => $operatorId,
                'email' => $newEmail,
            ];
            $operatorModel = new \Models\Operator();
            $operatorModel->updateEmail($params);

            //update success message
            $pageParams['SUCCESS_CODE'] = \Utils\ErrorCodes::EMAIL_CHANGED;
        }

        return parent::applyPageParams($pageParams);
    }
}
