<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great! a
|
*/

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\logsController;
use App\Http\Controllers\clasificacionController;
use App\Http\Controllers\bancosController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\cuentasController;
use App\Http\Controllers\ingresos_egresosController;
use App\Http\Controllers\cuentas_bancariasController;
use App\Http\Controllers\reportesGenerales;
use App\Http\Controllers\pagoPendientesController;

Route::get('/', function () {
    return view('welcome');
});

// Rutas de logins
Route::prefix('logins')->group(function () {
    Route::get('/getLogins', [LoginController::class, 'get']);
    Route::get('/getLogins/{id}', [LoginController::class, 'getById']);
    Route::get('/getProjectName/{usuarios}', [LoginController::class, 'getByNombre']);
    Route::post('/create', [LoginController::class, 'create']);
    Route::put('/update/{usuarios}', [LoginController::class, 'update']);
    Route::delete('/delete/{id}', [LoginController::class, 'delete']);
    Route::post('/authenticate', [LoginController::class, 'authenticate']);
});
// Rutas de Proyectos
Route::prefix('proyectos')->group(function () {
    Route::get('/get', [ProjectsController::class, 'get']);
    Route::get('/getProject/{id}', [ProjectsController::class, 'getById']);
    Route::get('/getProjectName/{nombre}', [ProjectsController::class, 'getByNombre']);
    Route::post('/create', [ProjectsController::class, 'create']);
    Route::put('/update/{nombre}', [ProjectsController::class, 'update']);
    Route::delete('/delete/{id}', [ProjectsController::class, 'delete']);
});
// Rutas de Clasificaciones (Ingreso y Egreso)
Route::prefix('clasificacion')->group(function () {
    Route::get('/get', [clasificacionController::class, 'get']);
    Route::get('/get/{id}', [clasificacionController::class, 'getById']);
    Route::get('/getTipo/{tipo}', [clasificacionController::class, 'getByTipo']);
    Route::post('/create', [clasificacionController::class, 'create']);
    Route::put('/update/{id}', [clasificacionController::class, 'update']);
    Route::put('/updateTipo/{tipo}', [clasificacionController::class, 'updateByTipo']);
    Route::delete('/delete/{id}', [clasificacionController::class, 'delete']);
});
// Rutas de Bancos
Route::prefix('bancos')->group(function () {
    Route::get('/get', [bancosController::class, 'get']);
    Route::get('/get/{id}', [bancosController::class, 'getById']);
    Route::get('/getBancoName/{nombre}', [bancosController::class, 'getByNombre']);
    Route::post('/create', [bancosController::class, 'create']);
    Route::put('/update/{id}', [bancosController::class, 'update']);
    Route::put('/updatebyname/{banco}', [bancosController::class, 'updateByBanco']);
    Route::delete('/delete/{id}', [bancosController::class, 'delete']);
});
// Rutas de Logs
Route::prefix('logs')->group(function () {
    Route::get('/get', [logsController::class, 'get']);
    Route::get('/get/{id}', [logsController::class, 'getById']);
});
// Rutas de Cuentas
Route::prefix('cuentas')->group(function () {
    Route::get('/get', [cuentasController::class, 'get']);
    Route::get('/get/{id}', [cuentasController::class, 'getById']);
    Route::get('/get/{cuenta}', [cuentasController::class, 'getByCuenta']);
    Route::get('/getWithNombres', [cuentasController::class, 'getWithNombres']);
    Route::get('/getCrud/{nombre}', [cuentasController::class, 'GetCuentasCRUD']);
    Route::get('/getCuentasIngresos', [cuentasController::class, 'getCuentasIngresos']);
    Route::get('/getCuentasEgresos', [cuentasController::class, 'getCuentasEgresos']);
    Route::post('/create', [cuentasController::class, 'create']);
    Route::get('/getCuentaName/{nombre}', [cuentasController::class, 'getByNombre']);
    Route::put('/update/{cuenta}', [cuentasController::class, 'update']);
    // Route::post('/create', [cuentasController::class, 'create']);
    // Route::put('/update/{id}', [cuentasController::class, 'update']);
    // Route::put('/updatebyname/{cuenta}', [cuentasController::class, 'updateByCuenta']);
    // Route::delete('/delete/{id}', [cuentasController::class, 'delete']);
});
// Rutas de Ingresos y Egresos
Route::prefix('in_eg')->group(function () {
    Route::get('/getInfoAnticipoAG', [ingresos_egresosController::class, 'getInfoAnticipoAG']);
    Route::get('/get', [ingresos_egresosController::class, 'get']);
    Route::get('/getWithCuenta', [ingresos_egresosController::class, 'getWithCuentas']);
    // cuentas a usar
    Route::get('/getAllCuentasIngresoAG', [ingresos_egresosController::class, 'getAllCuentasIngresoAG']);
    Route::get('/getAllCuentasEgresoAG', [ingresos_egresosController::class, 'getAllCuentasEgresoAG']);
    Route::get('/getAllCuentasIngresoCA', [ingresos_egresosController::class, 'getAllCuentasIngresoCA']);
    Route::get('/getAllCuentasEgresoCA', [ingresos_egresosController::class, 'getAllCuentasEgresoCA']);

    // Ruta para la vista de la tabla de anticipo
    Route::get('/tablaVistaAnticipoAG', [ingresos_egresosController::class, 'tablaVistaAnticipoAG']);
    Route::get('/tablaVistaAnticipoCA', [ingresos_egresosController::class, 'tablaVistaAnticipoCA']);

    // Route::post('/create', [ingresos_egresosController::class, 'create']);
    //Route::post('/create', [ingresos_egresosController::class, 'create']);
    Route::put('/update/{nomenclatura}', [ingresos_egresosController::class, 'update']);

    // datos de ingresos de bancos
    Route::get('/getINB', [ingresos_egresosController::class, 'getDatosIngresoBancos']);
    Route::get('/getINEGBDatos', [ingresos_egresosController::class, 'getWithDatosINGB']);
    // Route::post('/createALLIN', [ingresos_egresosController::class, 'createALLIN']);
    // Route::post('/createALLEG', [ingresos_egresosController::class, 'createALLEG']);
    // Route::post('/createALLINEGCaja', [ingresos_egresosController::class, 'createALLINEGCaja']);

    // ingresos de proyecto agricola ----------------------------------------------------------
    Route::post('/createALLINAG', [ingresos_egresosController::class, 'createALLINAG']);
    Route::post('/createALLEGAG', [ingresos_egresosController::class, 'createALLEGAG']);
    Route::post('/createALLINEGCajaAG', [ingresos_egresosController::class, 'createALLINEGCajaAG']);

    // ingresos de proyecto capilla -----------------------------------------------------------
    Route::post('/createALLINCA', [ingresos_egresosController::class, 'createALLINCA']);
    Route::post('/createALLEGCA', [ingresos_egresosController::class, 'createALLEGCA']);
    Route::post('/createALLINEGCajaCA', [ingresos_egresosController::class, 'createALLINEGCajaCA']);

    Route::get('/getByCuentas', [ingresos_egresosController::class, 'getAllCuentasEgreso']);
    Route::get('/getByCuentasI', [ingresos_egresosController::class, 'getAllCuentasIngreso']);
    Route::get('/getByNombreB', [ingresos_egresosController::class, 'getByNombreBanco']);
    // Route::get('/getINBDatos', [ingresos_egresosController::class, 'getWithDatosINGB']);

    // AGRÍCOLA
    // traslados internos (depósito de caja)
    Route::post('/createTrasDepCajaAG', [ingresos_egresosController::class, 'createTrasDepCajaAG']);
    // traslados internos (retiro de bancos)
    Route::post('/createTrasRetBanAG', [ingresos_egresosController::class, 'createTrasRetBanAG']);

    // CAPILLA
    // traslados internos (depósito de caja)
    Route::post('/createTrasDepCajaCA', [ingresos_egresosController::class, 'createTrasDepCajaCA']);
    // traslados internos (retiro de bancos)
    Route::post('/createTrasRetBanCA', [ingresos_egresosController::class, 'createTrasRetBanCA']);
    Route::post('/fecha', [ingresos_egresosController::class, 'getWithCuentasByDate']);
    Route::post('/fechaBanco', [ingresos_egresosController::class, 'getWithCuentasBancosByDate']);
    Route::post('/libroDiario', [ingresos_egresosController::class, 'getWithCuentasLibroDiario']);

    // reportes de capilla
    Route::post('/fechaCA', [ingresos_egresosController::class, 'getWithCuentasByDateCA']);
    Route::post('/fechaBancoCA', [ingresos_egresosController::class, 'getWithCuentasBancosByDateCA']);
    Route::post('/libroDiarioCA', [ingresos_egresosController::class, 'getWithCuentasLibroDiarioCA']);

    // reporte final agricola
    Route::post('/reporteFinalAG', [ingresos_egresosController::class, 'getReporteFinalAG']);
    Route::post('/reporteFinalCA', [ingresos_egresosController::class, 'getReporteFinalCA']);
    // reportes generales (nuevos controladores optimizados)
    Route::post('/reporteGeneralAG', [reportesGenerales::class, 'reporteGeneralAgricola']);
    Route::post('/reporteGeneralCA', [reportesGenerales::class, 'reporteGeneralCapilla']);
    Route::post('/getReporteEstadoResultadosCA', [ingresos_egresosController::class, 'getReporteEstadoResultadosCA']);
    Route::post('/getReporteEstadoResultadosAG', [ingresos_egresosController::class, 'getReporteEstadoResultadosAG']);

    // Anticipo sobre compras
    Route::post('/createAnticipoCompraAG', [ingresos_egresosController::class, 'anticipoAG']);
    Route::post('/createAnticipoCompraCA', [ingresos_egresosController::class, 'anticipoCA']);
});
// Rutas de cuentas bancarias
Route::prefix('cuentasB')->group(function () {
    Route::get('/get', [cuentas_bancariasController::class, 'get']);
    Route::get('/getWithBancos', [cuentas_bancariasController::class, 'getWithBancos']);
    Route::get('/get/{cuentaB}', [cuentas_bancariasController::class, 'getByCuentaB']);
    Route::post('/create', [cuentas_bancariasController::class, 'create']);
    Route::put('/update/{cuentaB}', [cuentas_bancariasController::class, 'update']);
    Route::get('/getConcatenada', [cuentas_bancariasController::class, 'getByCuentaBName']);
    Route::post('/getNumeroCuenta/{numero_cuenta}', [cuentas_bancariasController::class, 'getByCuentaId']);
    Route::get('/cuentas_bancarias/{numero_cuenta}', [cuentas_bancariasController::class, 'getByCuenta']);
    Route::get('/for-select', [cuentas_bancariasController::class, 'getIdCuenta']);

});

Route::prefix('pago_pendientes')->group(function () {
    Route::post('/createNewPagoPendiente', [pagoPendientesController::class, 'addNewPendientes']);
});

Route::prefix('saldar_anticipos')->group(function () {
    Route::post('/saldarAnticipoAG', [App\Http\Controllers\saldarAnticipos::class, 'saldarAnticipoAG']);
    Route::post('/getMontoFaltanteAG', [App\Http\Controllers\saldarAnticipos::class, 'getMontoFaltanteAG']);
});