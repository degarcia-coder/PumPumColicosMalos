<?php

namespace App\Handlers;

use App\Models\ModelosUsuario;

class AuthHandler
{
    // ─── Login ────────────────────────────────────────────────
    public function login(array $data): array
    {
        // 1. Validar que lleguen los datos
        if (empty($data['credencial']) || empty($data['contrasena'])) {
            return $this->respuesta(400, ['error' => 'Credenciales incompletas.']);
        }

        // 2. Buscar usuario por correo o usuario
        $usuario = ModelosUsuario::where('correo', $data['credencial'])
                                 ->orWhere('usuario', $data['credencial'])
                                 ->first();

        // 3. Verificar que existe y que la contraseña es correcta
        if (!$usuario || !password_verify($data['contrasena'], $usuario->contrasena)) {
            return $this->respuesta(401, ['error' => 'Credenciales incorrectas.']);
        }

        // 4. Verificar que el usuario esté activo
        if ($usuario->estado !== 'activo') {
            return $this->respuesta(403, ['error' => 'Usuario inactivo.']);
        }

        // 5. Generar token y actualizar sesión
        $token = bin2hex(random_bytes(32));

        $usuario->token          = hash('sha256', $token);
        $usuario->sesion_activa  = 1;
        $usuario->save();

        // 6. Retornar respuesta al frontend
        return $this->respuesta(200, [
            'mensaje' => 'Inicio de sesión exitoso.',
            'token'   => $token,
            'usuario' => [
                'nombre' => $usuario->nombre,
                'correo' => $usuario->correo,
                'rol'    => $usuario->rol,
            ]
        ]);
    }

    // ─── Logout ───────────────────────────────────────────────
    public function logout(string $token): array
    {
        // 1. Buscar usuario por token hasheado
        $usuario = ModelosUsuario::where('token', hash('sha256', $token))
                                 ->where('sesion_activa', 1)
                                 ->first();

        if (!$usuario) {
            return $this->respuesta(401, ['error' => 'Sesión no válida.']);
        }

        // 2. Invalidar token
        $usuario->token         = null;
        $usuario->sesion_activa = 0;
        $usuario->save();

        return $this->respuesta(200, ['mensaje' => 'Sesión cerrada correctamente.']);
    }

    // ─── Validar sesión ───────────────────────────────────────
    public function validarSesion(string $token): array
    {
        // 1. Buscar usuario con token activo
        $usuario = ModelosUsuario::where('token', hash('sha256', $token))
                                 ->where('sesion_activa', 1)
                                 ->first();

        if (!$usuario) {
            return $this->respuesta(401, ['error' => 'Token inválido o sesión expirada.']);
        }

        // 2. Retornar info básica del usuario
        return $this->respuesta(200, [
            'valido'  => true,
            'usuario' => [
                'nombre' => $usuario->nombre,
                'correo' => $usuario->correo,
                'rol'    => $usuario->rol,
            ]
        ]);
    }

    // ─── Helper respuesta ─────────────────────────────────────
    private function respuesta(int $status, array $data): array
    {
        return ['status' => $status, 'body' => $data];
    }
}
