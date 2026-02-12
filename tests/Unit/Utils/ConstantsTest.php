<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use Tirreno\Utils\Constants;
use Base;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\ErrorException;

/**
 * Unit tests for Tirreno\Utils\Constants.
 *
 * Focus:
 * - Constants::get() behavior (native constant lookup + F3 overrides)
 * - basic integrity invariants that are easy to break accidentally
 */
final class ConstantsTest extends TestCase {
    /**
     * @var Base
     */
    private Base $f3;
    private Constants $constants;

    protected function setUp(): void {
        parent::setUp();

        $this->f3 = Base::instance();

        //$key = 'EXTRA_DEVICE_TYPES';
        //$override = ['wearable'];
        //$this->f3->set($key, $override);

        $this->constants = Constants::get();
        $this->clearExtraOverrides();
    }

    protected function tearDown(): void {
        $this->clearExtraOverrides();

        parent::tearDown();
    }

    public function testGetReturnsConstantValue(): void {
        $value = Constants::get()->SECONDS_IN_MINUTE;

        $this->assertSame(60, $value);
    }

    /*public function testGetMergesArrayFromF3(): void {
        $key = 'EXTRA_DEVICE_TYPES';
        $override = [
            'wearable',
        ];
        $this->f3->set($key, $override);

        $value = Constants::get()->DEVICE_TYPES;

        $this->assertIsArray($value);

        $expected = array_merge(Constants::get()->DEVICE_TYPES, $override);
        $this->assertSame($expected, $value);
    }*/

    public function testGetUndefinedConstantThrowsRuntimeException(): void {
        $missing = 'THIS_CONSTANT_DOES_NOT_EXIST';

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Undefined constant: ' . $missing);

        Constants::get()->$missing;
    }

    public function testEventTypeIdsAreUnique(): void {
        $ids = [
            Constants::get()->PAGE_VIEW_EVENT_TYPE_ID,
            Constants::get()->PAGE_EDIT_EVENT_TYPE_ID,
            Constants::get()->PAGE_DELETE_EVENT_TYPE_ID,
            Constants::get()->PAGE_SEARCH_EVENT_TYPE_ID,
            Constants::get()->ACCOUNT_LOGIN_EVENT_TYPE_ID,
            Constants::get()->ACCOUNT_LOGOUT_EVENT_TYPE_ID,
            Constants::get()->ACCOUNT_LOGIN_FAIL_EVENT_TYPE_ID,
            Constants::get()->ACCOUNT_REGISTRATION_EVENT_TYPE_ID,
            Constants::get()->ACCOUNT_EMAIL_CHANGE_EVENT_TYPE_ID,
            Constants::get()->ACCOUNT_PASSWORD_CHANGE_EVENT_TYPE_ID,
            Constants::get()->ACCOUNT_EDIT_EVENT_TYPE_ID,
            Constants::get()->PAGE_ERROR_EVENT_TYPE_ID,
            Constants::get()->FIELD_EDIT_EVENT_TYPE_ID,
        ];

        $unique = array_unique($ids);

        $this->assertCount(count($ids), $unique, 'All event type IDs must be unique.');
    }

    public function testEventTypeGroupsDoNotOverlap(): void {
        $alert = Constants::get()->ALERT_EVENT_TYPES;
        $editing = Constants::get()->EDITING_EVENT_TYPES;
        $normal = Constants::get()->NORMAL_EVENT_TYPES;

        $intersectAlertEditing = array_intersect($alert, $editing);
        $intersectAlertNormal = array_intersect($alert, $normal);
        $intersectEditingNormal = array_intersect($editing, $normal);

        $this->assertSame([], array_values($intersectAlertEditing), 'ALERT and EDITING event types must not overlap.');
        $this->assertSame([], array_values($intersectAlertNormal), 'ALERT and NORMAL event types must not overlap.');
        $this->assertSame([], array_values($intersectEditingNormal), 'EDITING and NORMAL event types must not overlap.');
    }

    /**
     * Clears all EXTRA_* overrides used by this test suite.
     *
     * @return void
     */
    private function clearExtraOverrides(): void {
        $keys = [
            'EXTRA_SECONDS_IN_MINUTE',
            'EXTRA_DEVICE_TYPES',
        ];

        $iters = count($keys);

        for ($i = 0; $i < $iters; ++$i) {
            $key = $keys[$i];

            $exists = $this->f3->exists($key);
            if ($exists) {
                $this->f3->clear($key);
            }
        }
    }
}
