<?php

declare(strict_types=1);

namespace App\Validator;

use App\Validator\Assert\AssertInterface;

final class FormValidator
{
    /**
     * @var array<string, array> List of form fields with their validation rules.
     */
    private array $fields = [];

    /**
     * @var string[] List of form-level errors not associated with any specific field.
     */
    private array $formErrors = [];

    /**
     * Adds a form field with validation rules.
     * 
     * @param string $fieldName Field name.
     * @param AssertInterface[] $asserts Array of objects implementing AssertInterface.
     * 
     * @return static
     * 
     * @throws \InvalidArgumentException If the field already exists or if one of the rules
     *  does not implement AssertInterface.
     * 
     */
    public function addField(string $fieldName, array $asserts = []): static
    {
        // Check if the field has already been defined.
        if (array_key_exists($fieldName, $this->fields)) {
            throw new \InvalidArgumentException("The field \"$fieldName\" is already defined in fields.");
        }

        foreach ($asserts as $assert) {
            if (!$assert instanceof AssertInterface) {
                throw new \InvalidArgumentException("Each assertion for field '$fieldName' must implement " . AssertInterface::class);
            }
        }

        // Remove duplicate validation rules.
        $asserts = array_unique($asserts, SORT_REGULAR);

        $this->fields[$fieldName] = [
            'asserts' => $asserts,
            'value' => null,
            'error' => null
        ];

        return $this;
    }

    /**
     * Validates the input data against all defined fields and rules.
     * 
     * @param array $body Input data to validate.
     * 
     * @return bool True if all fields are valid, false if at least one field fails validation.
     */
    public function valid(array $body): bool
    {
        $isValid = true;

        foreach (array_keys($this->fields) as $fieldName) {
            if (!array_key_exists($fieldName, $body)) {
                throw new \InvalidArgumentException("The field \"$fieldName\" not exists in body.");
            }

            $value = $body[$fieldName] ?? '';
            $this->fields[$fieldName]['value'] = $value;

            /**
             * @var AssertInterface $fieldAssert
             */
            foreach ($this->fields[$fieldName]['asserts'] as $fieldAssert) {
                if (!$fieldAssert->validate($this->fields[$fieldName]['value'])) {
                    $this->fields[$fieldName]['error'] = $fieldAssert->errorMessage();
                    $isValid = false;
                    break;
                }
            }
        }

        return $isValid;
    }

    /**
     * Sets an error message for the given form field.
     * 
     * @param string $fieldName Field name
     * @param string $message Error message
     * 
     * @throws \InvalidArgumentException If the given field does not exist in the fields collection.
     * 
     * @return static
     */
    public function setFieldError(string $fieldName, string $message): static
    {
        if (!array_key_exists($fieldName, $this->fields)) {
            throw new \InvalidArgumentException("The field \"$fieldName\" does not exist in fields.");
        }

        $this->fields[$fieldName]['error'] = $message;

        return $this;
    }

    /**
     * Adds a general form-level error.
     * 
     * @param string $message error message.
     * @return static
     */
    public function addFormError(string $message): static
    {
        $this->formErrors[] = $message;

        return $this;
    }

    /**
     * Returns form result that contains all validated fields and form level errors.
     * 
     * @return array{
     *  fields: array<string, array{value: mixed, error: string|null}>,
     *  formErrors: string[]
     * }
     */
    public function getForm(): array
    {
        $fields = array_map(function ($field) {
            return [
                'value' => $field['value'],
                'error' => $field['error'],
            ];
        }, $this->fields);

        return [
            'fields' => $fields,
            'formErrors' => $this->formErrors
        ];
    }
}
