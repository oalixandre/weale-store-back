<?php

declare(strict_types=1);

namespace Weale\Application\User\Handlers;

use Psr\Log\LoggerInterface;
use Weale\Domain\Shared\EventDispatcher\EventListenerInterface;
use Weale\Domain\Shared\Events\DomainEventInterface;
use Weale\Domain\User\Events\UserRegisteredEvent;

final class SendWelcomeEmailListener implements EventListenerInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function handle(DomainEventInterface $event): void
    {
        if (!$event instanceof UserRegisteredEvent) {
            return;
        }

        // In a real app this would enqueue a mail job
        $this->logger->info('Welcome email queued', [
            'user_id' => $event->userId()->value(),
            'email'   => $event->email()->value(),
        ]);
    }
}
