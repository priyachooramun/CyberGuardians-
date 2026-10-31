<?php
/**
 * Server-side validation (the browser checks in assets/js/validation.js can be
 * switched off by an attacker, so the server checks everything again).
 * 
 */

declare(strict_types=1);

final class Validator
{
    private array $errors = [];

    public function __construct(private readonly array $data)
    {
    }

    /** Trimmed string value with control characters removed. */
    public function clean(string $field): string
    {
        $value = $this->data[$field] ?? '';
        if (!is_string($value)) {
            return '';
        }
        return trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '');
    }

    public function required(string $field, string $label): self
    {
        if ($this->clean($field) === '') {
            $this->add($field, "{$label} is required.");
        }
        return $this;
    }

    public function maxLength(string $field, int $max, string $label): self
    {
        if (mb_strlen($this->clean($field)) > $max) {
            $this->add($field, "{$label} must be at most {$max} characters.");
        }
        return $this;
    }

    public function minLength(string $field, int $min, string $label): self
    {
        $value = $this->clean($field);
        if ($value !== '' && mb_strlen($value) < $min) {
            $this->add($field, "{$label} must be at least {$min} characters.");
        }
        return $this;
    }

    public function name(string $field, string $label = 'Name'): self
    {
        $value = $this->clean($field);
        if ($value !== '' && !preg_match("/^[\p{L} .'-]{2,100}$/u", $value)) {
            $this->add($field, "{$label} may only contain letters, spaces, apostrophes and hyphens.");
        }
        return $this;
    }

    public function email(string $field = 'email'): self
    {
        $value = $this->clean($field);
        if ($value !== '' && (strlen($value) > 100 || !filter_var($value, FILTER_VALIDATE_EMAIL))) {
            $this->add($field, 'Please enter a valid email address.');
        }
        return $this;
    }

    /** Mauritian numbers: 7 digits (landline) or 8 digits starting with 5 (mobile), optional +230. */
    public function phone(string $field = 'phone'): self
    {
        $value = preg_replace('/[\s-]/', '', $this->clean($field));
        if ($value !== '' && !preg_match('/^(\+230)?(5\d{7}|\d{7})$/', $value)) {
            $this->add($field, 'Please enter a valid Mauritian phone number (e.g. 5712 3456).');
        }
        return $this;
    }

    /** At least 8 characters with an uppercase letter, a lowercase letter and a number. */
    public function strongPassword(string $field = 'password'): self
    {
        $value = (string)($this->data[$field] ?? '');
        if (strlen($value) < 8 || strlen($value) > 72
            || !preg_match('/[A-Z]/', $value) || !preg_match('/[a-z]/', $value) || !preg_match('/\d/', $value)) {
            $this->add($field, 'Password needs 8–72 characters with an uppercase letter, a lowercase letter and a number.');
        }
        return $this;
    }

    public function matches(string $field, string $other, string $message): self
    {
        if (($this->data[$field] ?? '') !== ($this->data[$other] ?? null)) {
            $this->add($other, $message);
        }
        return $this;
    }

    public function money(string $field, string $label, bool $required = true): self
    {
        $value = $this->clean($field);
        if ($value === '' && !$required) {
            return $this;
        }
        if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $value) || (float)$value <= 0) {
            $this->add($field, "{$label} must be a positive amount (e.g. 120 or 99.50).");
        }
        return $this;
    }

    public function integer(string $field, string $label, int $min = 0, int $max = PHP_INT_MAX): self
    {
        $value = filter_var($this->clean($field), FILTER_VALIDATE_INT, ['options' => ['min_range' => $min, 'max_range' => $max]]);
        if ($value === false) {
            $this->add($field, "{$label} must be a whole number between {$min} and {$max}.");
        }
        return $this;
    }

    public function in(string $field, array $allowed, string $label): self
    {
        if (!in_array($this->clean($field), $allowed, true)) {
            $this->add($field, "Please choose a valid {$label}.");
        }
        return $this;
    }

    public function add(string $field, string $message): void
    {
        $this->errors[$field] ??= $message;   // keep the first error per field
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function errors(): array
    {
        return $this->errors;
    }
}

/** Bootstrap helpers for showing errors under fields. */
function field_class(array $errors, string $field): string
{
    return isset($errors[$field]) ? ' is-invalid' : '';
}

function field_error(array $errors, string $field): string
{
    return isset($errors[$field]) ? '<div class="invalid-feedback d-block">' . e($errors[$field]) . '</div>' : '';
}
