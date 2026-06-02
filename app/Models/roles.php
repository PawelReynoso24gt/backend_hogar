<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class roles extends Model
{
    use HasFactory;

    protected $table = 'roles';
    protected $primaryKey = 'id_rol';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado'
    ];

    public function usuarios()
{
    return $this->hasMany(logins::class, 'id_rol', 'id_rol');
}
}
