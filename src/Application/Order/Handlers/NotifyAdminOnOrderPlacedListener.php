<?php

declare(strict_types=1);

namespace Weale\Application\Order\Handlers;

use Psr\Log\LoggerInterface;
use Weale\Domain\Order\Events\OrderPlacedEvent;
use Weale\Domain\Shared\EventDispatcher\EventListenerInterface;
use Weale\Domain\Shared\Events\DomainEventInterface;

final class NotifyAdminOnOrderPlacedListener implements EventListenerInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function handle(DomainEventInterface $event): void
    {
        if (!$event instanceof OrderPlacedEvent) {
            return;
        }

        $this->logger->info('New order placed — admin notified', [
            'order_id' => $event->orderId()->value(),
            'user_id'  => $event->userId()->value(),
            'total'    => $event->total()->amount(),
        ]);
    }
}
