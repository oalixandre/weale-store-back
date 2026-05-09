<?php

declare(strict_types=1);

namespace Weale\Tests\Feature;

use Weale\Tests\Helpers\HttpTestCase;

/**
 * @group feature
 */
final class OrdersTest extends HttpTestCase
{
    private string $authToken   = '';
    private string $productId   = '';

    protected function setUp(): void
    {
        parent::setUp();
        $this->authToken = $this->registerAndLogin();
        $this->productId = $this->createProduct();
    }

    private function registerAndLogin(): string
    {
        $email = 'order_user_' . uniqid() . '@example.com';
        $this->post('/api/v1/auth/register', [
            'name' => 'Order User', 'email' => $email, 'password' => 'password123',
        ]);
        $res = json_decode((string) $this->post('/api/v1/auth/login', [
            'email' => $email, 'password' => 'password123',
        ])->getBody(), true);
        return $res['access_token'] ?? '';
    }

    private function createProduct(int $stock = 100): string
    {
        $res = json_decode((string) $this->post('/api/v1/products', [
            'name'     => 'Order Product ' . uniqid(),
            'price'    => 50.00,
            'stock'    => $stock,
            'category' => 'test',
        ], $this->withToken($this->authToken))->getBody(), true);
        return $res['data']['id'] ?? '';
    }

    public function test_place_order_succeeds(): void
    {
        $data = $this->assertJsonResponse(
            $this->post('/api/v1/orders', [
                'items' => [['product_id' => $this->productId, 'quantity' => 2]],
            ], $this->withToken($this->authToken)),
            201
        );

        $this->assertEquals('pending', $data['data']['status']);
        $this->assertEquals(100.0, $data['data']['total']);
        $this->assertCount(1, $data['data']['items']);
        $this->assertArrayHasKey('id', $data['data']);
    }

    public function test_place_order_requires_auth(): void
    {
        $response = $this->post('/api/v1/orders', [
            'items' => [['product_id' => $this->productId, 'quantity' => 1]],
        ]);
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_place_order_fails_on_insufficient_stock(): void
    {
        $lowStockProduct = $this->createProduct(stock: 1);

        $data = $this->assertJsonResponse(
            $this->post('/api/v1/orders', [
                'items' => [['product_id' => $lowStockProduct, 'quantity' => 99]],
            ], $this->withToken($this->authToken)),
            422
        );

        $this->assertArrayHasKey('error', $data);
    }

    public function test_get_order_by_id(): void
    {
        $placed = $this->assertJsonResponse(
            $this->post('/api/v1/orders', [
                'items' => [['product_id' => $this->productId, 'quantity' => 1]],
            ], $this->withToken($this->authToken)),
            201
        );

        $orderId = $placed['data']['id'];

        $data = $this->assertJsonResponse(
            $this->get("/api/v1/orders/{$orderId}", $this->withToken($this->authToken))
        );

        $this->assertEquals($orderId, $data['data']['id']);
        $this->assertEquals('pending', $data['data']['status']);
    }

    public function test_update_order_status_to_confirmed(): void
    {
        $placed  = $this->assertJsonResponse(
            $this->post('/api/v1/orders', [
                'items' => [['product_id' => $this->productId, 'quantity' => 1]],
            ], $this->withToken($this->authToken)),
            201
        );

        $orderId = $placed['data']['id'];

        $data = $this->assertJsonResponse(
            $this->patch("/api/v1/orders/{$orderId}/status",
                ['status' => 'confirmed'],
                $this->withToken($this->authToken)
            )
        );

        $this->assertEquals('confirmed', $data['data']['status']);
    }

    public function test_invalid_status_transition_returns_422(): void
    {
        $placed  = $this->assertJsonResponse(
            $this->post('/api/v1/orders', [
                'items' => [['product_id' => $this->productId, 'quantity' => 1]],
            ], $this->withToken($this->authToken)),
            201
        );

        $orderId = $placed['data']['id'];

        // pending -> delivered is invalid
        $data = $this->assertJsonResponse(
            $this->patch("/api/v1/orders/{$orderId}/status",
                ['status' => 'delivered'],
                $this->withToken($this->authToken)
            ),
            422
        );

        $this->assertArrayHasKey('error', $data);
    }

    public function test_list_orders_requires_auth(): void
    {
        $response = $this->get('/api/v1/orders');
        $this->assertEquals(401, $response->getStatusCode());
    }
}
