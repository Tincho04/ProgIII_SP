<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model {

    protected $table = 'usuarios';
    public $timestamps = false;

    const CREATED_AT = 'creation_date';
    const UPDATED_AT = 'last_update';
}



