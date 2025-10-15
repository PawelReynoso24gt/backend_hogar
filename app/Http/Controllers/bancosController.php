<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\bancos;

class bancosController extends Controller
{
    //Metodo get
    public function get(){
        try{
            $data = bancos::get();
            return response()->json($data, 200);
            } catch (\Throwable $th){
                return response()->json(['error' => $th ->getMessage()],500);
            }
       }

       //Metodo get by id
       public function getById($id){
        try {
            $data = bancos::find($id);
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json([ 'error' => $th->getMessage()], 500);
        }
    }   

    //Get by name
       public function getByNombre($banco){
        try {
            $banco = bancos::where('banco', $banco)->first();
            if (!$banco) {
                return response()->json(['error' => 'El banco no existe'], 404);
            }
            return response()->json($banco, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }


    //Metodo Create
    public function create(Request $request){
        try {
            // Validar si ya existe un banco con el mismo nombre
            $existingBanco = bancos::where('banco', $request['banco'])->first();
    
            if ($existingBanco) {
                // Si ya existe un banco con el mismo nombre, devuelve un error
                return response()->json(['error' => 'Ya existe un banco con este nombre.'], 400);
            }
    
            // Si no existe un banco con el mismo nombre, crea uno nuevo
            $data['banco'] = $request['banco'];
            $data['estado'] = 1; // Establecer estado como 1
            $res = bancos::create($data);
            return response()->json($res, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // Metodo Update
    public function update(Request $request, $id) {
        try {
            $banco = bancos::find($id);
            if (!$banco) {
                return response()->json(['error' => 'Banco no encontrado'], 404);
            }
    
            // Crear un arreglo vacío para almacenar los datos que se van a actualizar
            $data = [];
    
            // Verificar si el nombre del banco está presente en la solicitud
            if ($request->has('banco')) {
                $data['banco'] = $request['banco'];
            }
    
            // Verificar si el estado está presente en la solicitud
            if ($request->has('estado')) {
                $data['estado'] = $request['estado'];
            }
    
            // Si hay datos para actualizar, hacer la actualización
            if (!empty($data)) {
                $banco->update($data);
            }
    
            $res = bancos::find($id);
            return response()->json($res, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Error al actualizar el banco'], 500);
        }
    }    

    // Método Delete
    public function delete($id) {
        try {
            $banco = bancos::find($id);
            if (!$banco) {
                return response()->json(['error' => 'Banco no encontrado'], 404);
            }

            $banco->delete();
            return response()->json(['message' => 'Banco eliminado correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Error al eliminar el banco'], 500);
        }
    }

    // Método Update por banco
    public function updateByBanco(Request $request, $banco){
        try {
            // Buscar la clasificación por el tipo
            $bancos = bancos::where('banco', $banco)->first();
            
            // Verificar si la clasificación existe
            if (!$bancos) {
                return response()->json(['error' => 'El Banco no existe'], 404);
            }

            // Actualizar solo los campos que se hayan enviado en la solicitud
            if ($request->has('banco')) {
                $bancos->banco = $request->input('banco');
            }

            // Verificar si el estado está presente en la solicitud
            if ($request->has('estado')) {
                $bancos->estado = $request->input('estado'); // Actualizar la propiedad 'estado' del modelo
            }

            // Guardar los cambios
            $bancos->save();

            // Obtener la clasificación actualizada
            $updatedBancos = bancos::find($bancos->id);

            return response()->json($updatedBancos, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }


}
