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

namespace Sensor\Factory;

use Sensor\Exception\ValidationException;
use Sensor\Model\CreateEventDto;
use Sensor\Model\HashedValue;
use Sensor\Model\Validated\Email;
use Sensor\Model\Validated\IpAddress;
use Sensor\Model\Validated\Phone;
use Sensor\Model\Validated\Timestamp;
use Sensor\Model\Validated\HttpCode;
use Sensor\Model\Validated\Firstname;
use Sensor\Model\Validated\Lastname;
use Sensor\Model\Validated\Fullname;
use Sensor\Model\Validated\HttpReferer;
use Sensor\Model\Validated\Userid;
use Sensor\Model\Validated\PageTitle;
use Sensor\Model\Validated\Url;
use Sensor\Model\Validated\UserAgent;
use Sensor\Model\Validated\BrowserLanguage;
use Sensor\Model\Validated\EventType;
use Sensor\Model\Validated\HttpMethod;

class RequestFactory {
    private const REQUIRED_FIELDS = ['ipAddress', 'url', 'eventTime'];

    /**
     * @param array<string, string> $data
     */
    public static function createFromArray(array $data, ?string $traceId): CreateEventDto {
        // Check required fields
        foreach (self::REQUIRED_FIELDS as $key) {
            if (!isset($data[$key])) {
                throw new ValidationException('Required field is missing or empty', $key);
            }
        }

        // Check datetime
        $eventTime = new Timestamp($data['eventTime']);

        $userCreated = isset($data['userCreated']) ? (new Timestamp($data['userCreated'])) : null;

        $referer = isset($data['httpReferer']) ? (new HttpReferer($data['httpReferer'])) : null;
        $httpCode = isset($data['httpCode']) ? (new HttpCode($data['httpCode'])) : null;

        $ipAddress = new IpAddress($data['ipAddress']);

        $phone = isset($data['phoneNumber']) ? (new Phone($data['phoneNumber'])) : null;

        $payload = null;
        if (isset($data['payload'])) {
            try {
                /** @var array<mixed, mixed> $payload */
                $payload = json_decode($data['payload'], true, 512, \JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                $payload = null;
            }
        }

        $email =       isset($data['emailAddress']) ? (new Email($data['emailAddress'])) : null;
        $firstname =   isset($data['firstName']) ? (new Firstname($data['firstName'])) : null;
        $lastname =    isset($data['lastName']) ? (new Lastname($data['lastName'])) : null;
        $fullname =    isset($data['fullName']) ? (new Fullname($data['fullName'])) : null;
        $username =    isset($data['userName']) ? (new Userid($data['userName'])) : null;
        $pageTitle =   isset($data['pageTitle']) ? (new PageTitle($data['pageTitle'])) : null;
        $url =         isset($data['url']) ? (new Url($data['url'])) : null;
        $userAgent =   isset($data['userAgent']) ? (new UserAgent($data['userAgent'])) : null;
        $browserLang = isset($data['browserLanguage']) ? (new BrowserLanguage($data['browserLanguage'])) : null;
        $eventType =   isset($data['eventType']) ? (new EventType($data['eventType'])) : null;
        $httpMethod =  isset($data['httpMethod']) ? (new HttpMethod($data['httpMethod'])) : null;

        $validatedParams = [
            $email, $eventTime, $userCreated, $referer, $httpCode, $ipAddress,
            $phone, $firstname, $lastname, $fullname, $username, $pageTitle,
            $url, $userAgent, $browserLang, $eventType, $httpMethod,
        ];

        $changedParams = self::changedParams($validatedParams);

        $email = !isset($data['emailAddress']) && !isset($data['userName']) ? Email::makePlaceholder() : $email;
        $username = $username === null ? md5($email->value) : $username->value;

        return new CreateEventDto(
            $firstname?->value,
            $lastname?->value,
            $fullname?->value,
            $pageTitle?->value,
            $username,
            $email !== null ? new HashedValue($email) : null,
            $email !== null ? explode('@', $email->value)[1] : null,
            $phone !== null && !$phone->isEmpty() ? new HashedValue($phone) : null,
            new HashedValue($ipAddress),
            $url?->value,
            $userAgent?->value,
            $eventTime->value,
            $referer?->value,
            $httpCode?->value,
            $browserLang?->value,
            $eventType?->value,
            $httpMethod?->value,
            $userCreated?->value,
            $traceId,
            $payload,
            $changedParams,
        );
    }

    private static function changedParams(array $validatedParams): array {
        $result = [];

        foreach ($validatedParams as $param) {
            if ($param !== null) {
                $validation = $param->validationStatement();
                if ($validation !== null) {
                    $result[] = $validation;
                }
            }
        }

        return $result;
    }
}
