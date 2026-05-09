<?php

declare(strict_types=1);

namespace Weale\Domain\Shared\EventDispatcher;

use Weale\Domain\Shared\Events\DomainEventInterface;

interface EventListenerInterface
{
    public function handle(DomainEventInterface $event): void;
}
