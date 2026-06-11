<?php

use App\Controllers\SeguimientoController;
use Slim\App;

return function (App $app) {
    $app->get('/seguimientos',                          [SeguimientoController::class, 'listar']);
    $app->post('/seguimientos',                         [SeguimientoController::class, 'registrar']);
    $app->patch('/seguimientos/{id}/estado',            [SeguimientoController::class, 'actualizarEstado']);
};