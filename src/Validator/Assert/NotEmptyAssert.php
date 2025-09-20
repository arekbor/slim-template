<?php

declare(strict_types=1);

namespace App\Validator\Assert;

final class NotEmptyAssert implements AssertInterface
{
    public function validate(mixed $value): bool
    {
        return !empty($value);
    }

    public function errorMessage(): string
    {
        return "This field cannot be empty.";
    }
}
