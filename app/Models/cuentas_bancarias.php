<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class cuentas_bancarias extends Model
{
    use HasFactory;
    protected $table = 'cuentas_bancarias';
    protected $primaryKey = 'id_cuentas_bancarias';
    protected $fillable = ['numero_cuenta', 'estado', 'id_bancos'];

    public function bancos()
    {
        return $this->belongsTo(bancos::class, 'id_bancos');
    }
}
