<?php

namespace App\Config;

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$capsule->addConnection([
        'driver'    => 'mysql',
            'host'      => $_ENV['DB_HOST'],
            'database'  => $_ENV['DB_DATABASE'],
            'username'  => $_ENV['DB_USERNAME'],
            'password'  => $_ENV['DB_PASSWORD'],
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();

try {
    $capsule->getConnection()->getPdo();
} catch (\PDOException $e) {
    http_response_code(503);
    header('Content-Type: application/json');
    echo json_encode([
        "error" => "No se puede conectar a la base de datos."
    ]);
    exit;
}