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

namespace Sensor\Service\Debug;

use Sensor\Service\Logger;

class PdoProxy extends \PDO {
    private ?Logger $logger = null;

    public function setLogger(Logger $logger): void {
        $this->logger = $logger;
    }

    #[\ReturnTypeWillChange]
    public function prepare(string $query, array $options = []): PdoStatementProxy {
        $statement = parent::prepare($query, $options);

        return new PdoStatementProxy($statement, $this->logger);
    }
}
