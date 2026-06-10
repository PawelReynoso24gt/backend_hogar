<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\logins;
use Illuminate\Support\Facades\Crypt;

class LoginController extends Controller
{
    // Método Get (GET)
    public function get(Request $request)
    {
        try {

            if ($request->user()->id_rol != 1) {
                return response()->json([
                    'error' => 'No autorizado'
                ], 403);
            }

            $data = logins::get();

            return response()->json($data, 200);

        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ], 500);
        }
    }

    // Método GetByID (GET)
    // CANDIDATO A IDOR
    public function getById(Request $request, $id) // Se agrega el Request para obtener el usuario autenticado
    {
        try {

            // Este if nos permite eliminar IDOR, se verifica si la info solicitada pertenece al usuario autenticado, si no es así se devuelve un error de no autorizado
            if (
                $request->user()->id_rol != 1 &&
                $request->user()->id_login != $id
            ) {
                return response()->json([
                    'error' => 'No autorizado, dummy no puedes acceder a la información de otros usuarios'
                ], 403);
            }

            $data = logins::find($id);
            if (!$data) {
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            }
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // Método GetByNombre (GET)
    public function getByNombre(Request $request, $usuarios)
    {
        try {

            if (
                $request->user()->id_rol != 1 &&
                $request->user()->usuarios !== $usuarios
            ) {
                return response()->json([
                    'error' => 'No autorizado, dummy no puedes acceder a la información de otros usuarios'
                ], 403);
            }

            $proyecto = logins::where('usuarios', $usuarios)->first();
            if (!$proyecto) {
                return response()->json(['error' => 'El proyecto no existe'], 404);
            }

            // Descifrar la contraseña usando Laravel Crypt
            $decryptedPassword = Crypt::decryptString($proyecto->contrasenias);

            return response()->json([
                'usuarios' => $proyecto->usuarios,
                'contrasenias' => $decryptedPassword, // Mostrar la contraseña descifrada
                'estado' => $proyecto->estado
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function create(Request $request)
    {

        if ($request->user()->id_rol != 1) {
            return response()->json([
                'error' => 'No autorizado'
            ], 403);
        }

        try {
            // Validar los datos de entrada
            $request->validate([
                'usuarios' => 'required|string',
                'contrasenias' => 'required|string',
            ]);

            // Obtener los datos del request
            $data['usuarios'] = $request->input('usuarios');
            $password = $request->input('contrasenias');
            $data['estado'] = 1; // Establecer estado como 1

            // Cifrar la contraseña usando Laravel Crypt
            $data['contrasenias'] = Crypt::encryptString($password);

            // Crear el usuario
            $user = logins::create($data);

            return response()->json([
                'message' => 'Usuario creado',
                'usuario' => $user
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ], 500);
        }
    }


    // Método Update (PUT)
    public function update(Request $request, $usuarios)
    {
        try {

            if (
                $request->user()->id_rol != 1 &&
                $request->user()->usuarios !== $usuarios
            ) {
                return response()->json([
                    'error' => 'No autorizado, dummy no puedes acceder a la información de otros usuarios'
                ], 403);
            }

            // Buscar el proyecto por el nombre
            $proyecto = logins::where('usuarios', $usuarios)->first();

            // Verificar si el proyecto existe
            if (!$proyecto) {
                return response()->json(['error' => 'El proyecto no existe'], 404);
            }

            // Validar la contraseña si está presente en la solicitud
            if ($request->has('contrasenias')) {
                $password = $request->input('contrasenias');

                // Cifrar la contraseña usando Laravel Crypt
                $proyecto->contrasenias = Crypt::encryptString($password);
            }

            // Actualizar los campos que se hayan enviado en la solicitud
            if ($request->has('usuarios')) {
                $proyecto->usuarios = $request->input('usuarios');
            }

            if ($request->has('estado')) {
                $proyecto->estado = $request->input('estado');
            }

            // Guardar los cambios
            $proyecto->save();

            // Obtener el proyecto actualizado
            $updatedProyecto = logins::find($proyecto->id);

            return response()->json($updatedProyecto, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }


    // Método Delete (DELETE)
    public function delete(Request $request, $id)
    {
        try {

            if (
                $request->user()->id_rol != 1 &&
                $request->user()->id_login !== (int) $id
            ) { //if ($request->user()->usuarios !== $id) Esta es la manera en como se manejaba antes
                return response()->json([
                    'error' => 'No autorizado, dummy no puedes acceder a la información de otros usuarios'
                ], 403);
            }

            $user = logins::find($id);
            if (!$user) {
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            }

            $user->delete();
            return response()->json(['message' => 'Usuario borrado'], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // Método Authenticate (POST)
    public function authenticate(Request $request)
    {
        try {
            $usuario = $request->input('usuarios');
            $contrasenias = $request->input('contrasenias');

            // Busca al usuario por el nombre
            $user = logins::where('usuarios', $usuario)->first();
            if (!$user) {
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            }

            // Verifica si el usuario está habilitado
            if ($user->estado === 0) {
                return response()->json(['error' => 'Usuario no está habilitado'], 403);
            }

            // Descifra la contraseña almacenada
            $decryptedPassword = Crypt::decryptString($user->contrasenias);

            // Verifica la contraseña
            if ($contrasenias !== $decryptedPassword) {
                return response()->json(['error' => 'Contraseña incorrecta'], 401);
            }

            // Eliminar tokens antiguos del usuario
            $user->tokens()->delete();

            // Generar nuevo token
            $token = $user->createToken('auth-token')->plainTextToken;

            // Autenticación exitosa
            return response()->json([
                'message' => 'Autenticación exitosa',
                'token' => $token,
                'usuario' => $user->usuarios,
                'id_usuario' => $user->id_login,
                'id_rol' => $user->id_rol
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
}
