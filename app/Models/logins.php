<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class logins extends Authenticatable
{
    use HasFactory, HasApiTokens;
    protected $table = 'logins';
    protected $primaryKey = 'id_login';
    protected $fillable = ['usuarios', 'contrasenias', 'estado', 'id_rol'];

    public function rol()
    {
        return $this->belongsTo(roles::class, 'id_rol', 'id_rol');
    }
}
