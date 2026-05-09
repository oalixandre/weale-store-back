<?php

declare(strict_types=1);

namespace Weale\Infrastructure\Http\Controllers;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Weale\Application\Product\CreateProductCommand;
use Weale\Application\Product\CreateProductUseCase;
use Weale\Application\Product\DeleteProductUseCase;
use Weale\Application\Product\GetProductUseCase;
use Weale\Application\Product\ListProductsUseCase;
use Weale\Application\Product\UpdateProductCommand;
use Weale\Application\Product\UpdateProductUseCase;
use Weale\Domain\Shared\Exceptions\NotFoundException;

final class ProductController
{
    public function __construct(
        private readonly CreateProductUseCase  $createProduct,
        private readonly GetProductUseCase     $getProduct,
        private readonly ListProductsUseCase   $listProducts,
        private readonly DeleteProductUseCase  $deleteProduct,
        private readonly UpdateProductUseCase  $updateProduct,
    ) {}

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params  = $request->getQueryParams();
        $page    = max(1, (int) ($params['page']     ?? 1));
        $perPage = min(100, max(1, (int) ($params['per_page'] ?? 20)));

        $result = $this->listProducts->execute($page, $perPage);

        return $this->json($response, [
            'data' => array_map(fn ($p) => $p->toArray(), $result['data']),
            'meta' => [
                'total'    => $result['total'],
                'page'     => $result['page'],
                'per_page' => $result['per_page'],
                'pages'    => (int) ceil(max(1, $result['total']) / $result['per_page']),
            ],
        ]);
    }

    public function show(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $product = $this->getProduct->execute($args['id']);
            return $this->json($response, ['data' => $product->toArray()]);
        } catch (NotFoundException $e) {
            return $this->json($response, ['error' => $e->getMessage()], 404);
        } catch (InvalidArgumentException $e) {
            return $this->json($response, ['error' => 'Invalid product id.'], 422);
        }
    }

    public function store(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = (array) $request->getParsedBody();
        try {
            $this->validateCreatePayload($body);
            $command = new CreateProductCommand(
                name:        (string) ($body['name']        ?? ''),
                description: (string) ($body['description'] ?? ''),
                price:       (float)  ($body['price']       ?? 0),
                stock:       (int)    ($body['stock']       ?? 0),
                category:    (string) ($body['category']    ?? ''),
            );
            $product = $this->createProduct->execute($command);
            return $this->json($response, ['data' => $product->toArray()], 201);
        } catch (InvalidArgumentException $e) {
            return $this->json($response, ['error' => $e->getMessage()], 422);
        }
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $body = (array) $request->getParsedBody();
        try {
            $command = new UpdateProductCommand(
                name:        (string) ($body['name']        ?? ''),
                description: (string) ($body['description'] ?? ''),
                price:       (float)  ($body['price']       ?? 0),
                category:    (string) ($body['category']    ?? ''),
            );
            $product = $this->updateProduct->execute($args['id'], $command);
            return $this->json($response, ['data' => $product->toArray()]);
        } catch (NotFoundException $e) {
            return $this->json($response, ['error' => $e->getMessage()], 404);
        } catch (InvalidArgumentException $e) {
            return $this->json($response, ['error' => $e->getMessage()], 422);
        }
    }

    public function destroy(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $this->deleteProduct->execute($args['id']);
            return $response->withStatus(204);
        } catch (NotFoundException $e) {
            return $this->json($response, ['error' => $e->getMessage()], 404);
        } catch (InvalidArgumentException $e) {
            return $this->json($response, ['error' => 'Invalid product id.'], 422);
        }
    }

    private function json(ResponseInterface $response, mixed $data, int $status = 200): ResponseInterface
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    private function validateCreatePayload(array $body): void
    {
        foreach (['name', 'price', 'category'] as $field) {
            if (empty($body[$field])) {
                throw new InvalidArgumentException("Field '{$field}' is required.");
            }
        }
        if ((float) ($body['price'] ?? 0) < 0) {
            throw new InvalidArgumentException('Price must be non-negative.');
        }
    }
}
