<?php

declare(strict_types=1);

namespace Weale\Infrastructure\EventDispatcher;

use Psr\Log\LoggerInterface;
use Weale\Domain\Shared\EventDispatcher\EventDispatcherInterface;
use Weale\Domain\Shared\EventDispatcher\EventListenerInterface;
use Weale\Domain\Shared\Events\DomainEventInterface;

final class InMemoryEventDispatcher implements EventDispatcherInterface
{
    /** @var array<string, EventListenerInterface[]> */
    private array $listeners = [];

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function dispatch(DomainEventInterface $event): void
    {
        $eventName = $event->eventName();

        $this->logger->debug("Dispatching domain event: {$eventName}", [
            'event'       => $eventName,
            'occurred_on' => $event->occurredOn()->format(DATE_ATOM),
        ]);

        foreach ($this->listeners[$eventName] ?? [] as $listener) {
            $listener->handle($event);
        }
    }

    public function dispatchAll(array $events): void
    {
        foreach ($events as $event) {
            $this->dispatch($event);
        }
    }

    public function subscribe(string $eventName, EventListenerInterface $listener): void
    {
        $this->listeners[$eventName][] = $listener;
    }
}
