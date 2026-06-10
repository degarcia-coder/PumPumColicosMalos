<?php

use Slim\Factory\AppFactory;
use MsCore\Middlewares\CorsMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

require __DIR__ . '/../app/Config/Database.php';

$routes = require __DIR__ . '/../app/auth/Routes/endpoints.php';

$app = AppFactory::create();

$app->options('/{routes:.+}', fn($req, $res) => $res);
$app->add(new CorsMiddleware());

$routes($app);

$app->run();