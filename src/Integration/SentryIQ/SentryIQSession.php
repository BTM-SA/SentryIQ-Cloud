<?php

declare(strict_types=1);

namespace SentryIQCloud\Integration\SentryIQ;

use RuntimeException;
use SentryIQCloud\Contracts\AuthenticationInterface;
use SentryIQCloud\Contracts\CsrfTokenInterface;

/**
 * Adapter for the existing SentryIQ session.
 *
 * Cloud does not create or validate a second credential/session. The host
 * application is responsible for bootstrapping the SentryIQ security session
 * before this adapter is used.
 */
final class SentryIQSession implements AuthenticationInterface, CsrfTokenInterface
{
    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new RuntimeException('SentryIQ session must be active before Cloud integration.');
        }
    }

    public function isAuthenticated(): bool
    {
        $key = $_SESSION['master_key'] ?? null;

        return is_string($key) && strlen($key) === 32;
    }

    public function userId(): string
    {
        $username = $_SESSION['app_username'] ?? '';

        if (!is_string($username) || $username === '') {
            throw new RuntimeException('Authenticated SentryIQ user is unavailable.');
        }

        return $username;
    }

    public function token(): string
    {
        $token = $_SESSION['csrf_token'] ?? '';

        if (!is_string($token) || $token === '') {
            throw new RuntimeException('SentryIQ CSRF token is unavailable.');
        }

        return $token;
    }

    public function isValid(string $token): bool
    {
        $expected = $_SESSION['csrf_token'] ?? null;

        return is_string($expected)
            && $expected !== ''
            && $token !== ''
            && hash_equals($expected, $token);
    }
}
