<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use Tirreno\Utils\Rules;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Tirreno\Utils\Rules.
 *
 * Covered:
 * - Rules::checkPhoneCountryMatchIp()
 * - Rules::eventDeviceIsNew()
 * - Rules::countryIsNewByIpId()
 * - Rules::cidrIsNewByIpId()
 */
final class RulesTest extends TestCase {
    /**
     * @dataProvider checkPhoneCountryMatchIpProvider
     */
    public function testCheckPhoneCountryMatchIpReturnsExpected(
        ?int $lpCountryCode,
        array $eipCountryId,
        ?bool $expected
    ): void {
        $params = [
            'lp_country_code' => $lpCountryCode,
            'eip_country_id' => $eipCountryId,
        ];

        $result = Rules::checkPhoneCountryMatchIp($params);

        $this->assertSame($expected, $result);
    }

    public static function checkPhoneCountryMatchIpProvider(): array {
        return [
            'null country => null' => [
                'lpCountryCode' => null,
                'eipCountryId' => [1, 2],
                'expected' => null,
            ],
            '0 country => null' => [
                'lpCountryCode' => 0,
                'eipCountryId' => [1, 2],
                'expected' => null,
            ],
            'match => true' => [
                'lpCountryCode' => 2,
                'eipCountryId' => [1, 2, 3],
                'expected' => true,
            ],
            'no match => false' => [
                'lpCountryCode' => 9,
                'eipCountryId' => [1, 2, 3],
                'expected' => false,
            ],
        ];
    }

    /**
     * @dataProvider eventDeviceIsNewProvider
     */
    public function testEventDeviceIsNewReturnsExpected(
        string $created,
        string $lastSeen,
        bool $expected
    ): void {
        $params = [
            'event_device_created' => [
                0 => $created,
            ],
            'event_device_lastseen' => [
                0 => $lastSeen,
            ],
        ];

        $result = Rules::eventDeviceIsNew($params, 0);

        $this->assertSame($expected, $result);
    }

    public static function eventDeviceIsNewProvider(): array {
        return [
            'less than three hours => true' => [
                'created' => '2024-01-01 10:00:00',
                'lastSeen' => '2024-01-01 12:59:59',
                'expected' => true,
            ],
            'exactly three hours => false' => [
                'created' => '2024-01-01 10:00:00',
                'lastSeen' => '2024-01-01 13:00:00',
                'expected' => false,
            ],
            'more than three hours => false' => [
                'created' => '2024-01-01 10:00:00',
                'lastSeen' => '2024-01-01 13:00:01',
                'expected' => false,
            ],
            'reverse order uses absolute diff => true' => [
                'created' => '2024-01-01 12:00:00',
                'lastSeen' => '2024-01-01 10:00:00',
                'expected' => true,
            ],
        ];
    }

    /**
     * @dataProvider countryIsNewByIpIdProvider
     */
    public function testCountryIsNewByIpIdReturnsExpected(
        array $params,
        int $ipId,
        bool $expected
    ): void {
        $result = Rules::countryIsNewByIpId($params, $ipId);

        $this->assertSame($expected, $result);
    }

    public static function countryIsNewByIpIdProvider(): array {
        return [
            'ipId not found => false' => [
                'params' => [
                    'eip_ip_id' => [],
                    'eip_country_count' => [],
                ],
                'ipId' => 10,
                'expected' => false,
            ],
            'country count == 1 => true' => [
                'params' => [
                    'eip_ip_id' => [
                        7 => ['country' => 5],
                    ],
                    'eip_country_count' => [
                        5 => 1,
                    ],
                ],
                'ipId' => 7,
                'expected' => true,
            ],
            'country count > 1 => false' => [
                'params' => [
                    'eip_ip_id' => [
                        7 => ['country' => 5],
                    ],
                    'eip_country_count' => [
                        5 => 3,
                    ],
                ],
                'ipId' => 7,
                'expected' => false,
            ],
            'country missing in count => false' => [
                'params' => [
                    'eip_ip_id' => [
                        7 => ['country' => 5],
                    ],
                    'eip_country_count' => [
                        9 => 1,
                    ],
                ],
                'ipId' => 7,
                'expected' => false,
            ],
        ];
    }

    /**
     * @dataProvider cidrIsNewByIpIdProvider
     */
    public function testCidrIsNewByIpIdReturnsExpected(
        array $params,
        int $ipId,
        bool $expected
    ): void {
        $result = Rules::cidrIsNewByIpId($params, $ipId);

        $this->assertSame($expected, $result);
    }

    public static function cidrIsNewByIpIdProvider(): array {
        return [
            'ipId not found => false' => [
                'params' => [
                    'eip_ip_id' => [],
                    'eip_cidr_count' => [],
                ],
                'ipId' => 10,
                'expected' => false,
            ],
            'cidr count == 1 => true' => [
                'params' => [
                    'eip_ip_id' => [
                        7 => ['cidr' => '1.2.3.0/24'],
                    ],
                    'eip_cidr_count' => [
                        '1.2.3.0/24' => 1,
                    ],
                ],
                'ipId' => 7,
                'expected' => true,
            ],
            'cidr count > 1 => false' => [
                'params' => [
                    'eip_ip_id' => [
                        7 => ['cidr' => '1.2.3.0/24'],
                    ],
                    'eip_cidr_count' => [
                        '1.2.3.0/24' => 2,
                    ],
                ],
                'ipId' => 7,
                'expected' => false,
            ],
            'cidr missing in count => false' => [
                'params' => [
                    'eip_ip_id' => [
                        7 => ['cidr' => '1.2.3.0/24'],
                    ],
                    'eip_cidr_count' => [
                        '9.9.9.0/24' => 1,
                    ],
                ],
                'ipId' => 7,
                'expected' => false,
            ],
        ];
    }
}
