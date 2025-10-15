<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class clasificacion extends Model
{
    use HasFactory;
    protected $table = 'clasificacion';
    protected $primaryKey = 'id_clasificacion';
    protected $fillable = ['tipo'];
}
