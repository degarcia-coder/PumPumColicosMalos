<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModelosUsuario extends Model
{
    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'correo',
        'usuario',
        'contrasena',
        'rol',
        'token',
        'sesion_activa',
        'estado',
    ];

    protected $hidden = [
        'contrasena',
        'token',
    ];
}
