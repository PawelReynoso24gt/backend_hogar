<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ingresos_egresos extends Model
{
    use HasFactory;
    protected $table = 'ingresos_egresos';
    protected $primaryKey = 'id_ingresos_egresos';
    protected $fillable = ['nomenclatura', 'fecha', 'identificación', 'nombre', 'descripcion', 'monto', 'monto_debe', 'monto_haber', 'es_pendiente', 'tipo', 'id_cuentas'];

    public function cuentas()
    {
        return $this->belongsTo(cuentas::class, 'id_cuentas');
    }

    public function datos_de_pago_ingresos()
    {
        return $this->hasMany(datos_de_pago_ingresos::class, 'id_ingresos_egresos');
    }

    public function datos_de_pago_egresos()
    {
        return $this->hasMany(datos_de_pago_egresos::class, 'id_ingresos_egresos');
    }
}