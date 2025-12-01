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

namespace Sensor\Model\Validated;

class IpAddress extends Base {
    private const INVALIDPLACEHOLDER = '0.0.0.0';
    private const LOCALHOST_NETS = [
        '0.0.0.0/8', '10.0.0.0/8', '100.64.0.0/10', '127.0.0.0/8',
        '169.254.0.0/16', '172.16.0.0/12', '192.0.0.0/24', '192.0.2.0/24',
        '192.168.0.0/16', '198.18.0.0/15', '198.51.100.0/24', '203.0.113.0/24',
        '224.0.0.0/4', '240.0.0.0/4', '255.255.255.255/32',
        '::/128', '::1/128', '::ffff:0:0/96', '::/96', '100::/64', '2001:10::/28',
        '2001:db8::/32', 'fc00::/7', 'fe80::/10', 'fec0::/10', 'ff00::/8',
    ];
    public string $value;

    public function __construct(string $value) {
        parent::__construct($value, 'ipAddress');
        $value = str_replace(' ', '', $value);

        if (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6) === false) {
            $this->value = self::INVALIDPLACEHOLDER;
        } else {
            $this->value = $value;
        }

        $this->invalid = filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6) === false;
    }

    public static function isInvalid(string $value): bool {
        return self::INVALIDPLACEHOLDER === $value;
    }

    public function isLocalhost(): bool {
        if (filter_var($this->value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6) === false) {
            return false;
        }

        $size = filter_var($this->value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? 4 : 16;
        $ip = inet_pton($this->value);

        foreach (self::LOCALHOST_NETS as $cidr) {
            [$net, $maskBits] = explode('/', $cidr);
            $net = inet_pton($net);
            if (!$net) {
                continue;
            }

            $solid = intval(floor($maskBits / 8));
            $solidBits = $solid * 8;
            $mask = str_repeat(chr(255), $solid);
            for ($i = $solidBits; $i < $maskBits; $i += 8) {
                $bits = max(0, min(8, $maskBits - $i));
                $mask .= chr((pow(2, $bits) - 1) << (8 - $bits));
            }
            $mask = str_pad($mask, $size, chr(0));

            if (($ip & $mask) === ($net & $mask)) {
                return true;
            };
        }

        return false;
    }
}
