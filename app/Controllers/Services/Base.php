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

namespace Tirreno\Controllers\Services;

abstract class Base {
    public function __construct() {
        $keepSessionInDb = tirreno('storage')->get('KEEP_SESSION_IN_DB') ?? null;
        if (!tirreno('utils')->database->initConnect(boolval($keepSessionInDb))) {
            tirreno('response')->error(404);
        }

        //Determine current user
        tirreno('utils')->routes->setCurrentRequestOperator();
        tirreno('utils')->routes->setCurrentRequestApiKey();

        //Set CSRF token
        //$rnd = mt_rand();
        //tirreno('router')->CSRF = sprintf('%s.%s', tirreno('router')->SEED, tirreno('router')->hash($rnd));
    }

    protected function idMapIterate(array $map, object $model, int $apiKey, ?string $default = 'getAll', mixed ...$extra): array {
        $result = [];

        foreach ($map as $param => $method) {
            $id = tirreno('utils')->conversion->getIntRequestParam($param, true);
            if ($id !== null) {
                $result = $model->$method($id, $apiKey, ...$extra);
            }

            if ($result) {
                break;
            }
        }

        if (!$result && $default !== null) {
            $result = $model->$default($apiKey, ...$extra);
        }

        return $result;
    }
}
