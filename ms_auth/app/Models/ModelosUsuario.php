<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModeloUsuario extends Model
{
    protected $table = 'usuarios';

    protected $hidden = [
        'contrasena',
        'token'
    ];
}