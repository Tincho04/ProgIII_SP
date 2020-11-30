<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profesor extends Model {

    protected $table = 'profesores';
    protected $primaryKey = 'id';
    public $timestamps = false;

    const CREATED_AT = 'creation_date';
    const UPDATED_AT = 'last_update';
}