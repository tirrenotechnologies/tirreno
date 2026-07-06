<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use Tirreno\Utils\Render;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Tirreno\Utils\Render.
 *
 * Covered:
 * - Render::getUserScoreClass()
 */
final class RenderTest extends TestCase {
    /**
     * @dataProvider userScoreClassProvider
     */
    public function testGetUserScoreClassReturnsExpectedValueAndClass(
        ?int $score,
        ?bool $fraud,
        ?string $addedToReview,
        array $expected
    ): void {
        $result = Render::getUserScoreClass($score, $fraud, $addedToReview);

        $this->assertSame($expected, $result);
    }

    public static function userScoreClassProvider(): array {
        return [
            'null score' => [
                'score' => null,
                'fraud' => null,
                'addedToReview' => null,
                'expected' => ['&minus;', 'empty'],
            ],

            'low lower boundary' => [
                'score' => 0,
                'fraud' => null,
                'addedToReview' => null,
                'expected' => [0, 'low'],
            ],

            'low upper boundary' => [
                'score' => 32,
                'fraud' => null,
                'addedToReview' => null,
                'expected' => [32, 'low'],
            ],

            'medium lower boundary' => [
                'score' => 33,
                'fraud' => null,
                'addedToReview' => null,
                'expected' => [33, 'medium'],
            ],

            'medium upper boundary' => [
                'score' => 66,
                'fraud' => null,
                'addedToReview' => null,
                'expected' => [66, 'medium'],
            ],

            'high lower boundary' => [
                'score' => 67,
                'fraud' => null,
                'addedToReview' => null,
                'expected' => [67, 'high'],
            ],

            'high above boundary' => [
                'score' => 100,
                'fraud' => null,
                'addedToReview' => null,
                'expected' => [100, 'high'],
            ],

            'fraud true overrides score' => [
                'score' => 100,
                'fraud' => true,
                'addedToReview' => null,
                'expected' => ['&times;', 'low'],
            ],

            'fraud false overrides score' => [
                'score' => 0,
                'fraud' => false,
                'addedToReview' => null,
                'expected' => ['OK', 'high'],
            ],

            'fraud true overrides review mark' => [
                'score' => 100,
                'fraud' => true,
                'addedToReview' => '2026-02-12 12:00:00',
                'expected' => ['&times;', 'low'],
            ],

            'fraud false overrides review mark' => [
                'score' => 0,
                'fraud' => false,
                'addedToReview' => '2026-02-12 12:00:00',
                'expected' => ['OK', 'high'],
            ],

            'added to review replaces low score with exclamation' => [
                'score' => 10,
                'fraud' => null,
                'addedToReview' => '2026-02-12 12:00:00',
                'expected' => ['!', 'low'],
            ],

            'added to review replaces medium score with exclamation' => [
                'score' => 50,
                'fraud' => null,
                'addedToReview' => '2026-02-12 12:00:00',
                'expected' => ['!', 'medium'],
            ],
            'added to review replaces high score with exclamation' => [
                'score' => 90,
                'fraud' => null,
                'addedToReview' => '2026-02-12 12:00:00',
                'expected' => ['!', 'high'],
            ],
            'added to review replaces empty score with exclamation' => [
                'score' => null,
                'fraud' => null,
                'addedToReview' => '2026-02-12 12:00:00',
                'expected' => ['!', 'empty'],
            ],
        ];
    }
}
