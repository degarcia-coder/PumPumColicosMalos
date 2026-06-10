<?php

use App\Config\Database;
use App\Middleware\CorsMiddleware;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Inicializa la base de datos
Database::init();

$app = AppFactory::create();

$app->add(new CorsMiddleware());

$routes = require __DIR__ . '/../app/Routers/endpoints.php';
$routes($app);

$app->run();