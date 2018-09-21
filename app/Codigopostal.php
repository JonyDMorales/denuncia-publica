<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Codigopostal extends Model {

    protected $connection = 'mongodenuncia';
    protected $collection = 'codigospostales';

    protected $fillable = [
        'fecha', 'clave'
    ];

    public $timestamps = true;

}