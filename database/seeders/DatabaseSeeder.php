<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Dotenv\Dotenv;
use Weale\Domain\Product\Product;
use Weale\Domain\Product\ValueObjects\Money;
use Weale\Domain\User\User;
use Weale\Domain\User\ValueObjects\Email;
use Weale\Domain\User\ValueObjects\HashedPassword;
use Weale\Domain\User\ValueObjects\UserRole;
use Weale\Infrastructure\Persistence\Repositories\DoctrineProductRepository;
use Weale\Infrastructure\Persistence\Repositories\DoctrineUserRepository;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

$config = ORMSetup::createAttributeMetadataConfiguration(
    paths:     [__DIR__ . '/../src/Infrastructure/Persistence/Entities'],
    isDevMode: true,
);

$em = new EntityManager(
    DriverManager::getConnection([
        'driver'   => 'pdo_pgsql',
        'host'     => $_ENV['DB_HOST']     ?? 'db',
        'port'     => (int) ($_ENV['DB_PORT'] ?? 5432),
        'dbname'   => $_ENV['DB_DATABASE']  ?? 'weale_store',
        'user'     => $_ENV['DB_USERNAME']  ?? 'weale',
        'password' => $_ENV['DB_PASSWORD']  ?? 'secret',
    ]),
    $config
);

$userRepo    = new DoctrineUserRepository($em);
$productRepo = new DoctrineProductRepository($em);

echo "🌱 Seeding database...\n";

// ── Users ────────────────────────────────────────────────────────────────────

$admin = User::register(
    name:     'Admin Weale',
    email:    new Email('admin@weale.com'),
    password: HashedPassword::fromPlainText('admin123'),
    role:     UserRole::ADMIN,
);
$userRepo->save($admin);
echo "  ✓ Admin user created (admin@weale.com / admin123)\n";

$customer = User::register(
    name:     'João Cliente',
    email:    new Email('joao@example.com'),
    password: HashedPassword::fromPlainText('customer123'),
);
$userRepo->save($customer);
echo "  ✓ Customer user created (joao@example.com / customer123)\n";

// ── Products ─────────────────────────────────────────────────────────────────

$products = [
    ['name' => 'iPhone 15 Pro',       'category' => 'smartphones',  'price' => 7999.99, 'stock' => 50],
    ['name' => 'Samsung Galaxy S24',  'category' => 'smartphones',  'price' => 5999.99, 'stock' => 30],
    ['name' => 'MacBook Pro M3',      'category' => 'laptops',      'price' => 18999.99,'stock' => 15],
    ['name' => 'Dell XPS 15',         'category' => 'laptops',      'price' => 12999.99,'stock' => 20],
    ['name' => 'AirPods Pro 2',       'category' => 'accessories',  'price' => 2199.99, 'stock' => 100],
    ['name' => 'Logitech MX Master 3','category' => 'accessories',  'price' => 699.99,  'stock' => 80],
    ['name' => 'iPad Air M1',         'category' => 'tablets',      'price' => 5499.99, 'stock' => 40],
    ['name' => 'Kindle Paperwhite',   'category' => 'tablets',      'price' => 799.99,  'stock' => 60],
    ['name' => 'Sony WH-1000XM5',     'category' => 'headphones',   'price' => 2499.99, 'stock' => 45],
    ['name' => 'Monitor LG 27" 4K',   'category' => 'monitors',     'price' => 3999.99, 'stock' => 25],
];

foreach ($products as $p) {
    $product = Product::create(
        name:        $p['name'],
        description: "Produto: {$p['name']}. Categoria: {$p['category']}.",
        price:       Money::fromFloat($p['price']),
        stock:       $p['stock'],
        category:    $p['category'],
    );
    $productRepo->save($product);
    echo "  ✓ Product: {$p['name']}\n";
}

echo "\n✅ Seed complete!\n";
