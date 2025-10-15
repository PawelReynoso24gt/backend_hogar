<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class logins extends Model
{
    use HasFactory;
    protected $table = 'logins';
    protected $primaryKey = 'id_login';
    protected $fillable = ['usuarios', 'contrasenias', 'estado'];
}
