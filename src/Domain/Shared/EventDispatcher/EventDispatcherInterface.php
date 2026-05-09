<?php

declare(strict_types=1);

namespace Weale\Domain\Shared\EventDispatcher;

use Weale\Domain\Shared\Events\DomainEventInterface;

interface EventDispatcherInterface
{
    public function dispatch(DomainEventInterface $event): void;

    /** @param DomainEventInterface[] $events */
    public function dispatchAll(array $events): void;

    public function subscribe(string $eventName, EventListenerInterface $listener): void;
}
