<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\pago_pendientes;


class pagoPendientesController extends Controller
{
    public function get()
    {
        try{
            $data = pago_pendientes::get();
            return response()->json($data, 200);
        }catch(\Exception $e){
            return response() ->json(['error' => 'Error al obtener los pagos pendientes', 'message' => $e->getMessage()], 500);  
        }
    }
    //Crear un nuevo registro de pago pendiente
    public function addNewPendientes(Request $request)
    {
        $validatedData = $request->validate([
            'fecha_pago' => 'required|date',
            'id_ingresos_egresos' => 'required|integer|exists:ingresos_egresos,id_ingresos_egresos',
            'id_abono' => 'required|integer|exists:ingresos_egresos,id_ingresos_egresos',
            'monto_pago' => 'required|numeric|min:0',
        ]);

        $pagoPendiente = pago_pendientes::create([
            'fecha_pago' => $validatedData['fecha_pago'],
            'id_ingresos_egresos' => $validatedData['id_ingresos_egresos'],
            'id_abono' => $validatedData['id_abono'],
            'monto_pago' => $validatedData['monto_pago'],
        ]);

        return response()->json([
            'message' => 'Pago pendiente registrado exitosamente',
            'data' => $pagoPendiente
        ], 201);
    }
}
