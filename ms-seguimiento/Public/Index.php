<?php

use App\Config\Database;
use App\Middleware\AuthMiddleware;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

Database::init();

$app = AppFactory::create();

$app->add(new AuthMiddleware());
$app->addBodyParsingMiddleware();

$routes = require __DIR__ . '/../app/Routers/endpoints.php';
$routes($app);

$app->run();