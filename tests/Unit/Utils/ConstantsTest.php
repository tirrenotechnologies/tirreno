<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use Tirreno\Utils\Constants;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Tirreno\Utils\Constants.
 *
 * Covered:
 * - Constants::get() returns defined constant values.
 * - Undefined constants throw LogicException.
 * - Constants are read-only.
 * - Event type IDs are unique.
 * - Event type groups do not overlap.
 */
final class ConstantsTest extends TestCase {
    public function testGetReturnsConstantValue(): void {
        $value = Constants::get()->SECONDS_IN_MINUTE;

        $this->assertSame(60, $value);
    }

    public function testGetUndefinedConstantThrowsLogicException(): void {
        $missing = 'THIS_CONSTANT_DOES_NOT_EXIST';

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Undefined constant: ' . $missing);

        Constants::get()->$missing;
    }

    public function testConstantsAreReadOnly(): void {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Constants are read-only');

        Constants::get()->SECONDS_IN_MINUTE = 120;
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
}
