<?php

declare(strict_types=1);

namespace App\Helpers;

class Validator
{
    private array $errors = [];

    public function required(string $field, mixed $value, string $message): self
    {
        if ($value === null || trim((string) $value) === '') {
            $this->errors[$field][] = $message;
        }
        return $this;
    }

    public function minLength(string $field, ?string $value, int $min, string $message): self
    {
        if ($value !== null && mb_strlen($value) < $min) {
            $this->errors[$field][] = $message;
        }
        return $this;
    }

    public function in(string $field, mixed $value, array $allowed, string $message): self
    {
        if (!in_array($value, $allowed, true)) {
            $this->errors[$field][] = $message;
        }
        return $this;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }
}
