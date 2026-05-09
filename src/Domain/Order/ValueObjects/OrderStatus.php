<?php

declare(strict_types=1);

namespace Weale\Domain\Order\ValueObjects;

enum OrderStatus: string
{
    case PENDING   = 'pending';
    case CONFIRMED = 'confirmed';
    case SHIPPED   = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public function canTransitionTo(self $next): bool
    {
        return match($this) {
            self::PENDING   => in_array($next, [self::CONFIRMED, self::CANCELLED]),
            self::CONFIRMED => in_array($next, [self::SHIPPED,   self::CANCELLED]),
            self::SHIPPED   => $next === self::DELIVERED,
            default         => false,
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::DELIVERED, self::CANCELLED]);
    }
}
