<?php

declare(strict_types=1);

namespace Weale\Infrastructure\Persistence\Entities;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'orders')]
class OrderEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\Column(type: 'string', length: 36, name: 'user_id')]
    public string $userId;

    #[ORM\Column(type: 'json')]
    public array $items;

    #[ORM\Column(type: 'string', length: 20)]
    public string $status;

    #[ORM\Column(type: 'integer', name: 'total_amount_cents')]
    public int $totalAmountCents;

    #[ORM\Column(type: 'string', length: 3, name: 'total_currency')]
    public string $totalCurrency;

    #[ORM\Column(type: 'datetime_immutable', name: 'created_at')]
    public DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', name: 'updated_at')]
    public DateTimeImmutable $updatedAt;
}
