<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\pago_pendientes;

class pagoPendientesController extends Controller
{
    public function get()
    {
        try {
            $data = pago_pendientes::all(); // 'all()' es más estándar que 'get()' cuando quieres traer todo
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

        // Como $validatedData ya tiene exactamente los 4 campos, lo pasamos directo.
        $pagoPendiente = pago_pendientes::create($validatedData);

        return response()->json([
            'message' => 'Pago pendiente registrado exitosamente',
            'data' => $pagoPendiente
        ], 201);
    }
}
