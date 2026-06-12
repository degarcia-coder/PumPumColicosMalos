<?php

namespace App\Controllers;

use App\Models\Seguimiento;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SeguimientoController
{
    // POST /seguimientos - Registrar seguimiento
    public function registrar(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();

        // Validar campos obligatorios
        $camposRequeridos = ['incapacidad_id', 'fecha', 'comentario', 'estado', 'usuario_responsable'];
        foreach ($camposRequeridos as $campo) {
            if (empty($body[$campo])) {
                return $this->json($response, [
                    'status'  => 'error',
                    'mensaje' => "El campo $campo es obligatorio"
                ], 400);
            }
        }

        // Validar fecha
        $fecha = \DateTime::createFromFormat('Y-m-d', $body['fecha']);
        if (!$fecha) {
            return $this->json($response, [
                'status'  => 'error',
                'mensaje' => 'Fecha inválida, formato esperado: YYYY-MM-DD'
            ], 400);
        }

        $seguimiento = Seguimiento::create($body);

        return $this->json($response, [
            'status'      => 'success',
            'mensaje'     => 'Seguimiento registrado correctamente',
            'seguimiento' => $seguimiento
        ], 201);
    }

    // GET /seguimientos - Listar historial completo
    public function listar(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $query  = Seguimiento::query();

        if (!empty($params['incapacidad_id'])) {
            $query->where('incapacidad_id', $params['incapacidad_id']);
        }

        if (!empty($params['estado'])) {
            $query->where('estado', $params['estado']);
        }

        if (!empty($params['usuario_responsable'])) {
            $query->where('usuario_responsable', $params['usuario_responsable']);
        }

        $seguimientos = $query->orderBy('fecha', 'asc')->get();

        return $this->json($response, [
            'status'       => 'success',
            'seguimientos' => $seguimientos
        ], 200);
    }

    // PATCH /seguimientos/{id}/estado - Actualizar estado
    public function actualizarEstado(Request $request, Response $response, array $args): Response
    {
        $body = $request->getParsedBody();

        if (empty($body['estado']) || !in_array($body['estado'], [
            'registrada', 'en_revision', 'aprobada', 'rechazada', 'finalizada'
        ])) {
            return $this->json($response, [
                'status'  => 'error',
                'mensaje' => 'Estado inválido'
            ], 400);
        }

        if (empty($body['comentario']) || empty($body['usuario_responsable'])) {
            return $this->json($response, [
                'status'  => 'error',
                'mensaje' => 'Comentario y usuario responsable son obligatorios'
            ], 400);
        }

        // Registrar el cambio en seguimientos
        $seguimiento = Seguimiento::create([
            'incapacidad_id'      => $args['id'],
            'fecha'               => date('Y-m-d'),
            'comentario'          => $body['comentario'],
            'estado'              => $body['estado'],
            'usuario_responsable' => $body['usuario_responsable'],
        ]);

        return $this->json($response, [
            'status'      => 'success',
            'mensaje'     => 'Estado actualizado correctamente',
            'seguimiento' => $seguimiento
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