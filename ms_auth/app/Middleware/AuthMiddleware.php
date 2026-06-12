<?php

namespace App\Middleware;

use App\Models\ModelosUsuario;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response as SlimResponse;

class AuthMiddleware implements MiddlewareInterface
{
    private array $rutasPublicas = [
        '/auth/ingreso'
    ];

    public function process(Request $request, Handler $handler): Response
    {
        if ($request->getMethod() === 'OPTIONS') {
            return new SlimResponse();
        }

        $ruta = $request->getUri()->getPath();
        if (in_array($ruta, $this->rutasPublicas)) {
            return $handler->handle($request);
        }

        $token = $request->getHeaderLine('Authorization');

        if (empty($token)) {
            return $this->unauthorized('Token no proporcionado');
        }

        $token = str_replace('Bearer ', '', $token);

        $usuario = ModelosUsuario::where('token', $token)
            ->where('sesion_activa', true)
            ->where('estado', 'activo')
            ->first();

        if (!$usuario) {
            return $this->unauthorized('Token inválido o sesión inactiva');
        }

        $request = $request->withAttribute('usuario', $usuario);
        return $handler->handle($request);
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