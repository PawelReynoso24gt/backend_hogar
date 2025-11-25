<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Importe los controlador necesarios para tener un mejor control de las funciones
use App\Http\Controllers\ingresos_egresosController;
use App\Http\Controllers\pagoPendientesController;

class saldarAnticipos extends Controller
{
    // Método que salda un anticipo: crea un registro en ingresos_egresos y registra el pago pendiente
    public function saldarAnticipo(Request $request)
    {
        // Validar los campos necesarios para crear el registro y para crear el pago pendiente
        $request->validate([
            // campos requeridos por ingresos_egresosController::create
            'fecha' => 'required|date',
            'identificacion' => 'required',
            'nombre' => 'required',
            'descripcion' => 'required',
            'monto' => 'required|numeric',
            'tipo' => 'required',
            'cuenta' => 'required',
            // campos requeridos por pagoPendientesController::addNewPendientes (excepto id_ingresos_egresos que se genera aquí)
            'fecha_pago' => 'required|date',
            'id_abono' => 'required|integer|exists:ingresos_egresos,id_ingresos_egresos',
            'monto_pago' => 'required|numeric|min:0',
        ]);

        try {
            $result = DB::transaction(function () use ($request) {
                // Llamar al método create del controlador ingresos_egresosController
                $ieController = new ingresos_egresosController();

                // incluir 'monto_pago' para que createSaldarAG lo use como monto_haber
                $createPayload = new Request($request->only([
                    'fecha', 'identificacion', 'nombre', 'descripcion', 'monto', 'tipo', 'cuenta', 'monto_pago'
                ]));

                $createResponse = $ieController->createSaldarAG($createPayload);

                // Extraer el id del ingreso/egreso creado
                $createdData = null;
                if (method_exists($createResponse, 'getData')) {
                    $createdData = $createResponse->getData(true);
                } else {
                    $createdData = $createResponse;
                }

                // soporte si la respuesta viene envuelta (por ejemplo ['ingreso_egreso' => {...}])
                $createdId = null;
                if (is_array($createdData) && isset($createdData['id_ingresos_egresos'])) {
                    $createdId = $createdData['id_ingresos_egresos'];
                } elseif (is_array($createdData) && isset($createdData[0]['id_ingresos_egresos'])) {
                    $createdId = $createdData[0]['id_ingresos_egresos'];
                } elseif (is_object($createdData) && isset($createdData->id_ingresos_egresos)) {
                    $createdId = $createdData->id_ingresos_egresos;
                } elseif (is_array($createdData) && isset($createdData['ingreso_egreso']['id_ingresos_egresos'])) {
                    $createdId = $createdData['ingreso_egreso']['id_ingresos_egresos'];
                }

                if (!$createdId) {
                    // intentar buscar el id dentro del modelo si fue devuelto como objeto Eloquent
                    if (is_object($createdData) && property_exists($createdData, 'id_ingresos_egresos')) {
                        $createdId = $createdData->id_ingresos_egresos;
                    }
                }

                if (!$createdId) {
                    throw new \RuntimeException('No se pudo obtener el id del ingreso/egreso creado.');
                }

                // Llamar al método addNewPendientes del controlador pagoPendientesController
                $ppController = new pagoPendientesController();

                $pagoPayload = new Request([
                    'fecha_pago' => $request->input('fecha_pago'),
                    'id_ingresos_egresos' => $createdId,
                    'id_abono' => $request->input('id_abono'),
                    'monto_pago' => $request->input('monto_pago'),
                ]);

                $pagoResponse = $ppController->addNewPendientes($pagoPayload);

                $pagoData = null;
                if (method_exists($pagoResponse, 'getData')) {
                    $pagoData = $pagoResponse->getData(true);
                } else {
                    $pagoData = $pagoResponse;
                }

                return [
                    'ingreso_egreso' => $createdData,
                    'pago_pendiente' => $pagoData,
                ];
            });

            return response()->json($result, 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
}
