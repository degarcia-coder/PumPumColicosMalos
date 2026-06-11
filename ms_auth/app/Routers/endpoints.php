<?php

use App\Controllers\AuthController;
use Slim\App;

return function (App $app) {
    $app->post('/auth/ingreso', [AuthController::class, 'login']);
    $app->post('/auth/salida',   [AuthController::class, 'logout']);
    $app->get('/auth/validar',  [AuthController::class, 'validate']);
};