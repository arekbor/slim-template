<?php

declare(strict_types=1);

namespace App\Validator\Assert;

interface AssertInterface
{
    public function validate(mixed $value): bool;
    public function errorMessage(): string;
}
