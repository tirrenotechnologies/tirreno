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

use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\AbstractDeviceParser;
use Sensor\Repository\UserAgentRepository;
use Sensor\Dto\GetApiKeyDto;
use Sensor\Model\DeviceDetected;

class DeviceDetectorService {
    public function __construct(
        private UserAgentRepository $userAgentRepository,
    ) {
    }

    public function parse(GetApiKeyDto $apiKeyDto, ?string $userAgent): ?DeviceDetected {
        if ($userAgent === null || $apiKeyDto->skipEnrichingUserAgents || $this->userAgentRepository->isChecked($userAgent, $apiKeyDto->id)) {
            return null;
        }

        $detector = new DeviceDetector($userAgent);
        $detector->parse();

        $deviceType = null;
        $browserName = null;
        $browserVersion = null;
        $osName = null;
        $osVersion = null;
        $modified = false;

        if ($detector->isBot()) {
            $deviceType = 'bot';
            $botInfo = $detector->getBot();
            $osName = $this->valueOrNull('name', $botInfo);
            $modified = true;
        } else {
            $deviceTypeInt = $detector->getDevice();
            $clientInfo = $detector->getClient();
            $osInfo = $detector->getOs();

            $deviceType = $deviceTypeInt !== null ? AbstractDeviceParser::getDeviceName($deviceTypeInt) : null;
            $browserName = $this->valueOrNull('name', $clientInfo);
            $browserVersion = $this->valueOrNull('version', $clientInfo);
            $osName = $this->valueOrNull('name', $osInfo);
            $osVersion = $this->valueOrNull('version', $osInfo);
            $modified = $deviceType === null;
        }

        return new DeviceDetected(
            $deviceType,
            $browserName,
            $browserVersion,
            $osName,
            $osVersion,
            $userAgent,
            $modified,
        );
    }

    private function valueOrNull(string $key, array|bool|int|null $array): ?string {
        if (!is_array($array) || !array_key_exists($key, $array)) {
            return null;
        }

        return ($array[$key] !== '') ? $array[$key] : null;
    }
}
