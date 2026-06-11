<?php

namespace App\Middleware;

use App\Models\Empleado;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response as SlimResponse;

class AuthMiddleware implements MiddlewareInterface
{
    // Este microservicio no tiene rutas públicas
    private array $rutasPublicas = [];

    public function process(Request $request, Handler $handler): Response
    {
        // 1. Maneja preflight OPTIONS
        if ($request->getMethod() === 'OPTIONS') {
            $response = new SlimResponse();
            return $this->addCorsHeaders($response);
        }

        // 2. Si es ruta pública, no valida token
        $ruta = $request->getUri()->getPath();
        if (in_array($ruta, $this->rutasPublicas)) {
            $response = $handler->handle($request);
            return $this->addCorsHeaders($response);
        }

        // 3. Valida token para rutas protegidas
        $token = $request->getHeaderLine('Authorization');

        if (empty($token)) {
            return $this->addCorsHeaders($this->unauthorized('Token no proporcionado'));
        }

        $token = str_replace('Bearer ', '', $token);

        // Valida el token contra la BD de autenticación
        // Nota: en producción esto debería consultar ms-auth
        if (empty($token)) {
            return $this->addCorsHeaders($this->unauthorized('Token inválido'));
        }

        $response = $handler->handle($request);
        return $this->addCorsHeaders($response);
    }

    private function addCorsHeaders(Response $response): Response
    {
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }

    private function unauthorized(string $mensaje): Response
    {
        $response = new SlimResponse();
        $response->getBody()->write(json_encode([
            'status'  => 'error',
            'mensaje' => $mensaje
        ]));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }
}