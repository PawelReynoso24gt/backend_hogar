<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\proyectos;
use App\Models\clasificacion;
use App\Models\cuentas;
use Illuminate\Support\Facades\DB;
use App\Models\ingresos_egresos;


class cuentasController extends Controller
{
    //Metodo get
    public function get(){
        try{
            $data = cuentas::get();
            return response()->json($data, 200);
        } catch (\Throwable $th){
            return response()->json(['error' => $th ->getMessage()],500);
        }
    }

        // Método para obtener el nombre de clasificación por su ID
    public function getNombreClasificacionById($id)
    {
        try {
            $clasificacion = clasificacion::find($id);
            if (!$clasificacion) {
                return response()->json(['error' => 'La clasificación no existe'], 404);
            }
            $nombre = $clasificacion->nombre; // Obtener el nombre de la clasificación
            return response()->json(['nombre' => $nombre], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }



       //Metodo get by id
    public function getById($id){
        try {
            $data = cuentas::find($id);
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json([ 'error' => $th->getMessage()], 500);
        }
    }
    
    // Método get de cuentas con nombres de clasificación y proyectos
    public function getWithNombres()
    {
        try {
            $data = cuentas::with(['clasificacion' => function ($query) {
                $query->select('id_clasificacion', 'tipo as clasificacion');
            }, 'proyecto' => function ($query) {
                $query->select('id_proyectos', 'nombre as proyecto');
            }])->get();

            // Mapear los resultados para formatear la respuesta
            $formattedData = $data->map(function ($cuenta) {
                return [
                    'id_cuentas' => $cuenta->id_cuentas,
                    'cuenta' => $cuenta->cuenta,
                    'estado' => $cuenta->estado,
                    'codigo' => $cuenta->codigo,
                    'id_clasificacion' => $cuenta->id_clasificacion,
                    'id_proyectos' => $cuenta->id_proyectos,
                    'tipo_cuenta' => $cuenta->tipo_cuenta,
                    'corriente' => $cuenta->corriente,
                    // 'created_at' => $cuenta->created_at,
                    // 'updated_at' => $cuenta->updated_at,
                    'clasificacion' => $cuenta->clasificacion->clasificacion,
                    'proyecto' => $cuenta->proyecto->proyecto
                ];
            });

            return response()->json($formattedData, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }



    //metodo buscar cuentas crud cuentas
    // Método get de cuentas con nombres de proyectos
        // Método get de cuentas con nombres de proyectos
        public function GetCuentasCRUD($nombre)
        {
            try {
                $data = cuentas::with(['clasificacion' => function ($query) {
                        $query->select('id_clasificacion', 'tipo as clasificacion');
                    }, 'proyecto' => function ($query) {
                        $query->select('id_proyectos', 'nombre as proyecto');
                    }])->where('cuenta', $nombre)->get();
        
                // Mapear los resultados para formatear la respuesta
                $formattedData = $data->map(function ($cuenta) {
                    return [
                        'id_cuentas' => $cuenta->id_cuentas,
                        'cuenta' => $cuenta->cuenta,
                        'estado' => $cuenta->estado,
                        'codigo' => $cuenta->codigo,
                        'id_proyectos' => $cuenta->id_proyectos,
                        'id_clasificacion' => $cuenta->id_clasificacion,
                        'tipo_cuenta' => $cuenta->tipo_cuenta,
                        'corriente' => $cuenta->corriente,
                        // 'created_at' => $cuenta->created_at,
                        // 'updated_at' => $cuenta->updated_at,
                        'clasificacion' => $cuenta->clasificacion ? $cuenta->clasificacion->clasificacion : null,
                        'proyecto' => $cuenta->proyecto ? $cuenta->proyecto->proyecto : null
                    ];
                });
        
                return response()->json($formattedData, 200);
            } catch (\Throwable $th) {
                return response()->json(['error' => $th->getMessage()], 500);
            }
        }
        
    // Métdo Create con nombres
    public function create(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'cuenta' => 'required|string',
            //estado' => 'required|boolean',
            'codigo' => 'required|string',
            'clasificacion' => 'required|string',
            'proyecto' => 'required|string',
            'tipo_cuenta' => 'required|integer',
            'corriente' => 'required|integer'
        ]);

        try {
            // Buscar la clasificación por su tipo
            $clasificacion = clasificacion::where('tipo', $request->input('clasificacion'))->first();

            // Si la clasificación no se encuentra, devolver un error
            if (!$clasificacion) {
                return response()->json(['error' => 'La clasificación proporcionada no existe'], 404);
            }

            // Buscar el proyecto por su nombre
            $proyecto = proyectos::where('nombre', $request->input('proyecto'))->first();

            // Si el proyecto no se encuentra, devolver un error
            if (!$proyecto) {
                return response()->json(['error' => 'El proyecto proporcionado no existe'], 404);
            }

            // Crear un nuevo registro en la tabla cuentas
            $cuenta = new cuentas();
            $cuenta->cuenta = $request->input('cuenta');
            //$cuenta->estado = $request->input('estado');
            $cuenta->codigo = $request->input('codigo');
            $cuenta->id_clasificacion = $clasificacion->id_clasificacion;
            $cuenta->id_proyectos = $proyecto->id_proyectos;
            $cuenta->tipo_cuenta = $request->input('tipo_cuenta');
            $cuenta->corriente = $request->input('corriente');
            $cuenta->save();

            return response()->json($cuenta, 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // Get by name with classification type and project name
    public function getByNombre($cuenta)
    {
        try {
            $cuenta = cuentas::where('cuenta', $cuenta)->first();

            if (!$cuenta) {
                return response()->json(['error' => 'La cuenta no existe'], 404);
            }

            // Cargar la relación de clasificación
            $cuenta->load('clasificacion');
            // Cargar la relación de proyecto
            $cuenta->load('proyecto');

            // Construir el objeto de respuesta
            $responseData = [
                'id_cuentas' => $cuenta->id_cuentas,
                'cuenta' => $cuenta->cuenta,
                'estado' => $cuenta->estado = 1,
                'codigo' => $cuenta->codigo,
                'clasificacion' => $cuenta->clasificacion ? $cuenta->clasificacion->tipo : null,
                'proyecto' => $cuenta->proyecto ? $cuenta->proyecto->nombre : null,
                'tipo_cuenta' => $cuenta->tipo_cuenta,
                'corriente' => $cuenta->corriente,
                // Puedes agregar más atributos si lo deseas
            ];

            return response()->json($responseData, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // Método update por nombre de cuenta
    public function update(Request $request, $cuenta)
    {
        try {
            // Buscar la cuenta por su nombre
            $cuenta = cuentas::where('cuenta', $cuenta)->first();

            // Verificar si la cuenta existe
            if (!$cuenta) {
                return response()->json(['error' => 'La cuenta no existe'], 404);
            }

            // Actualizar solo los campos que se hayan enviado en la solicitud
            if ($request->has('cuenta')) {
                $cuenta->cuenta = $request->input('cuenta');
            }

            if ($request->has('codigo')) {
                $cuenta->codigo = $request->input('codigo');
            }

            if ($request->has('estado')) {
                $cuenta->estado = $request->input('estado');
            }

            if ($request->has('clasificacion')) {
                // Buscar la clasificación por su tipo
                $clasificacion = clasificacion::where('tipo', $request->input('clasificacion'))->first();

                // Si la clasificación no se encuentra, devolver un error
                if (!$clasificacion) {
                    return response()->json(['error' => 'La clasificación proporcionada no existe'], 404);
                }

                $cuenta->id_clasificacion = $clasificacion->id_clasificacion;
            }

            if ($request->has('proyecto')) {
                // Buscar el proyecto por su nombre
                $proyecto = proyectos::where('nombre', $request->input('proyecto'))->first();

                // Si el proyecto no se encuentra, devolver un error
                if (!$proyecto) {
                    return response()->json(['error' => 'El proyecto proporcionado no existe'], 404);
                }

                $cuenta->id_proyectos = $proyecto->id_proyectos;
            }

            if ($request->has('tipo_cuenta')) {
                $cuenta->tipo_cuenta = $request->input('tipo_cuenta');
            }

            if ($request->has('corriente')) {
                $cuenta->corriente = $request->input('corriente');
            }

            // Guardar los cambios
            $cuenta->save();

            // Obtener la cuenta actualizada
            $updatedCuenta = Cuentas::find($cuenta->id_cuentas);

            return response()->json($updatedCuenta, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    //  //Metodo Create
    //  public function create(Request $request)
    //  {
    //      // Validar los datos de entrada
    //      $validator = Validator::make($request->all(), [
    //          'cuenta' => 'required|unique:cuentas',
    //          'codigo' => 'required',
    //          'id_clasificacion' => 'required|exists:clasificacion,id_clasificacion',
    //          'id_proyectos' => 'required|exists:proyectos,id_proyectos',
    //      ]);
     
    //      if ($validator->fails()) {
    //          return response()->json(['error' => $validator->errors()->first()], 400);
    //      }
     
    //      try {
    //          // Crear una nueva cuenta
    //          $cuenta = cuentas::create([
    //              'cuenta' => $request->input('cuenta'),
    //              'codigo' => $request->input('codigo'),
    //              'id_clasificacion' => $request->input('id_clasificacion'),
    //              'id_proyectos' => $request->input('id_proyectos'),
    //          ]);
     
    //          return response()->json($cuenta, 201);
    //      } catch (\Throwable $th) {
    //          return response()->json(['error' => $th->getMessage()], 500);
    //      }
    //  }

    //  // Metodo Update
    //  public function update(Request $request, $id)
    //  {
    //      // Validar los datos de entrada
    //      $validator = Validator::make($request->all(), [
    //          'cuenta' => 'required',
    //          'estado' => 'required',
    //          'codigo' => 'required',
    //          'id_clasificacion' => 'required|exists:clasificacion,id_clasificacion',
    //          'id_proyectos' => 'required|exists:proyectos,id_proyectos',
    //      ]);
     
    //      if ($validator->fails()) {
    //          return response()->json(['error' => $validator->errors()->first()], 400);
    //      }
     
    //      try {
    //          // Buscar la cuenta a actualizar
    //          $cuenta = cuentas::find($id);
    //          if (!$cuenta) {
    //              return response()->json(['error' => 'Cuenta no encontrada'], 404);
    //          }
     
    //          // Actualizar los datos de la cuenta
    //          $cuenta->update([
    //              'cuenta' => $request->input('cuenta'),
    //              'estado' => $request->input('estado'),
    //              'codigo' => $request->input('codigo'),
    //              'id_clasificacion' => $request->input('id_clasificacion'),
    //              'id_proyectos' => $request->input('id_proyectos'),
    //          ]);
     
    //          // Obtener y devolver la cuenta actualizada
    //          $res = cuentas::find($id);
    //          return response()->json($res, 200);
    //      } catch (\Throwable $th) {
    //          return response()->json(['error' => 'Error al actualizar la cuenta'], 500);
    //      }
    //  }

    //      // Método Delete
    // public function delete($id) {
    //     try {
    //         $cuenta = cuentas::find($id);
    //         if (!$cuenta) {
    //             return response()->json(['error' => 'Cuenta no encontrado'], 404);
    //         }

    //         $cuenta->delete();
    //         return response()->json(['message' => 'Cuenta eliminada correctamente'], 200);
    //     } catch (\Throwable $th) {
    //         return response()->json(['error' => 'Error al eliminar la cuenta'], 500);
    //     }
    // }

    // // Método Update por cuenta
    // public function updateByCuenta(Request $request, $cuenta)
    // {
    //     try {
    //         // Normalizar el nombre de la cuenta (convertir a minúsculas y eliminar espacios)
    //         $cuenta = strtolower(str_replace(' ', '', $cuenta));
    
    //         // Actualizar la cuenta en la base de datos
    //         $affectedRows = cuentas::whereRaw('LOWER(REPLACE(cuenta, " ", "")) = ?', [$cuenta])
    //             ->update($request->except('cuenta')); // Excluir el campo 'cuenta' de los datos a actualizar
    
    //         // Verificar si se actualizó alguna fila
    //         if ($affectedRows === 0) {
    //             return response()->json(['error' => 'La cuenta no existe'], 404);
    //         }
    
    //         // Obtener la cuenta actualizada
    //         $updatedCuenta = cuentas::whereRaw('LOWER(REPLACE(cuenta, " ", "")) = ?', [$cuenta])->first();
    
    //         return response()->json($updatedCuenta, 200);
    //     } catch (\Throwable $th) {
    //         return response()->json(['error' => $th->getMessage()], 500);
    //     }
    // }

    // Método get de cuentas con nombres de clasificación y proyectos, filtrando por clasificación "INGRESOS"
    public function getCuentasIngresos()
    {
        try {
            $data = cuentas::whereHas('clasificacion', function ($query) {
                $query->where('tipo', 'INGRESOS');
            })->with(['clasificacion' => function ($query) {
                $query->select('id_clasificacion', 'tipo as clasificacion');
            }, 'proyecto' => function ($query) {
                $query->select('id_proyectos', 'nombre as proyecto');
            }])->get();

            // Mapear los resultados para formatear la respuesta
            $formattedData = $data->map(function ($cuenta) {
                return [
                    'id_cuentas' => $cuenta->id_cuentas,
                    'cuenta' => $cuenta->cuenta,
                    'estado' => $cuenta->estado,
                    'codigo' => $cuenta->codigo,
                    'id_clasificacion' => $cuenta->id_clasificacion,
                    'id_proyectos' => $cuenta->id_proyectos,
                    'clasificacion' => $cuenta->clasificacion->clasificacion,
                    'proyecto' => $cuenta->proyecto->proyecto,
                    'tipo_cuenta' => $cuenta->tipo_cuenta,
                    'corriente' => $cuenta->corriente,
                ];
            });

            return response()->json($formattedData, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function getCuentasEgresos()
    {
        try {
            $data = cuentas::whereHas('clasificacion', function ($query) {
                $query->where('tipo', 'EGRESOS');
            })->with(['clasificacion' => function ($query) {
                $query->select('id_clasificacion', 'tipo as clasificacion');
            }, 'proyecto' => function ($query) {
                $query->select('id_proyectos', 'nombre as proyecto');
            }])->get();

            // Mapear los resultados para formatear la respuesta
            $formattedData = $data->map(function ($cuenta) {
                return [
                    'id_cuentas' => $cuenta->id_cuentas,
                    'cuenta' => $cuenta->cuenta
                    // 'estado' => $cuenta->estado,
                    // 'codigo' => $cuenta->codigo,
                    // 'id_clasificacion' => $cuenta->id_clasificacion,
                    // 'id_proyectos' => $cuenta->id_proyectos,
                    // 'clasificacion' => $cuenta->clasificacion->clasificacion,
                    // 'proyecto' => $cuenta->proyecto->proyecto
                ];
            });

            return response()->json($formattedData, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

     public function getMovimientosPorCuenta(Request $request)
{
    try {
       
        $request->validate([
            'cuenta' => 'required|string',
            'year'   => 'nullable|integer',
        ]);

        $nombreCuenta = $request->input('cuenta');          
        $year         = $request->input('year', date('Y')); 
        $cuenta = cuentas::with('clasificacion')
            ->where('cuenta', $nombreCuenta)
            ->first();

        if (!$cuenta) {
            return response()->json([
                'error' => "No existe una cuenta con nombre: {$nombreCuenta}"
            ], 404);
        }

        $esIngreso = $cuenta->clasificacion
            && strtoupper($cuenta->clasificacion->tipo) === 'INGRESOS';

      $rows = ingresos_egresos::with(['datos_de_pago_ingresos', 'datos_de_pago_egresos'])
        ->where('id_cuentas', $cuenta->id_cuentas)
        ->whereYear('fecha', $year)
        ->get();

        
        $saldo = 0;
        $movimientos = [];

      foreach ($rows as $row) {

            $numeroDocumento = '-';

            if ($row->datos_de_pago_ingresos->count() > 0) {
                $numeroDocumento = $row->datos_de_pago_ingresos->first()->numero_documento;
            }

            if ($row->datos_de_pago_egresos->count() > 0) {
                $numeroDocumento = $row->datos_de_pago_egresos->first()->numero_documento;
            }

            $monto = (float) $row->monto;

            if ($esIngreso) {
                $debita   = $monto;
                $acredita = 0;
                $saldo   += $monto;
            } else {
                $debita   = 0;
                $acredita = $monto;
                $saldo   -= $monto;
            }

            $movimientos[] = [
                'id_ingresos_egresos' => $row->id_ingresos_egresos,
                'nomenclatura'     => $row->nomenclatura,
                'numero_documento' => $numeroDocumento,
                'fecha'            => $row->fecha,
                'cuenta'           => $nombreCuenta,
                'descripcion'      => $row->descripcion,
                'acredita'         => number_format($acredita, 2, '.', ''),
                'debita'           => number_format($debita, 2, '.', ''),
                'total'            => $saldo,
            ];
        }

        return response()->json($movimientos, 200);

    } catch (\Throwable $th) {
        return response()->json([
            'error' => $th->getMessage()
        ], 500);
    }
}

}
