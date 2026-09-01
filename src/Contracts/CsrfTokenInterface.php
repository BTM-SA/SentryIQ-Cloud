<?php

declare(strict_types=1);

namespace SentryIQCloud\Contracts;

interface CsrfTokenInterface
{
    public function token(): string;

    public function isValid(string $token): bool;
}
