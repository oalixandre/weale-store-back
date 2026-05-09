<?php

declare(strict_types=1);

namespace Weale\Domain\Product\ValueObjects;

use InvalidArgumentException;

final class Money
{
    private int $amountInCents;
    private string $currency;

    public function __construct(int $amountInCents, string $currency = 'BRL')
    {
        if ($amountInCents < 0) {
            throw new InvalidArgumentException('Amount cannot be negative.');
        }
        $this->amountInCents = $amountInCents;
        $this->currency = strtoupper($currency);
    }

    public static function fromFloat(float $amount, string $currency = 'BRL'): self
    {
        return new self((int) round($amount * 100), $currency);
    }

    public function amountInCents(): int
    {
        return $this->amountInCents;
    }

    public function amount(): float
    {
        return $this->amountInCents / 100;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function add(self $other): self
    {
        $this->assertSameCurrency($other);
        return new self($this->amountInCents + $other->amountInCents, $this->currency);
    }

    public function subtract(self $other): self
    {
        $this->assertSameCurrency($other);
        return new self($this->amountInCents - $other->amountInCents, $this->currency);
    }

    public function equals(self $other): bool
    {
        return $this->amountInCents === $other->amountInCents
            && $this->currency === $other->currency;
    }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                "Cannot operate on different currencies: {$this->currency} and {$other->currency}"
            );
        }
    }
}
