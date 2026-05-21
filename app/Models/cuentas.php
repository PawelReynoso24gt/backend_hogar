<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class cuentas extends Model
{
    use HasFactory;
    protected $table = 'cuentas';
    protected $primaryKey = 'id_cuentas';
    protected $fillable = ['cuenta', 'estado', 'codigo', 'id_clasificacion', 'id_proyectos', 'tipo_cuenta', 'corriente'];


    // Asignación por defecto
    protected $attributes = [
        'estado' => 1,
    ];

    public function clasificacion()
    {
        return $this->belongsTo(clasificacion::class, 'id_clasificacion');
    }

    public function proyecto()
    {
        return $this->belongsTo(proyectos::class, 'id_proyectos');
    }

}
