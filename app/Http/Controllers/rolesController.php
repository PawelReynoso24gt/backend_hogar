<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\roles;

class rolesController extends Controller
{
    // Obtener todos los roles
    public function get()
    {
        try {
            $data = roles::get();
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ], 500);
        }
    }

    // Obtener rol por ID
    public function getById($id)
    {
        try {
            $data = roles::find($id);

            if (!$data) {
                return response()->json([
                    'error' => 'Rol no encontrado'
                ], 404);
            }

            return response()->json($data, 200);

        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ], 500);
        }
    }

    // Obtener rol por nombre
    public function getByNombre($nombre)
    {
        try {
            $data = roles::where('nombre', $nombre)->first();

            if (!$data) {
                return response()->json([
                    'error' => 'Rol no encontrado'
                ], 404);
            }

            return response()->json($data, 200);

        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ], 500);
        }
    }

    // Crear rol
    public function create(Request $request)
    {
        try {

            $request->validate([
                'nombre' => 'required|string|max:50|unique:roles,nombre',
                'descripcion' => 'nullable|string'
            ]);

            $rol = roles::create([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'estado' => 1
            ]);

            return response()->json([
                'message' => 'Rol creado correctamente',
                'rol' => $rol
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ], 500);
        }
    }

    // Actualizar rol
    public function update(Request $request, $id)
    {
        try {

            $rol = roles::find($id);

            if (!$rol) {
                return response()->json([
                    'error' => 'Rol no encontrado'
                ], 404);
            }

            if ($request->has('nombre')) {
                $rol->nombre = $request->nombre;
            }

            if ($request->has('descripcion')) {
                $rol->descripcion = $request->descripcion;
            }

            if ($request->has('estado')) {
                $rol->estado = $request->estado;
            }

            $rol->save();

            return response()->json([
                'message' => 'Rol actualizado correctamente',
                'rol' => $rol
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ], 500);
        }
    }

    // Desactivar rol
    public function desactivar($id)
    {
        try {

            $rol = roles::find($id);

            if (!$rol) {
                return response()->json([
                    'error' => 'Rol no encontrado'
                ], 404);
            }

            // Este metodo no nos deja desactivar un rol que tenga usuarios asignados, para evitar problemas de integridad referencial
            if ($rol->usuarios()->count() > 0) {
                return response()->json([
                    'error' => 'No se puede desactivar un rol que tiene usuarios asignados'
                ], 409);
            }

            $rol->estado = 0;
            $rol->save();

            return response()->json([
                'message' => 'Rol desactivado correctamente'
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ], 500);
        }
    }

    // Activar rol nuevamente
    public function activar($id)
    {
        try {

            $rol = roles::find($id);

            if (!$rol) {
                return response()->json([
                    'error' => 'Rol no encontrado'
                ], 404);
            }

            $rol->estado = 1;
            $rol->save();

            return response()->json([
                'message' => 'Rol activado correctamente'
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
