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

declare(strict_types=1);

namespace Sensor\Model\Http;

class Request {
    public const ACCEPTABLE_FIELDS = [
        'userName',
        'emailAddress',
        'ipAddress',
        'url',
        'userAgent',
        'eventTime',
        'firstName',
        'lastName',
        'fullName',
        'pageTitle',
        'phoneNumber',
        'httpReferer',
        'httpCode',
        'browserLanguage',
        'eventType',
        'httpMethod',
        'userCreated',
    ];

    /**
     * @param array<string,string> $body
     */
    public function __construct(
        public array $body,
        #[\SensitiveParameter]
        public ?string $apiKey,
        public ?string $traceId,
    ) {
        // all acceptable $this->body values should be either string or null
        foreach (self::ACCEPTABLE_FIELDS as $key) {
            if (isset($this->body[$key])) {
                $value = $this->body[$key];

                if (is_bool($value)) {
                    $this->body[$key] = ($value) ? 'true' : 'false';
                } elseif (is_array($value)) {
                    $this->body[$key] = json_encode($this->cleanArrayEncoding($value));
                } elseif ($value !== null) {
                    $this->body[$key] = $this->cleanArrayEncoding(strval($value));
                }
            } else {
                $this->body[$key] = null;
            }
        }
        $this->body['hashEmailAddress'] = null;
        $this->body['hashPhoneNumber'] = null;
        $this->body['hashIpAddress'] = null;
    }

    // recursive array encoding cleanup
    private function cleanArrayEncoding(mixed $data): mixed {
        if (is_string($data)) {
            return mb_convert_encoding($data, 'UTF-8', 'UTF-8');
        } elseif (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->cleanArrayEncoding($value);
            }
        }

        return $data;
    }
}
