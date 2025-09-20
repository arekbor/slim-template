<?php

declare(strict_types=1);

namespace App\Validator\Assert;

final class PasswordMatchAssert implements AssertInterface
{
    public function __construct(
        private readonly string $passwordToMatch
    ) {}

    public function validate(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return $value === $this->passwordToMatch;
    }

    public function errorMessage(): string
    {
        return "Passwords do not match.";
    }
}
