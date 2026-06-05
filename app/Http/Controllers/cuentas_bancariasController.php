<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\bancos;
use App\Models\cuentas_bancarias;

class cuentas_bancariasController extends Controller
{
    // Método get por número de cuenta con nombre del banco
    public function getByCuenta($numero_cuenta)
    {
        try {
            // Buscar la cuenta bancaria por su número de cuenta
            $cuenta = cuentas_bancarias::where('numero_cuenta', $numero_cuenta)->first();

            // Verificar si la cuenta existe
            if (!$cuenta) {
                return response()->json(['error' => 'La cuenta no existe'], 404);
            }

            // Cargar la relación de bancos
            $cuenta->load('bancos');

            // Construir el objeto de respuesta
            $responseData = [
                'id_cuentas_bancarias' => $cuenta->id_cuentas_bancarias,
                'numero_cuenta' => $cuenta->numero_cuenta,
                'estado' => $cuenta->estado,
                'banco' => $cuenta->bancos ? $cuenta->bancos->banco : null
                // Puedes agregar más atributos si lo deseas
            ];

            return response()->json($responseData, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // Método get
    public function get(){
        try{
            $data = cuentas_bancarias::all(); // all() es más directo que get() cuando no hay where
            return response()->json($data, 200);
        } catch (\Throwable $th){
            return response()->json(['error' => $th ->getMessage()],500);
        }
    }

    public function getByCuentaBName()
    {
        try {
            $cuenta_bancarias = cuentas_bancarias::with('bancos')->get();
    
            if($cuenta_bancarias->isEmpty()) {
                return response()->json(['error' => 'No hay cuentas bancarias disponibles'], 404);
            }
    
            // Cambiamos el foreach por map() para mantener el mismo estilo de código
            $responseData = $cuenta_bancarias->map(function ($cuentaB) {
                return [
                    'cuenta_bancaria' => $cuentaB->numero_cuenta, 
                    'banco_y_cuenta'  => $cuentaB->bancos ? $cuentaB->bancos->banco . ' ' . $cuentaB->numero_cuenta : null
                ];
            });
    
            return response()->json($responseData, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function create(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'numero_cuenta' => 'required|string',
            'banco'         => 'required|string',
            'estado'        => 'sometimes|integer' // Validación opcional para estado
        ]);

        try {
            $banco = bancos::where('banco', $request->input('banco'))->first();

            if (!$banco) {
                return response()->json(['error' => 'El banco proporcionado no existe'], 404);
            }

            // Crear un nuevo registro en la tabla cuentas
            $cuentaB = new cuentas_bancarias();
            $cuentaB->numero_cuenta = $request->input('numero_cuenta');
            $cuentaB->id_bancos = $banco->id_bancos;
            $cuentaB->estado = $request->input('estado', 1); // Establecer estado, por defecto 1
            $cuentaB->save();

            return response()->json($cuentaB, 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function update(Request $request, $cuentaB)
    {
        try {
            $cuenta = cuentas_bancarias::where('numero_cuenta', $cuentaB)->first();

            if (!$cuenta) {
                return response()->json(['error' => 'La cuenta no existe'], 404);
            }

            if ($request->has('numero_cuenta')) {
                $cuenta->numero_cuenta = $request->input('numero_cuenta');
            }

            if ($request->has('banco')) {
                $banco = bancos::where('banco', $request->input('banco'))->first();
                if (!$banco) {
                    return response()->json(['error' => 'El banco proporcionado no existe'], 404);
                }
                $cuenta->id_bancos = $banco->id_bancos;
            }

            if ($request->has('estado')) {
                $cuenta->estado = $request->input('estado');
            }

            $cuenta->save();

            $cuenta->load('bancos'); // Cargamos el banco por si cambió para devolver la data completa

            return response()->json($cuenta, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    //mostrar nombre y numero cuenta pero enviar id de cuenta bancaria
      public function getIdCuenta(): JsonResponse
    {
        $rows = cuentas_bancarias::with('bancos:id_bancos,banco')
            ->where('estado', 1)
            ->orderBy('id_cuentas_bancarias', 'asc')
            ->get(['id_cuentas_bancarias','numero_cuenta','id_bancos']);

        $result = $rows->map(fn($r) => [
            'id'    => (int) $r->id_cuentas_bancarias,
            'label' => ($r->bancos?->banco ?? 'Sin banco').' • '.$r->numero_cuenta,
        ]);

        return response()->json($result, 200);
    }
    
}
