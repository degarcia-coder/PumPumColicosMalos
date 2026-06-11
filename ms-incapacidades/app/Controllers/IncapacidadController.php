<?php

namespace App\Controllers;

use App\Models\Incapacidad;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class IncapacidadController
{
    // POST /incapacidades - Registrar incapacidad
    public function registrar(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();

        // Validar campos obligatorios
        $camposRequeridos = ['empleado_id', 'fecha_inicio', 'fecha_fin', 'tipo', 'diagnostico_general', 'entidad_medica'];
        foreach ($camposRequeridos as $campo) {
            if (empty($body[$campo])) {
                return $this->json($response, [
                    'status'  => 'error',
                    'mensaje' => "El campo $campo es obligatorio"
                ], 400);
            }
        }

        // Validar fechas
        $fechaInicio = \DateTime::createFromFormat('Y-m-d', $body['fecha_inicio']);
        $fechaFin    = \DateTime::createFromFormat('Y-m-d', $body['fecha_fin']);

        if (!$fechaInicio || !$fechaFin) {
            return $this->json($response, [
                'status'  => 'error',
                'mensaje' => 'Fechas inválidas, formato esperado: YYYY-MM-DD'
            ], 400);
        }

        // Validar que fecha fin no sea menor a fecha inicio
        if ($fechaFin < $fechaInicio) {
            return $this->json($response, [
                'status'  => 'error',
                'mensaje' => 'La fecha fin no puede ser menor a la fecha inicio'
            ], 400);
        }

        // Validar incapacidad duplicada para el mismo empleado y rango de fechas
        $duplicada = Incapacidad::where('empleado_id', $body['empleado_id'])
            ->where(function ($query) use ($body) {
                $query->whereBetween('fecha_inicio', [$body['fecha_inicio'], $body['fecha_fin']])
                      ->orWhereBetween('fecha_fin', [$body['fecha_inicio'], $body['fecha_fin']]);
            })->exists();

        if ($duplicada) {
            return $this->json($response, [
                'status'  => 'error',
                'mensaje' => 'Ya existe una incapacidad para ese empleado en ese rango de fechas'
            ], 409);
        }

        // Calcular días automáticamente
        $body['dias_incapacidad'] = $fechaInicio->diff($fechaFin)->days + 1;
        $body['estado']           = 'registrada';

        $incapacidad = Incapacidad::create($body);

        return $this->json($response, [
            'status'       => 'success',
            'mensaje'      => 'Incapacidad registrada correctamente',
            'incapacidad'  => $incapacidad
        ], 201);
    }

    public function listar(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $query  = Incapacidad::query();

        if (!empty($params['empleado_id'])) {
            $query->where('empleado_id', $params['empleado_id']);
        }

        if (!empty($params['fecha'])) {
            $query->where('fecha_inicio', '<=', $params['fecha'])
                  ->where('fecha_fin', '>=', $params['fecha']);
        }

        if (!empty($params['estado'])) {
            $query->where('estado', $params['estado']);
        }

        if (!empty($params['tipo'])) {
            $query->where('tipo', $params['tipo']);
        }

        $incapacidades = $query->get();

        return $this->json($response, [
            'status'        => 'success',
            'incapacidades' => $incapacidades
        ], 200);
    }

    // PUT /incapacidades/{id} - Editar incapacidad
    public function editar(Request $request, Response $response, array $args): Response
    {
        $incapacidad = Incapacidad::find($args['id']);

        if (!$incapacidad) {
            return $this->json($response, [
                'status'  => 'error',
                'mensaje' => 'Incapacidad no encontrada'
            ], 404);
        }

        $body = $request->getParsedBody();

        // Validar fechas si se están cambiando
        if (!empty($body['fecha_inicio']) || !empty($body['fecha_fin'])) {
            $fechaInicio = \DateTime::createFromFormat('Y-m-d', $body['fecha_inicio'] ?? $incapacidad->fecha_inicio);
            $fechaFin    = \DateTime::createFromFormat('Y-m-d', $body['fecha_fin'] ?? $incapacidad->fecha_fin);

            if (!$fechaInicio || !$fechaFin) {
                return $this->json($response, [
                    'status'  => 'error',
                    'mensaje' => 'Fechas inválidas, formato esperado: YYYY-MM-DD'
                ], 400);
            }

            if ($fechaFin < $fechaInicio) {
                return $this->json($response, [
                    'status'  => 'error',
                    'mensaje' => 'La fecha fin no puede ser menor a la fecha inicio'
                ], 400);
            }

            // Recalcular días automáticamente
            $body['dias_incapacidad'] = $fechaInicio->diff($fechaFin)->days + 1;
        }

        $incapacidad->update($body);

        return $this->json($response, [
            'status'      => 'success',
            'mensaje'     => 'Incapacidad actualizada correctamente',
            'incapacidad' => $incapacidad
        ], 200);
    }

    // PATCH /incapacidades/{id}/finalizar - Finalizar incapacidad
    public function finalizar(Request $request, Response $response, array $args): Response
    {
        $incapacidad = Incapacidad::find($args['id']);

        if (!$incapacidad) {
            return $this->json($response, [
                'status'  => 'error',
                'mensaje' => 'Incapacidad no encontrada'
            ], 404);
        }

        if ($incapacidad->estado === 'finalizada') {
            return $this->json($response, [
                'status'  => 'error',
                'mensaje' => 'La incapacidad ya está finalizada'
            ], 400);
        }

        $incapacidad->estado = 'finalizada';
        $incapacidad->save();

        return $this->json($response, [
            'status'      => 'success',
            'mensaje'     => 'Incapacidad finalizada correctamente',
            'incapacidad' => $incapacidad
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