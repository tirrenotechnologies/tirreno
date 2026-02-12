<?php

declare(strict_types=1);

namespace Tests\Support;

use Tirreno\Utils\Http\HttpClient;
use Tirreno\Entities\HttpRequest;
use Tirreno\Entities\HttpResponse;

/**
 * FakeHttpClient is a deterministic test double for HttpClient.
 */
final class FakeHttpClient extends HttpClient {
    public static ?HttpRequest $lastRequest = null;

    public static bool $ok = true;
    public static ?int $code = 200;
    public static ?string $body = '{"ok":true}';
    public static ?string $error = null;

    public static function reset(): void {
        self::$lastRequest = null;

        $ok = true;
        self::$ok = $ok;

        $code = 200;
        self::$code = $code;

        $body = '{"ok":true}';
        self::$body = $body;

        $error = null;
        self::$error = $error;
    }

    /**
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct() {
        // Intentionally empty: parent requires transports, but this fake doesn't use them.
    }

    public function request(HttpRequest $request): HttpResponse {
        self::$lastRequest = $request;

        $headers = [];

        if (!self::$ok) {
            $result = HttpResponse::failure(self::$code, self::$error, $headers);
            return $result;
        }

        $result = HttpResponse::success(self::$code, self::$body, $headers);
        return $result;
    }
}
