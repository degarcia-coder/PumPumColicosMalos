<?php
use Slim\Routing\RouteCollectorProxy;
use App\Handlers\authHandler;
use Slim\App;

return function (App $app) {
    $app->post('/auth/ingreso', [AuthHandler::class, 'ingreso']);
    $app->post('/auth/salida', [AuthHandler::class, 'salida']);
    $app->post('/auth/validar', [AuthHandler::class, 'validar']);
    $app->post('/auth/validar', [AuthHandler::class, 'validar']);
};