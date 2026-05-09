<?php

declare(strict_types=1);

namespace Weale\Tests\Feature;

use Weale\Tests\Helpers\HttpTestCase;

/**
 * @group feature
 */
final class ProductsTest extends HttpTestCase
{
    private string $authToken = '';

    protected function setUp(): void
    {
        parent::setUp();
        $this->authToken = $this->registerAndLogin();
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function registerAndLogin(): string
    {
        $email = 'admin_' . uniqid() . '@example.com';

        $this->post('/api/v1/auth/register', [
            'name'     => 'Admin',
            'email'    => $email,
            'password' => 'password123',
        ]);

        $data = json_decode(
            (string) $this->post('/api/v1/auth/login', [
                'email'    => $email,
                'password' => 'password123',
            ])->getBody(),
            true
        );

        return $data['access_token'] ?? '';
    }

    private function createProduct(array $overrides = []): array
    {
        $payload = array_merge([
            'name'        => 'Product ' . uniqid(),
            'description' => 'A test product',
            'price'       => 99.99,
            'stock'       => 10,
            'category'    => 'electronics',
        ], $overrides);

        return $this->assertJsonResponse(
            $this->post('/api/v1/products', $payload, $this->withToken($this->authToken)),
            201
        );
    }

    // ── List ───────────────────────────────────────────────────────────────

    public function test_list_products_returns_paginated_response(): void
    {
        $data = $this->assertJsonResponse($this->get('/api/v1/products'));

        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('meta', $data);
        $this->assertArrayHasKey('total', $data['meta']);
        $this->assertArrayHasKey('page', $data['meta']);
    }

    public function test_list_respects_pagination_params(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->createProduct();
        }

        $data = $this->assertJsonResponse($this->get('/api/v1/products?page=1&per_page=2'));

        $this->assertCount(2, $data['data']);
        $this->assertEquals(1, $data['meta']['page']);
        $this->assertEquals(2, $data['meta']['per_page']);
    }

    // ── Create ─────────────────────────────────────────────────────────────

    public function test_create_product_succeeds(): void
    {
        $data = $this->createProduct(['name' => 'iPhone 16', 'price' => 5999.99, 'stock' => 50]);

        $this->assertEquals('iPhone 16', $data['data']['name']);
        $this->assertEquals(5999.99, $data['data']['price']);
        $this->assertEquals(50, $data['data']['stock']);
        $this->assertArrayHasKey('id', $data['data']);
    }

    public function test_create_product_requires_auth(): void
    {
        $data = $this->assertJsonResponse(
            $this->post('/api/v1/products', ['name' => 'X', 'price' => 10, 'category' => 'y']),
            401
        );
        $this->assertArrayHasKey('error', $data);
    }

    public function test_create_product_validates_required_fields(): void
    {
        $data = $this->assertJsonResponse(
            $this->post('/api/v1/products', ['description' => 'Missing name and category'], $this->withToken($this->authToken)),
            422
        );
        $this->assertArrayHasKey('error', $data);
    }

    // ── Show ───────────────────────────────────────────────────────────────

    public function test_show_returns_product(): void
    {
        $created = $this->createProduct(['name' => 'Visible Product']);
        $id      = $created['data']['id'];

        $data = $this->assertJsonResponse($this->get("/api/v1/products/{$id}"));

        $this->assertEquals('Visible Product', $data['data']['name']);
        $this->assertEquals($id, $data['data']['id']);
    }

    public function test_show_returns_404_for_unknown_id(): void
    {
        $data = $this->assertJsonResponse(
            $this->get('/api/v1/products/00000000-0000-4000-a000-000000000000'),
            404
        );
        $this->assertArrayHasKey('error', $data);
    }

    // ── Update ─────────────────────────────────────────────────────────────

    public function test_update_product_succeeds(): void
    {
        $created = $this->createProduct(['name' => 'Old Name']);
        $id      = $created['data']['id'];

        $data = $this->assertJsonResponse(
            $this->put("/api/v1/products/{$id}", [
                'name'        => 'New Name',
                'description' => 'Updated desc',
                'price'       => 199.99,
                'category'    => 'clothing',
            ], $this->withToken($this->authToken))
        );

        $this->assertEquals('New Name', $data['data']['name']);
        $this->assertEquals(199.99, $data['data']['price']);
        $this->assertEquals('clothing', $data['data']['category']);
    }

    public function test_update_requires_auth(): void
    {
        $created = $this->createProduct();
        $id      = $created['data']['id'];

        $response = $this->put("/api/v1/products/{$id}", ['name' => 'Hacked']);
        $this->assertEquals(401, $response->getStatusCode());
    }

    // ── Delete ─────────────────────────────────────────────────────────────

    public function test_delete_product_succeeds(): void
    {
        $created  = $this->createProduct();
        $id       = $created['data']['id'];
        $response = $this->delete("/api/v1/products/{$id}", $this->withToken($this->authToken));

        $this->assertEquals(204, $response->getStatusCode());

        // Confirm it's gone
        $this->assertJsonResponse($this->get("/api/v1/products/{$id}"), 404);
    }

    public function test_delete_requires_auth(): void
    {
        $created  = $this->createProduct();
        $id       = $created['data']['id'];
        $response = $this->delete("/api/v1/products/{$id}");

        $this->assertEquals(401, $response->getStatusCode());
    }
}
