<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Inscripcion extends Model {

    protected $table = 'inscripciones';
    protected $primaryKey = 'id';
    public $timestamps = false;

    const CREATED_AT = 'creation_date';
    const UPDATED_AT = 'last_update';
}