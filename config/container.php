<?php

declare(strict_types=1);

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Weale\Application\Order\GetOrderUseCase;
use Weale\Application\Order\Handlers\NotifyAdminOnOrderPlacedListener;
use Weale\Application\Order\PlaceOrderUseCase;
use Weale\Application\Order\UpdateOrderStatusUseCase;
use Weale\Application\Product\CreateProductUseCase;
use Weale\Application\Product\DeleteProductUseCase;
use Weale\Application\Product\GetProductUseCase;
use Weale\Application\Product\ListProductsUseCase;
use Weale\Application\Product\UpdateProductUseCase;
use Weale\Application\User\Handlers\SendWelcomeEmailListener;
use Weale\Application\User\LoginUseCase;
use Weale\Application\User\RegisterUserUseCase;
use Weale\Domain\Order\OrderRepositoryInterface;
use Weale\Domain\Product\ProductRepositoryInterface;
use Weale\Domain\Shared\EventDispatcher\EventDispatcherInterface;
use Weale\Domain\User\UserRepositoryInterface;
use Weale\Infrastructure\Cache\CacheInterface;
use Weale\Infrastructure\Cache\NullCache;
use Weale\Infrastructure\EventDispatcher\InMemoryEventDispatcher;
use Weale\Infrastructure\Http\Controllers\AuthController;
use Weale\Infrastructure\Http\Controllers\OrderController;
use Weale\Infrastructure\Http\Controllers\ProductController;
use Weale\Infrastructure\Http\Middleware\JwtAuthMiddleware;
use Weale\Infrastructure\Persistence\Repositories\CachedProductRepository;
use Weale\Infrastructure\Persistence\Repositories\DoctrineOrderRepository;
use Weale\Infrastructure\Persistence\Repositories\DoctrineProductRepository;
use Weale\Infrastructure\Persistence\Repositories\DoctrineUserRepository;

return [

    LoggerInterface::class => function (): LoggerInterface {
        $logger = new Logger($_ENV['APP_NAME'] ?? 'weale-store');
        $logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));
        return $logger;
    },

    ResponseFactoryInterface::class => fn () => new ResponseFactory(),

    EntityManagerInterface::class => function (): EntityManagerInterface {
        $isDevMode = ($_ENV['APP_ENV'] ?? 'production') !== 'production';
        $config    = ORMSetup::createAttributeMetadataConfiguration(
            paths:     [dirname(__DIR__) . '/src/Infrastructure/Persistence/Entities'],
            isDevMode: $isDevMode,
        );
        $connection = DriverManager::getConnection([
            'driver'   => 'pdo_pgsql',
            'host'     => $_ENV['DB_HOST']     ?? 'db',
            'port'     => (int) ($_ENV['DB_PORT'] ?? 5432),
            'dbname'   => $_ENV['DB_DATABASE']  ?? 'weale_store',
            'user'     => $_ENV['DB_USERNAME']  ?? 'weale',
            'password' => $_ENV['DB_PASSWORD']  ?? 'secret',
            'charset'  => 'utf8',
        ]);
        return new EntityManager($connection, $config);
    },

    // Cache — NullCache by default; swap for RedisCache in production
    CacheInterface::class => fn () => new NullCache(),

    // Event Dispatcher
    EventDispatcherInterface::class => function (ContainerInterface $c): EventDispatcherInterface {
        $dispatcher = new InMemoryEventDispatcher($c->get(LoggerInterface::class));
        $dispatcher->subscribe('user.registered', $c->get(SendWelcomeEmailListener::class));
        $dispatcher->subscribe('order.placed',    $c->get(NotifyAdminOnOrderPlacedListener::class));
        return $dispatcher;
    },

    // Listeners
    SendWelcomeEmailListener::class       => fn (ContainerInterface $c) => new SendWelcomeEmailListener($c->get(LoggerInterface::class)),
    NotifyAdminOnOrderPlacedListener::class => fn (ContainerInterface $c) => new NotifyAdminOnOrderPlacedListener($c->get(LoggerInterface::class)),

    // Repositories
    DoctrineProductRepository::class => fn (ContainerInterface $c) =>
        new DoctrineProductRepository($c->get(EntityManagerInterface::class)),

    ProductRepositoryInterface::class => fn (ContainerInterface $c) =>
        new CachedProductRepository(
            $c->get(DoctrineProductRepository::class),
            $c->get(CacheInterface::class),
        ),

    UserRepositoryInterface::class => fn (ContainerInterface $c) =>
        new DoctrineUserRepository($c->get(EntityManagerInterface::class)),

    OrderRepositoryInterface::class => fn (ContainerInterface $c) =>
        new DoctrineOrderRepository($c->get(EntityManagerInterface::class)),

    // Product Use Cases
    CreateProductUseCase::class  => fn (ContainerInterface $c) => new CreateProductUseCase($c->get(ProductRepositoryInterface::class)),
    GetProductUseCase::class     => fn (ContainerInterface $c) => new GetProductUseCase($c->get(ProductRepositoryInterface::class)),
    ListProductsUseCase::class   => fn (ContainerInterface $c) => new ListProductsUseCase($c->get(ProductRepositoryInterface::class)),
    UpdateProductUseCase::class  => fn (ContainerInterface $c) => new UpdateProductUseCase($c->get(ProductRepositoryInterface::class)),
    DeleteProductUseCase::class  => fn (ContainerInterface $c) => new DeleteProductUseCase($c->get(ProductRepositoryInterface::class)),

    // User Use Cases
    RegisterUserUseCase::class => fn (ContainerInterface $c) => new RegisterUserUseCase(
        $c->get(UserRepositoryInterface::class),
        $c->get(EventDispatcherInterface::class),
    ),
    LoginUseCase::class => fn (ContainerInterface $c) => new LoginUseCase($c->get(UserRepositoryInterface::class)),

    // Order Use Cases
    PlaceOrderUseCase::class => fn (ContainerInterface $c) => new PlaceOrderUseCase(
        $c->get(OrderRepositoryInterface::class),
        $c->get(ProductRepositoryInterface::class),
        $c->get(EventDispatcherInterface::class),
    ),
    GetOrderUseCase::class          => fn (ContainerInterface $c) => new GetOrderUseCase($c->get(OrderRepositoryInterface::class)),
    UpdateOrderStatusUseCase::class => fn (ContainerInterface $c) => new UpdateOrderStatusUseCase($c->get(OrderRepositoryInterface::class)),

    // Controllers
    ProductController::class => fn (ContainerInterface $c) => new ProductController(
        $c->get(CreateProductUseCase::class),
        $c->get(GetProductUseCase::class),
        $c->get(ListProductsUseCase::class),
        $c->get(DeleteProductUseCase::class),
        $c->get(UpdateProductUseCase::class),
    ),
    AuthController::class => fn (ContainerInterface $c) => new AuthController(
        $c->get(RegisterUserUseCase::class),
        $c->get(LoginUseCase::class),
    ),
    OrderController::class => fn (ContainerInterface $c) => new OrderController(
        $c->get(PlaceOrderUseCase::class),
        $c->get(GetOrderUseCase::class),
        $c->get(UpdateOrderStatusUseCase::class),
        $c->get(OrderRepositoryInterface::class),
    ),

    JwtAuthMiddleware::class => fn (ContainerInterface $c) =>
        new JwtAuthMiddleware($c->get(ResponseFactoryInterface::class)),
];
