<?php

declare(strict_types=1);

namespace Weale\Tests\Unit\Infrastructure\EventDispatcher;

use Mockery;
use Mockery\MockInterface;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Weale\Domain\Shared\EventDispatcher\EventListenerInterface;
use Weale\Domain\Shared\Events\DomainEventInterface;
use Weale\Infrastructure\EventDispatcher\InMemoryEventDispatcher;

final class InMemoryEventDispatcherTest extends TestCase
{
    protected function tearDown(): void { Mockery::close(); }

    private function makeDispatcher(): InMemoryEventDispatcher
    {
        $logger = new Logger('test');
        $logger->pushHandler(new TestHandler());
        return new InMemoryEventDispatcher($logger);
    }

    private function makeEvent(string $name = 'test.event'): DomainEventInterface
    {
        $event = Mockery::mock(DomainEventInterface::class);
        $event->allows('eventName')->andReturn($name);
        $event->allows('occurredOn')->andReturn(new \DateTimeImmutable());
        return $event;
    }

    public function test_it_dispatches_to_subscribed_listener(): void
    {
        $dispatcher = $this->makeDispatcher();
        $event      = $this->makeEvent('order.placed');

        $listener = Mockery::mock(EventListenerInterface::class);
        $listener->shouldReceive('handle')->once()->with($event);

        $dispatcher->subscribe('order.placed', $listener);
        $dispatcher->dispatch($event);
    }

    public function test_it_does_not_call_unrelated_listener(): void
    {
        $dispatcher = $this->makeDispatcher();
        $event      = $this->makeEvent('product.created');

        $listener = Mockery::mock(EventListenerInterface::class);
        $listener->shouldNotReceive('handle');

        $dispatcher->subscribe('order.placed', $listener);
        $dispatcher->dispatch($event);
    }

    public function test_it_dispatches_multiple_events(): void
    {
        $dispatcher = $this->makeDispatcher();
        $event1     = $this->makeEvent('a.happened');
        $event2     = $this->makeEvent('b.happened');

        $listener = Mockery::mock(EventListenerInterface::class);
        $listener->shouldReceive('handle')->twice();

        $dispatcher->subscribe('a.happened', $listener);
        $dispatcher->subscribe('b.happened', $listener);

        $dispatcher->dispatchAll([$event1, $event2]);
    }

    public function test_dispatching_with_no_listeners_does_not_throw(): void
    {
        $dispatcher = $this->makeDispatcher();
        $dispatcher->dispatch($this->makeEvent('orphan.event'));
        $this->assertTrue(true); // no exception = pass
    }
}
