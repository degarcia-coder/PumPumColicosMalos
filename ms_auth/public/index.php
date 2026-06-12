<?php

use App\Config\Database;
use App\Middleware\AuthMiddleware;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

// CORS nativo - va primero que todo
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

Database::init();

$app = AppFactory::create();

$app->add(new AuthMiddleware());
$app->addBodyParsingMiddleware();

$routes = require __DIR__ . '/../app/Routers/endpoints.php';
$routes($app);

$app->run();