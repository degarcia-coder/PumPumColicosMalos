<?php

use App\Controllers\IncapacidadController;
use Slim\App;

return function (App $app) {
    $app->get('/incapacidades',                      [IncapacidadController::class, 'listar']);
    $app->post('/incapacidades',                     [IncapacidadController::class, 'registrar']);
    $app->put('/incapacidades/{id}',                 [IncapacidadController::class, 'editar']);
    $app->patch('/incapacidades/{id}/finalizar',     [IncapacidadController::class, 'finalizar']);
};