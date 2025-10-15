<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class datos_de_pago_egresos extends Model
{
    use HasFactory;
    protected $table = 'datos_de_pago_egresos';
    protected $primaryKey = 'id_datos_de_pago_egresos';
    protected $fillable = ['documento', 'numero_documento', 'fecha_emision', 'id_cuentas_bancarias', 'id_ingresos_egresos',];

    public function cuentas_bancarias()
    {
        return $this->belongsTo(cuentas_bancarias::class, 'id_cuentas_bancarias');
    }

    public function ingresos_egresos()
    {
        return $this->belongsTo(ingresos_egresos::class, 'id_ingresos_egresos');
    }
}
