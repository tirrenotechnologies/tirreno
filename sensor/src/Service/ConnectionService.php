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

namespace Sensor\Service;

class ConnectionService {
    /**
     * Try to close connection with user.
     */
    public function finishRequestForUser(): bool {
        http_response_code(204);

        if (function_exists('fastcgi_finish_request')) {
            return fastcgi_finish_request();
        }

        // Fallback to old method
        header('Connection: close');
        $size = ob_get_length();
        header("Content-Length: $size");
        if (ob_get_level() > 0) {
            ob_end_flush();
        }
        flush();

        return true;
    }
}
