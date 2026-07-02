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

namespace Tirreno\Entities;

class Logbook {
    public int $id;
    public string $ended;
    public ?string $started;
    public ?string $ip;
    public ?int $event;
    public ?string $errorText;
    public int $errorType;
    public string $errorName;
    public string $errorValue;
    public ?string $raw;
    public ?string $endpoint;

    public function __construct(
        int $id,
        string $ended,
        ?string $started,
        ?string $ip,
        ?int $event,
        ?string $errorText,
        int $errorType,
        string $errorName,
        string $errorValue,
        ?string $raw,
        ?string $endpoint,
    ) {
        $this->id               = $id;
        $this->ended            = $ended;
        $this->started          = $started;
        $this->ip               = $ip;
        $this->event            = $event;
        $this->errorText        = $errorText;
        $this->errorType        = $errorType;
        $this->errorName        = $errorName;
        $this->errorValue       = $errorValue;
        $this->raw              = $raw;
        $this->endpoint         = $endpoint;
    }

    public static function getById(int $logbookId, int $apiKey): ?self {
        $row = tirreno('models')->logbook->getLogbookDetails($logbookId, $apiKey);

        if (!$row) {
            return null;
        }

        return new self(
            $row['id'],
            $row['ended'],
            $row['started'],
            $row['ip'],
            $row['event'],
            $row['error_text'],
            $row['error_type'],
            $row['error_name'],
            $row['error_value'],
            $row['raw'],
            $row['endpoint'],
        );
    }

    // add row

    // incoming

    // id
    // request ended
    // request started
    // ip (incoming request)
    // event id
    // error_text
    // error_type
    // error_name
    // error_value
    // raw request data
    // endpoint caught

    // outcoming

    // id
    // request ended
    // request started
    // ! no ip !
    // ! no event !
    // error_text  ??   // store return code
    // error_type  ??   // 0 success or 3 error
    // error_name  ??
    // error_value ??
    // raw request data
    // url + endpoint

    public static function addRecord(
        string $endpoint,
        ?string $started,
        ?string $ip,
        ?int $eventId,
        ?string $errorText,
        ?string $raw,
        int $apiKey,
        int $errorType = 0,
        ?string $ended = null,
    ): ?self {
        $result = tirreno('models')->logbook->add($ip, $endpoint, $eventId, $errorType, $errorText, $raw, $started, $ended, $apiKey);
        $errorData = tirreno('models')->logbook->getEventErrorType($errorType);

        return new self(
            $result['id'],
            $result['ended'],
            $started,
            $ip,
            $eventId,
            $errorText,
            $errorType,
            $errorData['name'],
            $errorData['value'],
            $raw,
            $endpoint,
        );
    }
}
