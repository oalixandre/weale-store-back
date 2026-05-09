<?php

declare(strict_types=1);

namespace Weale\Bootstrap;

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Dotenv\Dotenv;
use Slim\App as SlimApp;

final class App
{
    public static function create(): SlimApp
    {
        // Load environment variables
        $dotenv = Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->safeLoad();

        // Build DI Container
        $containerBuilder = new ContainerBuilder();

        if ($_ENV['APP_ENV'] === 'production') {
            $containerBuilder->enableCompilation(dirname(__DIR__) . '/var/cache');
        }

        // Load container definitions
        $definitions = require_once dirname(__DIR__) . '/config/container.php';
        $containerBuilder->addDefinitions($definitions);

        $container = $containerBuilder->build();

        // Create Slim app
        AppFactory::setContainer($container);
        $app = AppFactory::create();

        // Register middleware
        (require_once dirname(__DIR__) . '/config/middleware.php')($app);

        // Register routes
        (require_once dirname(__DIR__) . '/config/routes.php')($app);

        return $app;
    }
}
