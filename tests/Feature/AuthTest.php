<?php

declare(strict_types=1);

namespace Weale\Tests\Feature;

use Weale\Tests\Helpers\HttpTestCase;

/**
 * @group feature
 */
final class AuthTest extends HttpTestCase
{
    public function test_register_creates_user(): void
    {
        $data = $this->assertJsonResponse(
            $this->post('/api/v1/auth/register', [
                'name'     => 'John Doe',
                'email'    => 'john_' . uniqid() . '@example.com',
                'password' => 'password123',
            ]),
            201
        );

        $this->assertArrayHasKey('data', $data);
        $this->assertEquals('John Doe', $data['data']['name']);
        $this->assertEquals('customer', $data['data']['role']);
        $this->assertTrue($data['data']['active']);
        $this->assertArrayNotHasKey('password', $data['data']);
    }

    public function test_register_rejects_invalid_email(): void
    {
        $data = $this->assertJsonResponse(
            $this->post('/api/v1/auth/register', [
                'name'     => 'Bob',
                'email'    => 'not-an-email',
                'password' => 'password123',
            ]),
            422
        );

        $this->assertArrayHasKey('error', $data);
    }

    public function test_register_rejects_short_password(): void
    {
        $data = $this->assertJsonResponse(
            $this->post('/api/v1/auth/register', [
                'name'     => 'Bob',
                'email'    => 'bob@example.com',
                'password' => '123',
            ]),
            422
        );

        $this->assertArrayHasKey('error', $data);
    }

    public function test_login_returns_jwt_token(): void
    {
        $email = 'login_test_' . uniqid() . '@example.com';

        // Register first
        $this->post('/api/v1/auth/register', [
            'name'     => 'Test User',
            'email'    => $email,
            'password' => 'mypassword',
        ]);

        // Then login
        $data = $this->assertJsonResponse(
            $this->post('/api/v1/auth/login', [
                'email'    => $email,
                'password' => 'mypassword',
            ]),
            200
        );

        $this->assertArrayHasKey('access_token', $data);
        $this->assertEquals('Bearer', $data['token_type']);
        $this->assertIsInt($data['expires_in']);
        $this->assertArrayHasKey('user', $data);
    }

    public function test_login_rejects_wrong_password(): void
    {
        $email = 'wrong_pw_' . uniqid() . '@example.com';

        $this->post('/api/v1/auth/register', [
            'name' => 'Test', 'email' => $email, 'password' => 'correctpassword',
        ]);

        $data = $this->assertJsonResponse(
            $this->post('/api/v1/auth/login', [
                'email'    => $email,
                'password' => 'wrongpassword',
            ]),
            401
        );

        $this->assertArrayHasKey('error', $data);
    }

    public function test_login_rejects_unknown_user(): void
    {
        $data = $this->assertJsonResponse(
            $this->post('/api/v1/auth/login', [
                'email'    => 'nobody@example.com',
                'password' => 'whatever',
            ]),
            401
        );

        $this->assertArrayHasKey('error', $data);
    }

    public function test_duplicate_email_registration_fails(): void
    {
        $email = 'dup_' . uniqid() . '@example.com';
        $payload = ['name' => 'A', 'email' => $email, 'password' => 'password123'];

        $this->post('/api/v1/auth/register', $payload);
        $data = $this->assertJsonResponse(
            $this->post('/api/v1/auth/register', $payload),
            422
        );

        $this->assertArrayHasKey('error', $data);
    }
}
