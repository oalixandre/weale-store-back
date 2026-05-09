<?php

declare(strict_types=1);

namespace Weale\Domain\Product;

use DateTimeImmutable;
use InvalidArgumentException;
use Weale\Domain\Product\ValueObjects\Money;
use Weale\Domain\Product\ValueObjects\ProductId;
use Weale\Domain\Shared\Events\DomainEventInterface;

final class Product
{
    private ProductId $id;
    private string $name;
    private string $description;
    private Money $price;
    private int $stock;
    private string $category;
    private bool $active;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    /** @var DomainEventInterface[] */
    private array $domainEvents = [];

    private function __construct(
        ProductId $id,
        string $name,
        string $description,
        Money $price,
        int $stock,
        string $category,
    ) {
        $this->id          = $id;
        $this->name        = $name;
        $this->description = $description;
        $this->price       = $price;
        $this->stock       = $stock;
        $this->category    = $category;
        $this->active      = true;
        $this->createdAt   = new DateTimeImmutable();
        $this->updatedAt   = new DateTimeImmutable();
    }

    public static function create(
        string $name,
        string $description,
        Money $price,
        int $stock,
        string $category,
    ): self {
        if (empty(trim($name))) {
            throw new InvalidArgumentException('Product name cannot be empty.');
        }
        if ($stock < 0) {
            throw new InvalidArgumentException('Stock cannot be negative.');
        }

        $product = new self(
            ProductId::generate(),
            trim($name),
            trim($description),
            $price,
            $stock,
            trim($category),
        );

        $product->recordEvent(new Events\ProductCreatedEvent($product->id, $name));

        return $product;
    }

    public static function reconstitute(
        ProductId $id,
        string $name,
        string $description,
        Money $price,
        int $stock,
        string $category,
        bool $active,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        $product              = new self($id, $name, $description, $price, $stock, $category);
        $product->active      = $active;
        $product->createdAt   = $createdAt;
        $product->updatedAt   = $updatedAt;
        $product->domainEvents = [];
        return $product;
    }

    public function update(string $name, string $description, Money $price, string $category): void
    {
        if (empty(trim($name))) {
            throw new InvalidArgumentException('Product name cannot be empty.');
        }
        $this->name        = trim($name);
        $this->description = trim($description);
        $this->price       = $price;
        $this->category    = trim($category);
        $this->updatedAt   = new DateTimeImmutable();
    }

    public function addStock(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Quantity to add must be positive.');
        }
        $this->stock += $quantity;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function decreaseStock(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Quantity to decrease must be positive.');
        }
        if ($this->stock < $quantity) {
            throw new \DomainException("Insufficient stock. Available: {$this->stock}");
        }
        $this->stock -= $quantity;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function deactivate(): void
    {
        $this->active    = false;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function activate(): void
    {
        $this->active    = true;
        $this->updatedAt = new DateTimeImmutable();
    }

    // Getters
    public function id(): ProductId          { return $this->id; }
    public function name(): string           { return $this->name; }
    public function description(): string    { return $this->description; }
    public function price(): Money           { return $this->price; }
    public function stock(): int             { return $this->stock; }
    public function category(): string       { return $this->category; }
    public function isActive(): bool         { return $this->active; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): DateTimeImmutable { return $this->updatedAt; }

    private function recordEvent(DomainEventInterface $event): void
    {
        $this->domainEvents[] = $event;
    }

    /** @return DomainEventInterface[] */
    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];
        return $events;
    }
}
