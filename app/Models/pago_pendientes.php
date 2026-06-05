<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pago_pendientes extends Model
{
    use HasFactory;
    protected $table = 'pago_pendientes';
    protected $primaryKey = 'id_pago_pendientes';
     protected $fillable = [
        'fecha_pago',
        'id_ingresos_egresos', 
        'id_abono',           
        'monto_pago',          
    ];

    public function deudaOriginal()
    {
        return $this->belongsTo(ingresos_egresos::class, 'id_ingresos_egresos', 'id_ingresos_egresos');
    }

    public function abono()
    {
        return $this->belongsTo(ingresos_egresos::class, 'id_abono', 'id_ingresos_egresos');
    }

}
