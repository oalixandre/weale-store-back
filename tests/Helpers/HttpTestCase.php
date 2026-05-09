<?php

declare(strict_types=1);

namespace Weale\Tests\Helpers;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Weale\Bootstrap\App as AppFactory;

abstract class HttpTestCase extends TestCase
{
    protected App $app;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = AppFactory::create();
    }

    protected function get(string $uri, array $headers = []): ResponseInterface
    {
        return $this->request('GET', $uri, null, $headers);
    }

    protected function post(string $uri, mixed $body = null, array $headers = []): ResponseInterface
    {
        return $this->request('POST', $uri, $body, $headers);
    }

    protected function put(string $uri, mixed $body = null, array $headers = []): ResponseInterface
    {
        return $this->request('PUT', $uri, $body, $headers);
    }

    protected function patch(string $uri, mixed $body = null, array $headers = []): ResponseInterface
    {
        return $this->request('PATCH', $uri, $body, $headers);
    }

    protected function delete(string $uri, array $headers = []): ResponseInterface
    {
        return $this->request('DELETE', $uri, null, $headers);
    }

    protected function request(
        string $method,
        string $uri,
        mixed  $body    = null,
        array  $headers = [],
    ): ResponseInterface {
        $factory = new ServerRequestFactory();
        $request = $factory->createServerRequest($method, $uri);

        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $request->withHeader('Accept', 'application/json');

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if ($body !== null) {
            $stream  = (new StreamFactory())->createStream(json_encode($body));
            $request = $request->withBody($stream)->withParsedBody($body);
        }

        return $this->app->handle($request);
    }

    protected function withToken(string $token): array
    {
        return ['Authorization' => "Bearer {$token}"];
    }

    protected function assertJsonResponse(ResponseInterface $response, int $statusCode = 200): array
    {
        $this->assertEquals($statusCode, $response->getStatusCode());
        $this->assertStringContainsString('application/json', $response->getHeaderLine('Content-Type'));

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertIsArray($data, "Response body is not valid JSON: {$body}");

        return $data;
    }
}
