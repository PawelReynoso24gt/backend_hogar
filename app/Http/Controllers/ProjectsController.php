<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\proyectos;

class ProjectsController extends Controller
{
    //Method get
    public function get(){
        try{
            $tareas = proyectos::all();
            return response()->json($tareas, 200);
        }catch(\Exception $e){
            return response()->json($e->getMessage(), 500);
        }
    }

    //Method get by id
    public function getById($id){
        try {
            $data = proyectos::find($id);
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json([ 'error' => $th->getMessage()], 500);
        }
    }

    //Method get my name
    public function getByNombre($nombre){
        try {
            $proyecto = proyectos::where('nombre', $nombre)->first();
            if (!$proyecto) {
                return response()->json(['error' => 'El proyecto no existe'], 404);
            }
            return response()->json($proyecto, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }


    //Methos post
    public function create(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required',
            ]);

            $data = [
                'nombre' => $request->input('nombre'),
                'estado' => 1
            ];

            $proyecto = proyectos::create($data);

            return response()->json($proyecto, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function update(Request $request, $nombre){
        try {
            // Buscar el proyecto por el nombre
            $proyecto = proyectos::where('nombre', $nombre)->first();
            
            // Verificar si el proyecto existe
            if (!$proyecto) {
                return response()->json(['error' => 'El proyecto no existe'], 404);
            }
    
            // Actualizar solo los campos que se hayan enviado en la solicitud
            if ($request->has('nombre')) {
                $proyecto->nombre = $request->input('nombre');
            }
    
            if ($request->has('estado')) {
                $proyecto->estado = $request->input('estado');
            }
    
            // Guardar los cambios
            $proyecto->save();
    
            // Obtener el proyecto actualizado
            $updatedProyecto = proyectos::find($proyecto->id);
    
            return response()->json($updatedProyecto, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
    

    //Method delete
    public function delete($id)
    {
        try {
            $proyecto = proyectos::find($id);
            
            // Verificar si el proyecto existe antes de intentar eliminarlo
            if (!$proyecto) {
                return response()->json(['error' => 'El proyecto no existe'], 404);
            }

            $proyecto->delete();

            return response()->json(['message' => 'Proyecto eliminado correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
}
