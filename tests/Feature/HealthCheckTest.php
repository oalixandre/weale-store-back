<?php

declare(strict_types=1);

namespace Weale\Tests\Feature;

use Weale\Tests\Helpers\HttpTestCase;

/**
 * @group feature
 */
final class HealthCheckTest extends HttpTestCase
{
    public function test_health_endpoint_returns_ok(): void
    {
        $response = $this->get('/health');
        $data     = $this->assertJsonResponse($response, 200);

        $this->assertEquals('ok', $data['status']);
        $this->assertArrayHasKey('app', $data);
        $this->assertArrayHasKey('env', $data);
    }

    public function test_unknown_route_returns_404(): void
    {
        $response = $this->get('/does-not-exist');
        $this->assertEquals(404, $response->getStatusCode());
    }
}
