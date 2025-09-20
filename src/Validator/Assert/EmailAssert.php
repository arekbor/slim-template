<?php

declare(strict_types=1);

namespace App\Validator\Assert;

final class EmailAssert implements AssertInterface
{
    public function validate(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) ? true : false;
    }

    public function errorMessage(): string
    {
        return "Invalid email.";
    }
}
