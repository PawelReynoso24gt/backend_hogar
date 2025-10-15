<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\cuentas_bancarias;
use App\Models\bancos;

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
                'estado' => $data['estado'] = 1,
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
            $data = cuentas_bancarias::get();
            return response()->json($data, 200);
        } catch (\Throwable $th){
            return response()->json(['error' => $th ->getMessage()],500);
        }
    }

    // método get con todos los nombres de bancos
    public function getWithBancos () {
        try{
            $data = cuentas_bancarias::with('bancos')->get()->map(function ($cuentas_bancarias) {
                return [
                    'id_cuentas_bancarias' => $cuentas_bancarias->id_cuentas_bancarias,
                    'numero_cuenta' => $cuentas_bancarias->numero_cuenta,
                    'estado' => $cuentas_bancarias->estado,
                    'banco' => $cuentas_bancarias->bancos->banco
                ];
            });
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // Método get por número de cuenta
    public function getByCuentaB($cuentaB)
    {
        try {
            $cuentaB = cuentas_bancarias::where('numero_cuenta', $cuentaB)->first();

            if(!$cuentaB) {
                return response()->json(['error' => 'La cuenta no existe'], 404);
            }

            $cuentaB->load('bancos');

            $responseData = [
                'id_cuentas_bancarias' => $cuentaB->id_cuentas_bancarias,
                'numero_cuenta' => $cuentaB->numero_cuenta,
                'estado' => $cuentaB->estado,
                'banco' => $cuentaB->bancos ? $cuentaB->bancos->banco : null
            ];

            return response()->json($responseData, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function getByCuentaBName()
    {
        try {
            $cuenta_bancarias = cuentas_bancarias::with('bancos')->get();
    
            if($cuenta_bancarias->isEmpty()) {
                return response()->json(['error' => 'No hay cuentas bancarias disponibles'], 404);
            }
    
            $responseData = [];
    
            foreach ($cuenta_bancarias as $cuentaB) {
                $responseData[] = [
                    'cuenta_bancaria' => $cuentaB->numero_cuenta, 
                    'banco_y_cuenta' => $cuentaB->bancos ? $cuentaB->bancos->banco . ' ' . $cuentaB->numero_cuenta : null
                ];
            }
    
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
            'banco' => 'required|string',
            'estado' => 'sometimes|integer' // Validación opcional para estado
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
            // Buscar la cuenta por su numero
            $cuentaB = cuentas_bancarias::where('numero_cuenta', $cuentaB)->first();

            // Verificar si la cuenta existe
            if (!$cuentaB) {
                return response()->json(['error' => 'La cuenta no existe'], 404);
            }

            // Actualizar solo los campos que se hayan enviado en la solicitud
            if ($request->has('numero_cuenta')) {
                $cuentaB->numero_cuenta = $request->input('numero_cuenta');
            }

            if ($request->has('banco')) {
                // Buscar el banco
                $banco = bancos::where('banco', $request->input('banco'))->first();

                // Si no se encuentra, devolver un error
                if (!$banco) {
                    return response()->json(['error' => 'El banco proporcionado no existe'], 404);
                }

                $cuentaB->id_bancos = $banco->id_bancos;
            }

            if ($request->has('estado')) {
                $cuentaB->estado = $request->input('estado');
            }

            // Guardar los cambios
            $cuentaB->save();

            // Obtener la cuenta actualizada
            $updatedCuenta = cuentas_bancarias::find($cuentaB->id_cuentas_bancarias);

            return response()->json($updatedCuenta, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
    

    // Método get por cuenta para Id
    public function getByCuentaId($numero_cuenta)
    {
        try {
            // Comprobar los datos recibidos
            if (!$numero_cuenta) {
                return response()->json(['error' => 'The numero cuenta field is required.'], 400);
            }

            // Buscar la cuenta bancaria por su número de cuenta
            $cuentaB = cuentas_bancarias::where('numero_cuenta', $numero_cuenta)->first();

            // Verificar si la cuenta existe
            if (!$cuentaB) {
                return response()->json(['error' => 'La cuenta no existe'], 404);
            }

            // Preparar la respuesta
            $responseData = [
                'id_cuentas_bancarias' => $cuentaB->id_cuentas_bancarias,
                'numero_cuenta' => $cuentaB->numero_cuenta,
                'estado' => $cuentaB->estado,
                'id_bancos' => $cuentaB->id_bancos
            ];

            return response()->json($responseData, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
    
}
