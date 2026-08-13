<?php
declare(strict_types=1);

final class Validator
{
    private array $errors = [];
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function required(string $field, string $label): self
    {
        if (trim((string)($this->data[$field] ?? '')) === '') {
            $this->errors[$field] = "{$label} is required.";
        }
        return $this;
    }

    public function email(string $field, string $label = 'Email'): self
    {
        $value = (string)($this->data[$field] ?? '');
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "{$label} must be a valid email address.";
        }
        return $this;
    }

    public function phone(string $field, string $label = 'Phone'): self
    {
        $value = (string)($this->data[$field] ?? '');
        if ($value !== '' && !preg_match('/^[0-9+\-\s()]{7,20}$/', $value)) {
            $this->errors[$field] = "{$label} is not a valid phone number.";
        }
        return $this;
    }

    public function minLength(string $field, int $len, string $label): self
    {
        $value = (string)($this->data[$field] ?? '');
        if (strlen($value) < $len) {
            $this->errors[$field] = "{$label} must be at least {$len} characters.";
        }
        return $this;
    }

    public function maxLength(string $field, int $len, string $label): self
    {
        $value = (string)($this->data[$field] ?? '');
        if (strlen($value) > $len) {
            $this->errors[$field] = "{$label} must not exceed {$len} characters.";
        }
        return $this;
    }

    public function numeric(string $field, string $label): self
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && !is_numeric($value)) {
            $this->errors[$field] = "{$label} must be a number.";
        }
        return $this;
    }

    public function inArray(string $field, array $allowed, string $label): self
    {
        $value = $this->data[$field] ?? null;
        if ($value !== null && $value !== '' && !in_array($value, $allowed, true)) {
            $this->errors[$field] = "{$label} is invalid.";
        }
        return $this;
    }

    public function range(string $field, float $min, float $max, string $label): self
    {
        $value = $this->data[$field] ?? null;
        if ($value !== null && $value !== '' && (!is_numeric($value) || $value < $min || $value > $max)) {
            $this->errors[$field] = "{$label} must be between {$min} and {$max}.";
        }
        return $this;
    }

    public function futureDate(string $field, string $label): self
    {
        $value = (string)($this->data[$field] ?? '');
        if ($value !== '') {
            $ts = strtotime($value);
            if ($ts === false || $ts < strtotime('today')) {
                $this->errors[$field] = "{$label} must be today or a future date.";
            }
        }
        return $this;
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function first(): ?string
    {
        return $this->errors === [] ? null : array_values($this->errors)[0];
    }
}
