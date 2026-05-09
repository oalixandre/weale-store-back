<?php

declare(strict_types=1);

namespace Weale\Domain\User\ValueObjects;

use InvalidArgumentException;

final class HashedPassword
{
    private string $hash;

    private function __construct(string $hash)
    {
        $this->hash = $hash;
    }

    public static function fromPlainText(string $plainText): self
    {
        if (strlen($plainText) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters long.');
        }

        return new self(password_hash($plainText, PASSWORD_BCRYPT, ['cost' => 12]));
    }

    public static function fromHash(string $hash): self
    {
        return new self($hash);
    }

    public function verify(string $plainText): bool
    {
        return password_verify($plainText, $this->hash);
    }

    public function hash(): string
    {
        return $this->hash;
    }
}
