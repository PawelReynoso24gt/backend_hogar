<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\clasificacion;


class clasificacionController extends Controller
{

    //Metodo get
    public function get(){
        try{
            $data = clasificacion::get();
            return response()->json($data, 200);
        } catch (\Throwable $th){
            return response()->json(['error' => $th ->getMessage()],500);
        }
    }

    //Metodo get by id
    public function getById($id){
        try {
            $data = clasificacion::find($id);
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json([ 'error' => $th->getMessage()], 500);
        }
    }
    
    // Método get by tipo
    public function getByTipo($tipo){
        try {
            $data = clasificacion::where('tipo', $tipo)->first();
            if (!$data) {
                return response()->json(['error' => 'El elemento no existe'], 404);
            }
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }


    //Metodo Create
    public function create(Request $request){
        try {
            $data['tipo'] = $request['tipo'];
            $res = clasificacion::create($data);
            return response()->json( $res, 200);
        } catch (\Throwable $th) {
            return response()->json([ 'error' => $th->getMessage()], 500);
        }
    }

    // Metodo Update
    public function update(Request $request, $id) {
        try {
            $clasificacion = clasificacion::find($id);
            if (!$clasificacion) {
                return response()->json(['error' => 'Clasificación no encontrada'], 404);
            }

            $data['tipo'] = $request['tipo'];
            $clasificacion->update($data);
            $res = clasificacion::find($id);
            return response()->json($res, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Error al actualizar la clasificación'], 500);
        }
    }

    // Método Update por tipo
    public function updateByTipo(Request $request, $tipo){
        try {
            // Buscar la clasificación por el tipo
            $clasificacion = clasificacion::where('tipo', $tipo)->first();
            
            // Verificar si la clasificación existe
            if (!$clasificacion) {
                return response()->json(['error' => 'La clasificación no existe'], 404);
            }
    
            // Actualizar solo los campos que se hayan enviado en la solicitud
            if ($request->has('tipo')) {
                $clasificacion->tipo = $request->input('tipo');
            }
    
            // Guardar los cambios
            $clasificacion->save();
    
            // Obtener la clasificación actualizada
            $updatedClasificacion = clasificacion::find($clasificacion->id);
    
            return response()->json($updatedClasificacion, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // Metodo Delete
    public function delete($id) {
        try {
            $clasificacion = clasificacion::find($id);
            if (!$clasificacion) {
                return response()->json(['error' => 'Clasificación no encontrada'], 404);
            }

            $clasificacion->delete();
            return response()->json(['message' => 'Clasificación eliminada correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Error al eliminar la clasificación'], 500);
        }
    }
}