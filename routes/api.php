    <?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Ruta protegida por autenticación Sanctum
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/authenticate', [LoginController::class, 'authenticate']);

Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return response()->json([
        'usuario' => $request->user()->usuarios,
        'id' => $request->user()->id_login,
        'rol' => $request->user()->id_rol
    ]);
});