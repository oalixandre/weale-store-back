<?php

declare(strict_types=1);

namespace Weale\Application\Order;

use DomainException;
use Weale\Domain\Order\Order;
use Weale\Domain\Order\OrderRepositoryInterface;
use Weale\Domain\Order\ValueObjects\OrderItem;
use Weale\Domain\Product\ProductRepositoryInterface;
use Weale\Domain\Product\ValueObjects\ProductId;
use Weale\Domain\Shared\EventDispatcher\EventDispatcherInterface;
use Weale\Domain\Shared\Exceptions\NotFoundException;
use Weale\Domain\User\ValueObjects\UserId;

final class PlaceOrderUseCase
{
    public function __construct(
        private readonly OrderRepositoryInterface   $orderRepository,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly EventDispatcherInterface   $dispatcher,
    ) {}

    public function execute(PlaceOrderCommand $command): OrderResponse
    {
        $userId = UserId::fromString($command->userId);
        $items  = [];

        foreach ($command->items as $itemData) {
            $productId = ProductId::fromString($itemData['product_id']);
            $product   = $this->productRepository->findById($productId);

            if ($product === null) {
                throw new NotFoundException("Product not found: {$itemData['product_id']}");
            }

            $quantity = (int) $itemData['quantity'];

            if ($product->stock() < $quantity) {
                throw new DomainException(
                    "Insufficient stock for product '{$product->name()}'. Available: {$product->stock()}"
                );
            }

            $items[] = OrderItem::create(
                productId:   $productId,
                productName: $product->name(),
                quantity:    $quantity,
                unitPrice:   $product->price(),
            );

            $product->decreaseStock($quantity);
            $this->productRepository->save($product);
        }

        $order = Order::place($userId, $items);
        $this->orderRepository->save($order);
        $this->dispatcher->dispatchAll($order->pullDomainEvents());

        return OrderResponse::fromOrder($order);
    }
}
