<?php

declare(strict_types=1);

namespace Weale\Infrastructure\Persistence\Entities;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'products')]
class ProductEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\Column(type: 'string', length: 255)]
    public string $name;

    #[ORM\Column(type: 'text')]
    public string $description;

    #[ORM\Column(type: 'integer', name: 'price_amount_cents')]
    public int $priceAmountCents;

    #[ORM\Column(type: 'string', length: 3, name: 'price_currency')]
    public string $priceCurrency;

    #[ORM\Column(type: 'integer')]
    public int $stock;

    #[ORM\Column(type: 'string', length: 100)]
    public string $category;

    #[ORM\Column(type: 'boolean')]
    public bool $active;

    #[ORM\Column(type: 'datetime_immutable', name: 'created_at')]
    public DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', name: 'updated_at')]
    public DateTimeImmutable $updatedAt;
}
