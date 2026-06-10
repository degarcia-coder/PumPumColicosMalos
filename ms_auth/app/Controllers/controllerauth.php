<?php

namespace App\Controllers;

use App\Models\Usuario;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class controllerauth
{
    // POST/login
    public function login(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();

        $identificador = $body['usuario'] ?? $body['correo'] ?? null;
        $contrasena    = $body['contrasena'] ?? null;

        if (!$identificador || !$contrasena) {
            return $this->json($response, [
                'status'  => 'error',
                'mensaje' => 'Usuario/correo y contraseña son obligatorios'
            ], 400);
        }

        // Busca por usuario o por correo
        $usuario = Usuario::where(function ($query) use ($identificador) {
            $query->where('usuario', $identificador)
                  ->orWhere('correo', $identificador);
        })->where('estado', 'activo')->first();

        if (!$usuario || $usuario->contrasena !== $contrasena) {
            return $this->json($response, [
                'status'  => 'error',
                'mensaje' => 'Credenciales incorrectas'
            ], 401);
        }

        // Genera token simple único
        $token = bin2hex(random_bytes(32));

        $usuario->token         = $token;
        $usuario->sesion_activa = true;
        $usuario->save();

        return $this->json($response, [
            'status'  => 'success',
            'mensaje' => 'Inicio de sesión exitoso',
            'token'   => $token,
            'usuario' => [
                'id'     => $usuario->id,
                'nombre' => $usuario->nombre,
                'correo' => $usuario->correo,
                'rol'    => $usuario->rol,
            ]
        ], 200);
    }

    // POST/logout
    public function logout(Request $request, Response $response): Response
    {
        $usuario = $request->getAttribute('usuario');

        $usuario->token         = null;
        $usuario->sesion_activa = false;
        $usuario->save();

        return $this->json($response, [
            'status'  => 'success',
            'mensaje' => 'Sesión cerrada correctamente'
        ], 200);
    }

    // GET /validate
    public function validate(Request $request, Response $response): Response
    {
        $usuario = $request->getAttribute('usuario');

        return $this->json($response, [
            'status'  => 'success',
            'usuario' => [
                'id'     => $usuario->id,
                'nombre' => $usuario->nombre,
                'correo' => $usuario->correo,
                'rol'    => $usuario->rol,
            ]
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
