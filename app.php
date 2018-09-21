<?php
namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Denuncia extends Model {


    protected $connection = 'mongodenuncia';
    protected $collection = 'denuncia2018';

    protected $fillable = [
        'archivos', 'ubicacion'
    ];

    public $timestamps = true;

    protected $dates = [
        'created_at',
        'updated_at',
        'fecha'
    ];

}