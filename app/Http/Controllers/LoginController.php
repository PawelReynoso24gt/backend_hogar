<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\logins;
use Illuminate\Support\Facades\Crypt;

class LoginController extends Controller
{
    // Método Get (GET)
    public function get(){
        try {
            $data = logins::get();
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // Método GetByID (GET)
    public function getById($id){
        try {
            $data = logins::find($id);
            if(!$data) {
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            }
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // Método GetByNombre (GET)
    public function getByNombre($usuarios){
        try {
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

    public function create(Request $request){
        try {
            // Validar los datos de entrada
            $request->validate([
                'usuarios' => 'required|string',
                'contrasenias' => 'required|string',
            ]);
    
           
    
            // Validar la contraseña
            /*if (strlen($password) < 8) {
                return response()->json(['error' => 'La contraseña no puede ser menor a 8 dígitos'], 400);
            }
            if (!preg_match('/[A-Z]/', $password)) {
                return response()->json(['error' => 'La contraseña debe contener al menos una letra mayúscula'], 400);
            }
            if (!preg_match('/[a-z]/', $password)) {
                return response()->json(['error' => 'La contraseña debe contener al menos una letra minúscula'], 400);
            }
            if (!preg_match('/[0-9]/', $password)) {
                return response()->json(['error' => 'La contraseña debe contener al menos un número'], 400);
            }
            if (preg_match('/^[0-9]|[0-9]$/', $password)) {
                return response()->json(['error' => 'El número en la contraseña no puede estar ni al principio ni al final'], 400);
            }
            if (!preg_match('/[!@#$]/', $password)) {
                return response()->json(['error' => 'La contraseña debe contener al menos uno de estos caracteres especiales: !@#$'], 400);
            }
            if (preg_match('/^[!@#$]|[!@#$]$/',     $password)) {
                return response()->json(['error' => 'Los caracteres especiales no pueden ir ni al principio ni al final'], 400);
            }*/
    
            // Obtener los datos del request
            $data['usuarios'] = $request->input('usuarios');
            $password = $request->input('contrasenias');
            $data['estado'] = 1; // Establecer estado como 1
        
            // Cifrar la contraseña usando Laravel Crypt
            $data['contrasenias'] = Crypt::encryptString($password);
        
            // Crear el usuario
            $user = logins::create($data);
    
            return response()->json(['message' => 'Usuario creado', 'usuario' => $user], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
    

    // Método Update (PUT)
    public function update(Request $request, $usuarios){
        try {
            // Buscar el proyecto por el nombre
            $proyecto = logins::where('usuarios', $usuarios)->first();

            // Verificar si el proyecto existe
            if (!$proyecto) {
                return response()->json(['error' => 'El proyecto no existe'], 404);
            }

            // Validar la contraseña si está presente en la solicitud
            if ($request->has('contrasenias')) {
                $password = $request->input('contrasenias');

                // Validar la contraseña
               /* if (strlen($password) < 8) {
                    return response()->json(['error' => 'La contraseña no puede ser menor a 8 dígitos'], 400);
                }
                if (!preg_match('/[A-Z]/', $password)) {
                    return response()->json(['error' => 'La contraseña debe contener al menos una letra mayúscula'], 400);
                }
                if (!preg_match('/[a-z]/', $password)) {
                    return response()->json(['error' => 'La contraseña debe contener al menos una letra minúscula'], 400);
                }
                if (!preg_match('/[0-9]/', $password)) {
                    return response()->json(['error' => 'La contraseña debe contener al menos un número'], 400);
                }
                if (preg_match('/^[0-9]|[0-9]$/', $password)) {
                    return response()->json(['error' => 'El número en la contraseña no puede estar ni al principio ni al final'], 400);
                }
                if (!preg_match('/[!@#$]/', $password)) {
                    return response()->json(['error' => 'La contraseña debe contener al menos uno de estos caracteres especiales: !@#$'], 400);
                }
                if (preg_match('/^[!@#$]|[!@#$]$/', $password)) {
                    return response()->json(['error' => 'Los caracteres especiales no pueden ir ni al principio ni al final'], 400);
                }*/

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
    public function delete($id){
        try {
            $user = logins::find($id);
            if(!$user) {
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

            // Autenticación exitosa
            return response()->json(['message' => 'Autenticación exitosa'], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
}
