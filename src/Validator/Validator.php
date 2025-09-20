<?php

declare(strict_types=1);

namespace App\Validator;

use App\Validator\Assert\AssertInterface;

final class Validator
{
    /**
     * @var array<string, AssertInterface[]> $fieldsAsserts
     */
    private array $fieldsAsserts = [];

    private array $data = [];

    private array $errors = [];

    /**
     * @var AssertInterface[] $asserts
     */
    public function assertField(string $field, array $asserts): static
    {
        if (array_key_exists($field, $this->fieldsAsserts)) {
            throw new \Exception("The $field is already assigned in validator.");
        }

        if (empty($asserts)) {
            throw new \Exception("The $field must have some asserts.");
        }

        foreach ($asserts as $assert) {
            if (!$assert instanceof AssertInterface) {
                throw new \InvalidArgumentException("$field asserts must be instance of " . AssertInterface::class);
            }
        }

        $asserts = array_unique($asserts, SORT_REGULAR);

        $this->fieldsAsserts[$field] = $asserts;

        return $this;
    }

    public function getData(): array
    {
        return [
            'values' => $this->data,
            'errors' => $this->errors
        ];
    }

    public function addError(string $field, string $error): void
    {
        $this->errors[$field][] = $error;
    }

    public function valid(array $data): bool
    {
        $this->data = array_map(function (string $input) {
            return $this->sanitize($input);
        }, $data);

        foreach ($this->fieldsAsserts as $field => $asserts) {
            foreach ($asserts as $assert) {
                if (!$assert->validate($this->data[$field])) {
                    $this->addError($field, $assert->errorMessage());
                }
            }
        }

        return count($this->errors) === 0;
    }

    private function sanitize(string $input): string
    {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input);

        return $input;
    }
}
