<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use Tirreno\Utils\Mailer;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Tirreno\Utils\Mailer.
 *
 * Covered:
 * - Mailer::send() returns a development-mode response when SEND_EMAIL is falsy.
 *
 * Not covered:
 * - Mailer::send() SMTP branch (PHPMailer, SMTP configuration, network IO).
 * - Mailer::send() native mail branch (filesystem checks, sendmail, mail()).
 * - sendByMailgun().
 * - sendByNativeMail().
 *
 * @todo Refactor:
 * - extract MailTransportInterface.
 * - extract ConfigInterface for application settings.
 * - wrap filesystem checks and mail() behind interfaces.
 */
final class MailerTest extends TestCase {
    private \Base $f3;

    /** @var array<string, mixed> */
    private array $f3Backup = [];

    /** @var list<string> */
    private array $f3Keys = [
        'SEND_EMAIL',
    ];

    protected function setUp(): void {
        parent::setUp();

        $this->f3 = \Base::instance();

        $this->backupF3();
        $this->clearF3();
    }

    protected function tearDown(): void {
        $this->restoreF3();

        parent::tearDown();
    }

    /**
     * @dataProvider devModeProvider
     */
    public function testSendReturnsDevModeResponse(mixed $sendEmailFlag): void {
        $this->f3->set('SEND_EMAIL', $sendEmailFlag);

        $toName = null;
        $toAddress = 'user@example.com';
        $subject = 'Subject';
        $message = 'Message';

        $result = Mailer::send($toName, $toAddress, $subject, $message);

        $this->assertIsArray($result);

        $expectedSuccess = true;
        $actualSuccess = $result['success'] ?? null;

        $this->assertSame($expectedSuccess, $actualSuccess);

        $expectedMessage = 'Email will not be sent in development mode';
        $actualMessage = $result['message'] ?? null;

        $this->assertSame($expectedMessage, $actualMessage);
    }

    public static function devModeProvider(): array {
        return [
            'missing flag (null)' => [
                'sendEmailFlag' => null,
            ],
            'false boolean' => [
                'sendEmailFlag' => false,
            ],
            'zero int' => [
                'sendEmailFlag' => 0,
            ],
            'empty string' => [
                'sendEmailFlag' => '',
            ],
            'string false' => [
                'sendEmailFlag' => '0',
            ],
        ];
    }

    private function backupF3(): void {
        foreach ($this->f3Keys as $key) {
            if ($this->f3->exists($key)) {
                $this->f3Backup[$key] = $this->f3->get($key);
            }
        }
    }

    private function clearF3(): void {
        foreach ($this->f3Keys as $key) {
            $this->f3->clear($key);
        }
    }

    private function restoreF3(): void {
        foreach ($this->f3Keys as $key) {
            $this->f3->clear($key);
        }

        foreach ($this->f3Backup as $key => $value) {
            $this->f3->set($key, $value);
        }
    }
}
