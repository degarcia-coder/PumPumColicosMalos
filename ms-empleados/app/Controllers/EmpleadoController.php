<?php

namespace App\Controllers;

use App\Models\Empleado;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class EmpleadoController
{
    // POST /empleados - Crear empleado
    public function crear(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();

        // Validar campos obligatorios
        $camposRequeridos = ['nombres', 'apellidos', 'documento', 'correo', 'telefono', 'cargo', 'area', 'fecha_ingreso'];
        foreach ($camposRequeridos as $campo) {
            if (empty($body[$campo])) {
                return $this->json($response, [
                    'status'  => 'error',
                    'mensaje' => "El campo $campo es obligatorio"
                ], 400);
            }
        }

        // Validar fecha
        $fecha = \DateTime::createFromFormat('Y-m-d', $body['fecha_ingreso']);
        if (!$fecha) {
            return $this->json($response, [
                'status'  => 'error',
                'mensaje' => 'Fecha de ingreso inválida, formato esperado: YYYY-MM-DD'
            ], 400);
        }

        // Validar documento duplicado
        if (Empleado::where('documento', $body['documento'])->exists()) {
            return $this->json($response, [
                'status'  => 'error',
                'mensaje' => 'El documento ya está registrado'
            ], 409);
        }

        // Validar correo duplicado
        if (Empleado::where('correo', $body['correo'])->exists()) {
            return $this->json($response, [
                'status'  => 'error',
                'mensaje' => 'El correo ya está registrado'
            ], 409);
        }

        $empleado = Empleado::create($body);

        return $this->json($response, [
            'status'   => 'success',
            'mensaje'  => 'Empleado creado correctamente',
            'empleado' => $empleado
        ], 201);
    }

    // PUT /empleados/{id} - Editar empleado
    public function editar(Request $request, Response $response, array $args): Response
    {
        $empleado = Empleado::find($args['id']);

        if (!$empleado) {
            return $this->json($response, [
                'status'  => 'error',
                'mensaje' => 'Empleado no encontrado'
            ], 404);
        }

        $body = $request->getParsedBody();

        // Validar documento duplicado si se está cambiando
        if (!empty($body['documento']) && $body['documento'] !== $empleado->documento) {
            if (Empleado::where('documento', $body['documento'])->exists()) {
                return $this->json($response, [
                    'status'  => 'error',
                    'mensaje' => 'El documento ya está registrado'
                ], 409);
            }
        }

        // Validar correo duplicado si se está cambiando
        if (!empty($body['correo']) && $body['correo'] !== $empleado->correo) {
            if (Empleado::where('correo', $body['correo'])->exists()) {
                return $this->json($response, [
                    'status'  => 'error',
                    'mensaje' => 'El correo ya está registrado'
                ], 409);
            }
        }

        $empleado->update($body);

        return $this->json($response, [
            'status'   => 'success',
            'mensaje'  => 'Empleado actualizado correctamente',
            'empleado' => $empleado
        ], 200);
    }

    // GET /empleados - Listar empleados con filtros
    public function listar(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();

        $query = Empleado::query();

        if (!empty($params['documento'])) {
            $query->where('documento', $params['documento']);
        }

        if (!empty($params['area'])) {
            $query->where('area', $params['area']);
        }

        if (!empty($params['estado'])) {
            $query->where('estado', $params['estado']);
        }

        $empleados = $query->get();

        return $this->json($response, [
            'status'    => 'success',
            'empleados' => $empleados
        ], 200);
    }

    // PATCH /empleados/{id}/estado - Cambiar estado
    public function cambiarEstado(Request $request, Response $response, array $args): Response
    {
        $empleado = Empleado::find($args['id']);

        if (!$empleado) {
            return $this->json($response, [
                'status'  => 'error',
                'mensaje' => 'Empleado no encontrado'
            ], 404);
        }

        $body = $request->getParsedBody();

        if (empty($body['estado']) || !in_array($body['estado'], ['activo', 'inactivo'])) {
            return $this->json($response, [
                'status'  => 'error',
                'mensaje' => 'Estado inválido, debe ser activo o inactivo'
            ], 400);
        }

        $empleado->estado = $body['estado'];
        $empleado->save();

        return $this->json($response, [
            'status'  => 'success',
            'mensaje' => 'Estado actualizado correctamente',
            'empleado' => $empleado
        ], 200);
    }

    // Helper para retornar JSON
    private function json(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}