<?php

use App\Controllers\EmpleadoController;
use Slim\App;

return function (App $app) {
    $app->get('/empleados', [EmpleadoController::class, 'listar']);

    $app->post('/empleados', [EmpleadoController::class, 'crear']);

    $app->put('/empleados/{id}', [EmpleadoController::class, 'editar']);

    $app->patch('/empleados/{id}/estado', [EmpleadoController::class, 'cambiarEstado']);
};