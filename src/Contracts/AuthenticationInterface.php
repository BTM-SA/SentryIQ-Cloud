<?php

declare(strict_types=1);

namespace SentryIQCloud\Contracts;

interface AuthenticationInterface
{
    public function isAuthenticated(): bool;

    public function userId(): string;
}
