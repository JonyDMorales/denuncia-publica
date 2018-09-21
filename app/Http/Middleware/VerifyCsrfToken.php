<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'http://localhost:8000/denuncia/insertar',
        'http://localhost:8000/denuncia/filtro',
        'http://localhost:8000/denuncia/aprobadas',
        'http://localhost:8000/denuncia/obtener/status',
        'http://localhost:8000/denuncia/aprobar',
        'http://localhost:8000/denuncia/rechazar',
        'http://localhost:8000/denuncia/email'
    ];
}
