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

namespace Traits;

trait Navigation {
    protected $response;

    public function beforeroute(): void {
        $currentOperator = $this->f3->get('CURRENT_USER');

        if ($currentOperator) {
            $apiKey = $this->getCurrentOperatorApiKeyId();
            $messages = \Utils\SystemMessages::get($apiKey);

            $this->f3->set('SYSTEM_MESSAGES', $messages);

            if (count($messages)) {
                $m = $messages[0];
                $doRedirect = $this->shouldRedirectToApiKeys($m);

                if ($doRedirect) {
                    $this->f3->reroute('/api');
                }
            }
        }
    }

    private function shouldRedirectToApiKeys($message): bool {
        $route = $this->f3->get('PARAMS.0');
        $allowedPages = [
            '/api',
            '/settings',
            '/logbook',
        ];

        $allowedPages = array_merge($allowedPages, $this->f3->get('EXTRA_ALLOWED_PAGES') ?? []);

        $isPageAllowed = in_array($route, $allowedPages);

        return !$isPageAllowed && ($message['id'] === \Utils\ErrorCodes::THERE_ARE_NO_EVENTS_YET);
    }

    public function isPostRequest(): bool {
        return $this->f3->VERB === 'POST';
    }

    /**
     * set a new view.
     */
    /* TODO: make sure that setView() is not needed
    public function setView(BaseView $view) {
        $this->response = $view;
    }*/

    /**
     * kick start the View, which creates the response
     * based on our previously set content data.
     * finally echo the response or overwrite this method
     * and do something else with it.
     */
    public function afterroute(): void {
        if (!$this->response) {
            trigger_error('No View has been set.');
        }

        $shouldPrintSqlToLog = $this->f3->get('PRINT_SQL_LOG_AFTER_EACH_SCRIPT_CALL');

        if ($shouldPrintSqlToLog) {
            $hive = $this->f3->hive();
            $path = $hive['PATH'];

            $log = $this->f3->get('API_DATABASE')->log();
            if ($log) {
                \Utils\Logger::logSql($path, $log);
            }
        }

        echo $this->response->render();
    }
}
