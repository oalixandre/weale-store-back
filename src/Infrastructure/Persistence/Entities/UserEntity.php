<?php

declare(strict_types=1);

namespace Weale\Infrastructure\Persistence\Entities;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class UserEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\Column(type: 'string', length: 255)]
    public string $name;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    public string $email;

    #[ORM\Column(type: 'string', length: 255, name: 'password_hash')]
    public string $passwordHash;

    #[ORM\Column(type: 'string', length: 20)]
    public string $role;

    #[ORM\Column(type: 'boolean')]
    public bool $active;

    #[ORM\Column(type: 'datetime_immutable', name: 'created_at')]
    public DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', name: 'updated_at')]
    public DateTimeImmutable $updatedAt;
}
