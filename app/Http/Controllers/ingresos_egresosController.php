<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ingresos_egresos;
use App\Models\cuentas;
use App\Models\datos_de_pago_ingresos;
use App\Models\datos_de_pago_egresos;
use App\Models\cuentas_bancarias;
use App\Models\bancos;

class ingresos_egresosController extends Controller
{
    // Método Get
    public function get(){
        try{
            $data = ingresos_egresos::get();
            return response()->json($data, 200);
        } catch (\Throwable $th){
            return response()->json(['error' => $th ->getMessage()],500);
        }
    }

    // reporte estado de resultados de capilla (solo EGRESOS) - POST only
    public function getReporteEstadoResultadosCA(Request $request)
    {
        try {
            // Requerir 'tipo' y demás campos en POST
            $validaciondata = $request->validate([
                'tipo' => 'required|string',
                'mes' => 'required|string|in:enero,febrero,marzo,abril,mayo,junio,julio,agosto,septiembre,octubre,noviembre,diciembre',
                //'sirviente' => 'required|string',
                //'responsable' => 'required|string',
                //'economa' => 'required|string'
            ]);

            $tipo = $validaciondata['tipo'];
            $mes = $validaciondata['mes'];
            //$sirviente = $validaciondata['sirviente'];
            //$responsable = $validaciondata['responsable'];
            //$economa = $validaciondata['economa'];

            // calcular rango de fechas según tipo (mensual, trimestral, semestral, anual)
            $fechaInicial = null;
            $fechaFinal = null;
            $añoActual = Carbon::now()->year;

            switch ($tipo) {
                case 'mensual':
                    switch ($mes) {
                        case 'enero':
                            $fechaInicial = Carbon::createFromDate($añoActual - 1, 12, 31);
                            $fechaFinal = Carbon::createFromDate($añoActual, 1, 31);
                            break;
                        case 'febrero':
                            $fechaInicial = Carbon::createFromDate($añoActual, 1, 31);
                            $fechaFinal = Carbon::createFromDate($añoActual, 2, Carbon::createFromDate($añoActual, 2, 1)->daysInMonth);
                            break;
                        case 'marzo':
                            $fechaInicial = Carbon::createFromDate($añoActual, 2, Carbon::createFromDate($añoActual, 2, 1)->daysInMonth);
                            $fechaFinal = Carbon::createFromDate($añoActual, 3, 31);
                            break;
                        case 'abril':
                            $fechaInicial = Carbon::createFromDate($añoActual, 3, 31);
                            $fechaFinal = Carbon::createFromDate($añoActual, 4, 30);
                            break;
                        case 'mayo':
                            $fechaInicial = Carbon::createFromDate($añoActual, 4, 30);
                            $fechaFinal = Carbon::createFromDate($añoActual, 5, 31);
                            break;
                        case 'junio':
                            $fechaInicial = Carbon::createFromDate($añoActual, 5, 31);
                            $fechaFinal = Carbon::createFromDate($añoActual, 6, 30);
                            break;
                        case 'julio':
                            $fechaInicial = Carbon::createFromDate($añoActual, 6, 30);
                            $fechaFinal = Carbon::createFromDate($añoActual, 7, 31);
                            break;
                        case 'agosto':
                            $fechaInicial = Carbon::createFromDate($añoActual, 7, 31);
                            $fechaFinal = Carbon::createFromDate($añoActual, 8, 31);
                            break;
                        case 'septiembre':
                            $fechaInicial = Carbon::createFromDate($añoActual, 8, 31);
                            $fechaFinal = Carbon::createFromDate($añoActual, 9, 30);
                            break;
                        case 'octubre':
                            $fechaInicial = Carbon::createFromDate($añoActual, 9, 30);
                            $fechaFinal = Carbon::createFromDate($añoActual, 10, 31);
                            break;
                        case 'noviembre':
                            $fechaInicial = Carbon::createFromDate($añoActual, 10, 31);
                            $fechaFinal = Carbon::createFromDate($añoActual, 11, 30);
                            break;
                        case 'diciembre':
                            $fechaInicial = Carbon::createFromDate($añoActual, 11, 30);
                            $fechaFinal = Carbon::createFromDate($añoActual, 12, 31);
                            break;
                        default:
                            return response()->json(['error' => 'Mes inválido'], 400);
                    }
                    break;
                // Para trimestral, semestral y anual puedes ampliar aquí (por simplicidad trataré trimestral similar al original)
                case 'trimestral':
                    // definir trimestres por mes de inicio
                    switch ($mes) {
                        case 'enero':
                            $fechaInicial = Carbon::createFromDate($añoActual - 1, 12, 31);
                            $fechaFinal = Carbon::createFromDate($añoActual, 3, 31);
                            break;
                        case 'abril':
                            $fechaInicial = Carbon::createFromDate($añoActual, 3, 31);
                            $fechaFinal = Carbon::createFromDate($añoActual, 6, 30);
                            break;
                        case 'julio':
                            $fechaInicial = Carbon::createFromDate($añoActual, 6, 30);
                            $fechaFinal = Carbon::createFromDate($añoActual, 9, 30);
                            break;
                        case 'octubre':
                            $fechaInicial = Carbon::createFromDate($añoActual, 9, 30);
                            $fechaFinal = Carbon::createFromDate($añoActual, 12, 31);
                            break;
                        default:
                            return response()->json(['error' => 'Mes inválido para trimestral'], 400);
                    }
                    break;
                case 'semestral':
                    if ($mes === 'enero') {
                        $fechaInicial = Carbon::createFromDate($añoActual - 1, 12, 31);
                        $fechaFinal = Carbon::createFromDate($añoActual, 6, 30);
                    } elseif ($mes === 'julio') {
                        $fechaInicial = Carbon::createFromDate($añoActual, 6, 30);
                        $fechaFinal = Carbon::createFromDate($añoActual, 12, 31);
                    } else {
                        return response()->json(['error' => 'Mes inválido para semestral'], 400);
                    }
                    break;
                case 'anual':
                    $fechaInicial = Carbon::createFromDate($añoActual - 1, 12, 31);
                    $fechaFinal = Carbon::createFromDate($añoActual, 12, 31);
                    break;
                default:
                    return response()->json(['error' => 'Tipo inválido'], 400);
            }

            // fecha anterior (saldo inicial hasta el día anterior a fechaInicial)
            $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));

            // Calcular saldos iniciales (bancos y caja) hasta fechaAnterior — considerar ingresos y egresos previos
            $ingresosAnterioresBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                ->whereHas('cuentas', function ($q) { $q->where('id_proyectos', 2); })
                ->where('tipo', 'bancos')
                ->where('nomenclatura', 'like', 'IN%')
                ->sum('monto');

            $egresosAnterioresBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                ->whereHas('cuentas', function ($q) { $q->where('id_proyectos', 2); })
                ->where('tipo', 'bancos')
                ->where('nomenclatura', 'like', 'EG%')
                ->sum('monto');

            $ingresosAnterioresCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                ->whereHas('cuentas', function ($q) { $q->where('id_proyectos', 2); })
                ->where('tipo', 'caja')
                ->where('nomenclatura', 'like', 'IN%')
                ->sum('monto');

            $egresosAnterioresCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                ->whereHas('cuentas', function ($q) { $q->where('id_proyectos', 2); })
                ->where('tipo', 'caja')
                ->where('nomenclatura', 'like', 'EG%')
                ->sum('monto');

            $saldoInicialBancos = $ingresosAnterioresBancos - $egresosAnterioresBancos;
            $saldoInicialCaja = $ingresosAnterioresCaja - $egresosAnterioresCaja;
            $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

            // Consultar todas las cuentas de egreso de Capilla y sumar EGRESOS por cuenta en el período
            $cuentasEgreso = cuentas::where('id_clasificacion', '2')
                ->where('id_proyectos', 2)
                ->whereNotIn('cuenta', ['Traslado a Bancos desde Caja', 'Traslado a Caja desde Bancos'])
                ->get();

            // Debug: log fechas y cantidad de movimientos EG en el periodo
            try {
                Log::debug('getReporteEstadoResultadosCA period', [
                    'fechaInicial' => $fechaInicial ? $fechaInicial->toDateString() : null,
                    'fechaFinal' => $fechaFinal ? $fechaFinal->toDateString() : null,
                ]);

                $countEg = ingresos_egresos::whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->whereHas('cuentas', function ($q) { $q->where('id_proyectos', 2); })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->count();

                Log::debug('getReporteEstadoResultadosCA egresos_count', ['count' => $countEg]);

                // Log sample sums for first 8 accounts to inspect
                foreach ($cuentasEgreso->take(8) as $c) {
                    $sumCaja = (float) ingresos_egresos::where('id_cuentas', $c->id_cuentas)
                        ->where('tipo', 'caja')
                        ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                        ->where('nomenclatura', 'like', 'EG%')
                        ->sum('monto');
                    $sumBancos = (float) ingresos_egresos::where('id_cuentas', $c->id_cuentas)
                        ->where('tipo', 'bancos')
                        ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                        ->where('nomenclatura', 'like', 'EG%')
                        ->sum('monto');

                    Log::debug('getReporteEstadoResultadosCA sample_account', [
                        'cuenta_id' => $c->id_cuentas,
                        'cuenta' => $c->cuenta,
                        'sumCaja' => $sumCaja,
                        'sumBancos' => $sumBancos,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::debug('getReporteEstadoResultadosCA debug_error', ['msg' => $e->getMessage()]);
            }

            $dataGroupedCaja = $cuentasEgreso->map(function ($cuenta) use ($fechaInicial, $fechaFinal) {
                $egresos = ingresos_egresos::where('id_cuentas', $cuenta->id_cuentas)
                    ->where('tipo', 'caja')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                return [
                    'cuenta' => $cuenta->cuenta,
                    'egresos' => number_format($egresos, 2)
                ];
            })->values();

            $dataGroupedBancos = $cuentasEgreso->map(function ($cuenta) use ($fechaInicial, $fechaFinal) {
                $egresos = ingresos_egresos::where('id_cuentas', $cuenta->id_cuentas)
                    ->where('tipo', 'bancos')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                return [
                    'cuenta' => $cuenta->cuenta,
                    'egresos' => number_format($egresos, 2)
                ];
            })->values();

            $totalEgresosCaja = $dataGroupedCaja->sum(function ($item) {
                return floatval(str_replace(',', '', $item['egresos']));
            });

            $totalEgresosBancos = $dataGroupedBancos->sum(function ($item) {
                return floatval(str_replace(',', '', $item['egresos']));
            });
            $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

            // Calcular saldo final restando solo EGRESOS (estado de resultados centrado en egresos)
            $saldoFinal = ($saldoInicial) - $totalGeneralEgresos;

            return response()->json([
                'fecha_anterior' => $fechaAnterior,
                'mes' => $mes,
                'fecha_inicial' => $fechaInicial->toDateString(),
                'fecha_final' => $fechaFinal->toDateString(),
                'saldo_inicial_bancos' => $saldoInicialBancos,
                'saldo_inicial_caja' => $saldoInicialCaja,
                'saldo_inicial' => $saldoInicial,
                'total_egresos_caja' => $totalEgresosCaja,
                'total_egresos_bancos' => $totalEgresosBancos,
                'total_general_egresos' => $totalGeneralEgresos,
                'data_caja' => $dataGroupedCaja,
                'data_bancos' => $dataGroupedBancos,
                'total_saldo_final' => $saldoFinal,
                //'responsable' => $responsable,
                //'sirviente' => $sirviente,
                //'economa' => $economa
            ], 200);

        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    //metodo get estado de resultados agricola
    public function getReporteEstadoResultadosAG(Request $request)
    {
        try {
            // Requerir 'tipo' y demás campos en POST
            $validaciondata = $request->validate([
                'tipo' => 'required|string',
                'mes' => 'required|string|in:enero,febrero,marzo,abril,mayo,junio,julio,agosto,septiembre,octubre,noviembre,diciembre',
            ]);

            $tipo = $validaciondata['tipo'];
            $mes = $validaciondata['mes'];

            // calcular rango de fechas según tipo (mensual, trimestral, semestral, anual)
            $fechaInicial = null;
            $fechaFinal = null;
            $añoActual = Carbon::now()->year;

            switch ($tipo) {
                case 'mensual':
                    switch ($mes) {
                        case 'enero':
                            $fechaInicial = Carbon::createFromDate($añoActual - 1, 12, 31);
                            $fechaFinal = Carbon::createFromDate($añoActual, 1, 31);
                            break;
                        case 'febrero':
                            $fechaInicial = Carbon::createFromDate($añoActual, 1, 31);
                            $fechaFinal = Carbon::createFromDate($añoActual, 2, Carbon::createFromDate($añoActual, 2, 1)->daysInMonth);
                            break;
                        case 'marzo':
                            $fechaInicial = Carbon::createFromDate($añoActual, 2, Carbon::createFromDate($añoActual, 2, 1)->daysInMonth);
                            $fechaFinal = Carbon::createFromDate($añoActual, 3, 31);
                            break;
                        case 'abril':
                            $fechaInicial = Carbon::createFromDate($añoActual, 3, 31);
                            $fechaFinal = Carbon::createFromDate($añoActual, 4, 30);
                            break;
                        case 'mayo':
                            $fechaInicial = Carbon::createFromDate($añoActual, 4, 30);
                            $fechaFinal = Carbon::createFromDate($añoActual, 5, 31);
                            break;
                        case 'junio':
                            $fechaInicial = Carbon::createFromDate($añoActual, 5, 31);
                            $fechaFinal = Carbon::createFromDate($añoActual, 6, 30);
                            break;
                        case 'julio':
                            $fechaInicial = Carbon::createFromDate($añoActual, 6, 30);
                            $fechaFinal = Carbon::createFromDate($añoActual, 7, 31);
                            break;
                        case 'agosto':
                            $fechaInicial = Carbon::createFromDate($añoActual, 7, 31);
                            $fechaFinal = Carbon::createFromDate($añoActual, 8, 31);
                            break;
                        case 'septiembre':
                            $fechaInicial = Carbon::createFromDate($añoActual, 8, 31);
                            $fechaFinal = Carbon::createFromDate($añoActual, 9, 30);
                            break;
                        case 'octubre':
                            $fechaInicial = Carbon::createFromDate($añoActual, 9, 30);
                            $fechaFinal = Carbon::createFromDate($añoActual, 10, 31);
                            break;
                        case 'noviembre':
                            $fechaInicial = Carbon::createFromDate($añoActual, 10, 31);
                            $fechaFinal = Carbon::createFromDate($añoActual, 11, 30);
                            break;
                        case 'diciembre':
                            $fechaInicial = Carbon::createFromDate($añoActual, 11, 30);
                            $fechaFinal = Carbon::createFromDate($añoActual, 12, 31);
                            break;
                        default:
                            return response()->json(['error' => 'Mes inválido'], 400);
                    }
                    break;
                // Para trimestral, semestral y anual puedes ampliar aquí (por simplicidad trataré trimestral similar al original)
                case 'trimestral':
                    // definir trimestres por mes de inicio
                    switch ($mes) {
                        case 'enero':
                            $fechaInicial = Carbon::createFromDate($añoActual - 1, 12, 31);
                            $fechaFinal = Carbon::createFromDate($añoActual, 3, 31);
                            break;
                        case 'abril':
                            $fechaInicial = Carbon::createFromDate($añoActual, 3, 31);
                            $fechaFinal = Carbon::createFromDate($añoActual, 6, 30);
                            break;
                        case 'julio':
                            $fechaInicial = Carbon::createFromDate($añoActual, 6, 30);
                            $fechaFinal = Carbon::createFromDate($añoActual, 9, 30);
                            break;
                        case 'octubre':
                            $fechaInicial = Carbon::createFromDate($añoActual, 9, 30);
                            $fechaFinal = Carbon::createFromDate($añoActual, 12, 31);
                            break;
                        default:
                            return response()->json(['error' => 'Mes inválido para trimestral'], 400);
                    }
                    break;
                case 'semestral':
                    if ($mes === 'enero') {
                        $fechaInicial = Carbon::createFromDate($añoActual - 1, 12, 31);
                        $fechaFinal = Carbon::createFromDate($añoActual, 6, 30);
                    } elseif ($mes === 'julio') {
                        $fechaInicial = Carbon::createFromDate($añoActual, 6, 30);
                        $fechaFinal = Carbon::createFromDate($añoActual, 12, 31);
                    } else {
                        return response()->json(['error' => 'Mes inválido para semestral'], 400);
                    }
                    break;
                case 'anual':
                    $fechaInicial = Carbon::createFromDate($añoActual - 1, 12, 31);
                    $fechaFinal = Carbon::createFromDate($añoActual, 12, 31);
                    break;
                default:
                    return response()->json(['error' => 'Tipo inválido'], 400);
            }

            // fecha anterior (saldo inicial hasta el día anterior a fechaInicial)
            $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));

            // Calcular saldos iniciales (bancos y caja) hasta fechaAnterior — considerar ingresos y egresos previos
            $ingresosAnterioresBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                ->whereHas('cuentas', function ($q) { $q->where('id_proyectos', 1); })
                ->where('tipo', 'bancos')
                ->where('nomenclatura', 'like', 'IN%')
                ->sum('monto');

            $egresosAnterioresBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                ->whereHas('cuentas', function ($q) { $q->where('id_proyectos', 1); })
                ->where('tipo', 'bancos')
                ->where('nomenclatura', 'like', 'EG%')
                ->sum('monto');

            $ingresosAnterioresCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                ->whereHas('cuentas', function ($q) { $q->where('id_proyectos', 1); })
                ->where('tipo', 'caja')
                ->where('nomenclatura', 'like', 'IN%')
                ->sum('monto');

            $egresosAnterioresCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                ->whereHas('cuentas', function ($q) { $q->where('id_proyectos', 1); })
                ->where('tipo', 'caja')
                ->where('nomenclatura', 'like', 'EG%')
                ->sum('monto');

            $saldoInicialBancos = $ingresosAnterioresBancos - $egresosAnterioresBancos;
            $saldoInicialCaja = $ingresosAnterioresCaja - $egresosAnterioresCaja;
            $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

            // Consultar todas las cuentas de egreso de Capilla y sumar EGRESOS por cuenta en el período
            $cuentasEgreso = cuentas::where('id_clasificacion', '2')
                ->where('id_proyectos', 1)
                ->whereNotIn('cuenta', ['Traslado a Bancos desde Caja', 'Traslado a Caja desde Bancos'])
                ->get();

            // Debug: log fechas y cantidad de movimientos EG en el periodo
            try {
                Log::debug('getReporteEstadoResultadosCA period', [
                    'fechaInicial' => $fechaInicial ? $fechaInicial->toDateString() : null,
                    'fechaFinal' => $fechaFinal ? $fechaFinal->toDateString() : null,
                ]);

                $countEg = ingresos_egresos::whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->whereHas('cuentas', function ($q) { $q->where('id_proyectos', 1); })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->count();

                Log::debug('getReporteEstadoResultadosCA egresos_count', ['count' => $countEg]);

                // Log sample sums for first 8 accounts to inspect
                foreach ($cuentasEgreso->take(8) as $c) {
                    $sumCaja = (float) ingresos_egresos::where('id_cuentas', $c->id_cuentas)
                        ->where('tipo', 'caja')
                        ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                        ->where('nomenclatura', 'like', 'EG%')
                        ->sum('monto');
                    $sumBancos = (float) ingresos_egresos::where('id_cuentas', $c->id_cuentas)
                        ->where('tipo', 'bancos')
                        ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                        ->where('nomenclatura', 'like', 'EG%')
                        ->sum('monto');

                    Log::debug('getReporteEstadoResultadosCA sample_account', [
                        'cuenta_id' => $c->id_cuentas,
                        'cuenta' => $c->cuenta,
                        'sumCaja' => $sumCaja,
                        'sumBancos' => $sumBancos,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::debug('getReporteEstadoResultadosCA debug_error', ['msg' => $e->getMessage()]);
            }

            $dataGroupedCaja = $cuentasEgreso->map(function ($cuenta) use ($fechaInicial, $fechaFinal) {
                $egresos = ingresos_egresos::where('id_cuentas', $cuenta->id_cuentas)
                    ->where('tipo', 'caja')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                return [
                    'cuenta' => $cuenta->cuenta,
                    'egresos' => number_format($egresos, 1)
                ];
            })->values();

            $dataGroupedBancos = $cuentasEgreso->map(function ($cuenta) use ($fechaInicial, $fechaFinal) {
                $egresos = ingresos_egresos::where('id_cuentas', $cuenta->id_cuentas)
                    ->where('tipo', 'bancos')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                return [
                    'cuenta' => $cuenta->cuenta,
                    'egresos' => number_format($egresos, 1)
                ];
            })->values();

            $totalEgresosCaja = $dataGroupedCaja->sum(function ($item) {
                return floatval(str_replace(',', '', $item['egresos']));
            });

            $totalEgresosBancos = $dataGroupedBancos->sum(function ($item) {
                return floatval(str_replace(',', '', $item['egresos']));
            });
            $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

            // Calcular saldo final restando solo EGRESOS (estado de resultados centrado en egresos)
            $saldoFinal = ($saldoInicial) - $totalGeneralEgresos;

            return response()->json([
                'fecha_anterior' => $fechaAnterior,
                'mes' => $mes,
                'fecha_inicial' => $fechaInicial->toDateString(),
                'fecha_final' => $fechaFinal->toDateString(),
                'saldo_inicial_bancos' => $saldoInicialBancos,
                'saldo_inicial_caja' => $saldoInicialCaja,
                'saldo_inicial' => $saldoInicial,
                'total_egresos_caja' => $totalEgresosCaja,
                'total_egresos_bancos' => $totalEgresosBancos,
                'total_general_egresos' => $totalGeneralEgresos,
                'data_caja' => $dataGroupedCaja,
                'data_bancos' => $dataGroupedBancos,
                'total_saldo_final' => $saldoFinal
            ], 200);

        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // Método Get con nombres de cuentas
    public function getWithCuentas()
    {
        try {
            $data = ingresos_egresos::with('cuentas')->get()->map(function ($ingreso_egreso) {
                return [
                    'id_ingresos_egresos' => $ingreso_egreso->id_ingresos_egresos,
                    'nomenclatura' => $ingreso_egreso->nomenclatura,
                    'fecha' => $ingreso_egreso->fecha,
                    'identificacion' => $ingreso_egreso->identificacion,
                    'nombre' => $ingreso_egreso->nombre,
                    'descripcion' => $ingreso_egreso->descripcion,
                    'monto' => $ingreso_egreso->monto,
                    'tipo' => $ingreso_egreso->tipo,
                    'cuenta' => $ingreso_egreso->cuentas->cuenta
                ];
            });
            
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
    // Método create con nombres de cuentas
    public function create(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'fecha' => 'required|date',
            'identificacion' => 'required',
            'nombre' => 'required',
            'descripcion' => 'required',
            'monto' => 'required|numeric',
            'tipo' => 'required',
            'cuenta' => 'required'
        ]);

        try {
            // Buscar la cuenta por su nombre
            $cuenta = cuentas::where('cuenta', $request->input('cuenta'))->first();

            // Si la cuenta no se encuentra, devolver un error
            if (!$cuenta) {
                return response()->json(['error' => 'La cuenta proporcionada no existe'], 404);
            }

            // Crear un nuevo registro en la tabla ingresos_egresos
            $ingreso_egreso = new ingresos_egresos();
            $ingreso_egreso->fecha = $request->input('fecha');
            $ingreso_egreso->identificacion = $request->input('identificacion');
            $ingreso_egreso->nombre = $request->input('nombre');
            $ingreso_egreso->descripcion = $request->input('descripcion');
            $ingreso_egreso->monto = $request->input('monto');
            $ingreso_egreso->tipo = $request->input('tipo');
            $ingreso_egreso->id_cuentas = $cuenta->id_cuentas;
            $ingreso_egreso->save();

            return response()->json($ingreso_egreso, 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function getAllCuentasEgreso(){
        try {
            $data = cuentas::where('id_clasificacion', '2')->get();
            if ($data->isEmpty()) {
                return response()->json(['error' => 'No se encontraron cuentas de tipo Egreso'], 404);
            }
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function getAllCuentasIngreso(){
        try {
            $data = cuentas::where('id_clasificacion', '1')->get();
            if ($data->isEmpty()) {
                return response()->json(['error' => 'No se encontraron cuentas de tipo Egreso'], 404);
            }
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // get de cuentas de ingresos de proyecto agrícola
    public function getAllCuentasIngresoAG() {
        try {
            $data = cuentas::where('id_clasificacion', '1')
                ->where('id_proyectos', '1')
                ->whereNotIn('cuenta', ['Traslado a Bancos desde Caja', 'Traslado a Caja desde Bancos'])
                ->get();
            if ($data->isEmpty()) {
                return response()->json(['error' => 'No se encontraron cuentas de tipo Egreso'], 404);
            }
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }       

    // get de cuentas de egresos de proyecto agrícola
    public function getAllCuentasEgresoAG(){
        try {
            $data = cuentas::where('id_clasificacion', '2')
            ->where('id_proyectos', '1')
            ->whereNotIn('cuenta', ['Traslado a Bancos desde Caja', 'Traslado a Caja desde Bancos'])
            ->get();
            if ($data->isEmpty()) {
                return response()->json(['error' => 'No se encontraron cuentas de tipo Egreso'], 404);
            }
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // get de cuentas de ingresos de proyecto capilla
    public function getAllCuentasIngresoCA(){
        try {
            $data = cuentas::where('id_clasificacion', '1')
            ->where('id_proyectos', '2')
            ->whereNotIn('cuenta', ['Traslado a Bancos desde Caja', 'Traslado a Caja desde Bancos'])
            ->get();
            if ($data->isEmpty()) {
                return response()->json(['error' => 'No se encontraron cuentas de tipo Egreso'], 404);
            }
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // get de cuentas de egresos de proyecto capilla
    public function getAllCuentasEgresoCA(){
        try {
            $data = cuentas::where('id_clasificacion', '2')
            ->where('id_proyectos', '2')
            ->whereNotIn('cuenta', ['Traslado a Bancos desde Caja', 'Traslado a Caja desde Bancos'])
            ->get();
            if ($data->isEmpty()) {
                return response()->json(['error' => 'No se encontraron cuentas de tipo Egreso'], 404);
            }
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function getByNombreBanco(){
        try{
            $data = cuentas_bancarias::get();
            return response()->json($data, 200);
            } catch (\Throwable $th){
                return response()->json(['error' => $th ->getMessage()],500);
            }
       }
    

    // Método update con nombres de cuentas
    public function update(Request $request, $nomenclatura)
    {
        try {
            // Buscar el ingreso/egreso por su nomenclatura
            $ingreso_egreso = ingresos_egresos::where('nomenclatura', $nomenclatura)->first();
            
            // Verificar si el ingreso/egreso existe
            if (!$ingreso_egreso) {
                return response()->json(['error' => 'El ingreso/egreso no existe'], 404);
            }

            // Actualizar solo los campos que se hayan enviado en la solicitud
            if ($request->has('fecha')) {
                $ingreso_egreso->fecha = $request->input('fecha');
            }

            if ($request->has('identificacion')) {
                $ingreso_egreso->identificacion = $request->input('identificacion');
            }

            if ($request->has('descripcion')) {
                $ingreso_egreso->descripcion = $request->input('descripcion');
            }

            if ($request->has('monto')) {
                $ingreso_egreso->monto = $request->input('monto');
            }

            if ($request->has('tipo')) {
                $ingreso_egreso->tipo = $request->input('tipo');
            }

            // Buscar la cuenta por su nombre si se proporciona
            if ($request->has('cuenta')) {
                $cuenta = cuentas::where('cuenta', $request->input('cuenta'))->first();

                // Si la cuenta no se encuentra, devolver un error
                if (!$cuenta) {
                    return response()->json(['error' => 'La cuenta proporcionada no existe'], 404);
                }
                $ingreso_egreso->id_cuentas = $cuenta->id_cuentas;
            }

            // Guardar los cambios
            $ingreso_egreso->save();

            // Obtener el ingreso/egreso actualizado
            $updatedIngresoEgreso = ingresos_egresos::find($ingreso_egreso->id_ingresos_egresos);

            return response()->json($updatedIngresoEgreso, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }



    // INGRESOS Y EGRESOS DE BANCOS

    // Método get
    public function getDatosIngresoBancos(){
        try{
            $data = datos_de_pago_ingresos::get();
            return response()->json($data, 200);
        } catch (\Throwable $th){
            return response()->json(['error' => $th ->getMessage()],500);
        }
    }

    public function getWithDatosINGB()
    {
        try {
            $data = datos_de_pago_ingresos::with(['cuentas_bancarias.bancos', 'ingresos_egresos'])->get();

            // Mapear los resultados para formatear la respuesta
            $formattedData = $data->map(function ($ingreso) {
                return [
                    'id_datos_de_pago_ingresos' => $ingreso->id_datos_de_pago_ingresos,
                    'documento' => $ingreso->documento,
                    'numero_documento' => $ingreso->numero_documento,
                    'fecha_emision' => $ingreso->fecha_emision,
                    'cuenta_bancaria' => $ingreso->cuentas_bancarias->numero_cuenta,
                    'banco' => $ingreso->cuentas_bancarias->bancos->banco, // Asumiendo que el nombre del banco está en el atributo "nombre"
                    'ingresos_egresos' => $ingreso->ingresos_egresos->cuentas->clasificacion->tipo, // Asumiendo que el tipo de ingreso/egreso está en el atributo "tipo"
                    'created_at' => $ingreso->created_at,
                    'updated_at' => $ingreso->updated_at,
                ];
            });

            return response()->json($formattedData, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
    
    // public function getWithDatosINGB()
    // {
    //     try {
    //         $data = datos_de_pago_ingresos::with(['cuentas_bancarias.bancos', 'ingresos_egresos' => function ($query) {
    //             $query->where('tipo', 'INGRESOS');
    //         }])->get();

    //         // Filtrar solo los ingresos
    //         $filteredData = $data->filter(function ($ingreso) {
    //             return $ingreso->ingresos_egresos->isNotEmpty();
    //         });

    //         // Mapear los resultados para formatear la respuesta
    //         $formattedData = $filteredData->map(function ($ingreso) {
    //             return [
    //                 'id_datos_de_pago_ingresos' => $ingreso->id_datos_de_pago_ingresos,
    //                 'documento' => $ingreso->documento,
    //                 'numero_documento' => $ingreso->numero_documento,
    //                 'fecha_emision' => $ingreso->fecha_emision,
    //                 'cuenta_bancaria' => $ingreso->cuentas_bancarias->numero_cuenta,
    //                 'banco' => $ingreso->cuentas_bancarias->bancos->banco,
    //                 'ingresos_egresos' => 'INGRESOS', // Aquí asumimos que solo obtendremos ingresos debido a la condición de consulta
    //                 'created_at' => $ingreso->created_at,
    //                 'updated_at' => $ingreso->updated_at,
    //             ];
    //         });

    //         return response()->json($formattedData, 200);
    //     } catch (\Throwable $th) {
    //         return response()->json(['error' => $th->getMessage()], 500);
    //     }
    // }

    public function createALLIN(Request $request) // este igual, sorry TT
    {
        try {
            // Buscar la cuenta por su nombre
            $cuenta = cuentas::where('cuenta', $request->input('cuenta'))->first();

            // Si la cuenta no se encuentra, devolver un error
            if (!$cuenta) {
                return response()->json(['error' => 'La cuenta proporcionada no existe'], 404);
            }

            // Buscar la cuenta bancaria por su número
            $cuenta_bancaria = cuentas_bancarias::where('numero_cuenta', $request->input('cuenta_bancaria'))->first();

            // Si la cuenta bancaria no se encuentra, devolver un error
            if (!$cuenta_bancaria) {
                return response()->json(['error' => 'La cuenta bancaria proporcionada no existe'], 404);
            }

            // Crear un nuevo registro en la tabla ingresos_egresos
            $ingreso_egreso = new ingresos_egresos();
            $ingreso_egreso->fecha = $request->input('fecha');
            $ingreso_egreso->identificacion = $request->input('identificacion');
            $ingreso_egreso->nombre = $request->input('nombre');
            $ingreso_egreso->descripcion = $request->input('descripcion');
            $ingreso_egreso->monto = $request->input('monto');
            $ingreso_egreso->tipo = $request->input('tipo');
            $ingreso_egreso->id_cuentas = $cuenta->id_cuentas;
            $ingreso_egreso->save(); // Guardar el ingreso/egreso primero

            // Obtener el id del ingreso/egreso recién creado
            $id_ingresos_egresos = $ingreso_egreso->id_ingresos_egresos;

            // Crear un nuevo registro en la tabla datos_de_pago_ingresos
            $datos_pago = new datos_de_pago_ingresos();
            $datos_pago->id_ingresos_egresos = $id_ingresos_egresos; // Asignar el id del ingreso/egreso
            $datos_pago->documento = $request->input('documento');
            $datos_pago->numero_documento = $request->input('numero_documento');
            $datos_pago->fecha_emision = $request->input('fecha_emision');
            // Asociar la cuenta bancaria
            $datos_pago->id_cuentas_bancarias = $cuenta_bancaria->id_cuentas_bancarias;
            // Llenar los otros campos según el request
            $datos_pago->save();

            return response()->json($ingreso_egreso, 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function createALLEG(Request $request) // este tampoco
    {
        try {
            // Buscar la cuenta por su nombre
            $cuenta = cuentas::where('cuenta', $request->input('cuenta'))->first();

            // Si la cuenta no se encuentra, devolver un error
            if (!$cuenta) {
                return response()->json(['error' => 'La cuenta proporcionada no existe'], 404);
            }

            // Buscar la cuenta bancaria por su número
            $cuenta_bancaria = cuentas_bancarias::where('numero_cuenta', $request->input('cuenta_bancaria'))->first();

            // Si la cuenta bancaria no se encuentra, devolver un error
            if (!$cuenta_bancaria) {
                return response()->json(['error' => 'La cuenta bancaria proporcionada no existe'], 404);
            }

            // Crear un nuevo registro en la tabla ingresos_egresos
            $ingreso_egreso = new ingresos_egresos();
            $ingreso_egreso->fecha = $request->input('fecha');
            $ingreso_egreso->identificacion = $request->input('identificacion');
            $ingreso_egreso->nombre = $request->input('nombre');
            $ingreso_egreso->descripcion = $request->input('descripcion');
            $ingreso_egreso->monto = $request->input('monto');
            $ingreso_egreso->tipo = $request->input('tipo');
            $ingreso_egreso->id_cuentas = $cuenta->id_cuentas;
            $ingreso_egreso->save(); // Guardar el ingreso/egreso primero

            // Obtener el id del ingreso/egreso recién creado
            $id_ingresos_egresos = $ingreso_egreso->id_ingresos_egresos;

            // Crear un nuevo registro en la tabla datos_de_pago_ingresos
            $datos_pago = new datos_de_pago_egresos();
            $datos_pago->id_ingresos_egresos = $id_ingresos_egresos; // Asignar el id del ingreso/egreso
            $datos_pago->documento = $request->input('documento');
            $datos_pago->numero_documento = $request->input('numero_documento');
            $datos_pago->fecha_emision = $request->input('fecha_emision');
            // Asociar la cuenta bancaria
            $datos_pago->id_cuentas_bancarias = $cuenta_bancaria->id_cuentas_bancarias;
            // Llenar los otros campos según el request
            $datos_pago->save();

            return response()->json($ingreso_egreso, 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // ingresos de proyecto agrícola (bancos)
    // ingresos de bancos

    public function createALLINAG(Request $request)
    {
        try {
            // Buscar la cuenta por su nombre
            $cuenta = cuentas::where('cuenta', $request->input('cuenta'))->where('id_proyectos', 1)->first();

            // Si la cuenta no se encuentra, devolver un error
            if (!$cuenta) {
                return response()->json(['error' => 'La cuenta proporcionada no existe'], 404);
            }

            // Verificar si la cuenta pertenece al id_proyectos 1
            if ($cuenta->id_proyectos != 1) {
                return response()->json(['error' => 'La cuenta no pertenece al proyecto con id 1'], 400);
            }

            // Buscar la cuenta bancaria por su número
            $cuenta_bancaria = cuentas_bancarias::where('numero_cuenta', $request->input('cuenta_bancaria'))->first();

            // Si la cuenta bancaria no se encuentra, devolver un error
            if (!$cuenta_bancaria) {
                return response()->json(['error' => 'La cuenta bancaria proporcionada no existe'], 404);
            }

            // Crear un nuevo registro en la tabla ingresos_egresos
            $ingreso_egreso = new ingresos_egresos();
            $ingreso_egreso->fecha = $request->input('fecha');
            $ingreso_egreso->identificacion = $request->input('identificacion');
            $ingreso_egreso->nombre = $request->input('nombre');
            $ingreso_egreso->descripcion = $request->input('descripcion');
            $ingreso_egreso->monto = $request->input('monto');
            $ingreso_egreso->tipo = $request->input('tipo');
            $ingreso_egreso->id_cuentas = $cuenta->id_cuentas;
            $ingreso_egreso->save(); // Guardar el ingreso/egreso primero

            // Obtener el id del ingreso/egreso recién creado
            $id_ingresos_egresos = $ingreso_egreso->id_ingresos_egresos;

            // Crear un nuevo registro en la tabla datos_de_pago_ingresos
            $datos_pago = new datos_de_pago_ingresos();
            $datos_pago->id_ingresos_egresos = $id_ingresos_egresos; // Asignar el id del ingreso/egreso
            $datos_pago->documento = $request->input('documento');
            $datos_pago->numero_documento = $request->input('numero_documento');
            $datos_pago->fecha_emision = $request->input('fecha_emision');
            // Asociar la cuenta bancaria
            $datos_pago->id_cuentas_bancarias = $cuenta_bancaria->id_cuentas_bancarias;
            // Llenar los otros campos según el request
            $datos_pago->save();

            return response()->json($ingreso_egreso, 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // egresos de proyecto agrícola (bancos)

    public function createALLEGAG(Request $request)
    {
        try {
            // Buscar la cuenta por su nombre
            $cuenta = cuentas::where('cuenta', $request->input('cuenta'))->where('id_proyectos', 1)->first();

            // Si la cuenta no se encuentra, devolver un error
            if (!$cuenta) {
                return response()->json(['error' => 'La cuenta proporcionada no existe'], 404);
            }

            // Verificar si la cuenta pertenece al id_proyectos 1
            if ($cuenta->id_proyectos != 1) {
                return response()->json(['error' => 'La cuenta no pertenece al proyecto con id 1'], 400);
            }

            // Buscar la cuenta bancaria por su número
            $cuenta_bancaria = cuentas_bancarias::where('numero_cuenta', $request->input('cuenta_bancaria'))->first();

            // Si la cuenta bancaria no se encuentra, devolver un error
            if (!$cuenta_bancaria) {
                return response()->json(['error' => 'La cuenta bancaria proporcionada no existe'], 404);
            }

            // Crear un nuevo registro en la tabla ingresos_egresos
            $ingreso_egreso = new ingresos_egresos();
            $ingreso_egreso->fecha = $request->input('fecha');
            $ingreso_egreso->identificacion = $request->input('identificacion');
            $ingreso_egreso->nombre = $request->input('nombre');
            $ingreso_egreso->descripcion = $request->input('descripcion');
            $ingreso_egreso->monto = $request->input('monto');
            $ingreso_egreso->tipo = $request->input('tipo');
            $ingreso_egreso->id_cuentas = $cuenta->id_cuentas;
            $ingreso_egreso->save(); // Guardar el ingreso/egreso primero

            // Obtener el id del ingreso/egreso recién creado
            $id_ingresos_egresos = $ingreso_egreso->id_ingresos_egresos;

            // Crear un nuevo registro en la tabla datos_de_pago_ingresos
            $datos_pago = new datos_de_pago_egresos();
            $datos_pago->id_ingresos_egresos = $id_ingresos_egresos; // Asignar el id del ingreso/egreso
            $datos_pago->documento = $request->input('documento');
            $datos_pago->numero_documento = $request->input('numero_documento');
            $datos_pago->fecha_emision = $request->input('fecha_emision');
            // Asociar la cuenta bancaria
            $datos_pago->id_cuentas_bancarias = $cuenta_bancaria->id_cuentas_bancarias;
            // Llenar los otros campos según el request
            $datos_pago->save();

            return response()->json($ingreso_egreso, 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

     // ingresos de proyecto capilla (bancos)

     public function createALLINCA(Request $request)
     {
         try {
             // Buscar la cuenta por su nombre
             $cuenta = cuentas::where('cuenta', $request->input('cuenta'))->where('id_proyectos', 2)->first();
 
             // Si la cuenta no se encuentra, devolver un error
             if (!$cuenta) {
                 return response()->json(['error' => 'La cuenta proporcionada no existe'], 404);
             }
 
             // Verificar si la cuenta pertenece al id_proyectos 2
             if ($cuenta->id_proyectos != 2) {
                 return response()->json(['error' => 'La cuenta no pertenece al proyecto con id 2'], 400);
             }
 
             // Buscar la cuenta bancaria por su número
             $cuenta_bancaria = cuentas_bancarias::where('numero_cuenta', $request->input('cuenta_bancaria'))->first();
 
             // Si la cuenta bancaria no se encuentra, devolver un error
             if (!$cuenta_bancaria) {
                 return response()->json(['error' => 'La cuenta bancaria proporcionada no existe'], 404);
             }
 
             // Crear un nuevo registro en la tabla ingresos_egresos
             $ingreso_egreso = new ingresos_egresos();
             $ingreso_egreso->fecha = $request->input('fecha');
             $ingreso_egreso->identificacion = $request->input('identificacion');
             $ingreso_egreso->nombre = $request->input('nombre');
             $ingreso_egreso->descripcion = $request->input('descripcion');
             $ingreso_egreso->monto = $request->input('monto');
             $ingreso_egreso->tipo = $request->input('tipo');
             $ingreso_egreso->id_cuentas = $cuenta->id_cuentas;
             $ingreso_egreso->save(); // Guardar el ingreso/egreso primero
 
             // Obtener el id del ingreso/egreso recién creado
             $id_ingresos_egresos = $ingreso_egreso->id_ingresos_egresos;
 
             // Crear un nuevo registro en la tabla datos_de_pago_ingresos
             $datos_pago = new datos_de_pago_ingresos();
             $datos_pago->id_ingresos_egresos = $id_ingresos_egresos; // Asignar el id del ingreso/egreso
             $datos_pago->documento = $request->input('documento');
             $datos_pago->numero_documento = $request->input('numero_documento');
             $datos_pago->fecha_emision = $request->input('fecha_emision');
             // Asociar la cuenta bancaria
             $datos_pago->id_cuentas_bancarias = $cuenta_bancaria->id_cuentas_bancarias;
             // Llenar los otros campos según el request
             $datos_pago->save();
 
             return response()->json($ingreso_egreso, 201);
         } catch (\Throwable $th) {
             return response()->json(['error' => $th->getMessage()], 500);
         }
     }
 
     // egresos de proyecto capilla (bancos)
 
     public function createALLEGCA(Request $request)
     {
         try {
             // Buscar la cuenta por su nombre
             $cuenta = cuentas::where('cuenta', $request->input('cuenta'))->where('id_proyectos', 2)->first();
 
             // Si la cuenta no se encuentra, devolver un error
             if (!$cuenta) {
                 return response()->json(['error' => 'La cuenta proporcionada no existe'], 404);
             }
 
             // Verificar si la cuenta pertenece al id_proyectos 2
             if ($cuenta->id_proyectos != 2) {
                 return response()->json(['error' => 'La cuenta no pertenece al proyecto con id 2'], 400);
             }
 
             // Buscar la cuenta bancaria por su número
             $cuenta_bancaria = cuentas_bancarias::where('numero_cuenta', $request->input('cuenta_bancaria'))->first();
 
             // Si la cuenta bancaria no se encuentra, devolver un error
             if (!$cuenta_bancaria) {
                 return response()->json(['error' => 'La cuenta bancaria proporcionada no existe'], 404);
             }
 
             // Crear un nuevo registro en la tabla ingresos_egresos
             $ingreso_egreso = new ingresos_egresos();
             $ingreso_egreso->fecha = $request->input('fecha');
             $ingreso_egreso->identificacion = $request->input('identificacion');
             $ingreso_egreso->nombre = $request->input('nombre');
             $ingreso_egreso->descripcion = $request->input('descripcion');
             $ingreso_egreso->monto = $request->input('monto');
             $ingreso_egreso->tipo = $request->input('tipo');
             $ingreso_egreso->id_cuentas = $cuenta->id_cuentas;
             $ingreso_egreso->save(); // Guardar el ingreso/egreso primero
 
             // Obtener el id del ingreso/egreso recién creado
             $id_ingresos_egresos = $ingreso_egreso->id_ingresos_egresos;
 
             // Crear un nuevo registro en la tabla datos_de_pago_ingresos
             $datos_pago = new datos_de_pago_egresos();
             $datos_pago->id_ingresos_egresos = $id_ingresos_egresos; // Asignar el id del ingreso/egreso
             $datos_pago->documento = $request->input('documento');
             $datos_pago->numero_documento = $request->input('numero_documento');
             $datos_pago->fecha_emision = $request->input('fecha_emision');
             // Asociar la cuenta bancaria
             $datos_pago->id_cuentas_bancarias = $cuenta_bancaria->id_cuentas_bancarias;
             // Llenar los otros campos según el request
             $datos_pago->save();
 
             return response()->json($ingreso_egreso, 201);
         } catch (\Throwable $th) {
             return response()->json(['error' => $th->getMessage()], 500);
         }
     }
    
    public function createALLINEGCaja(Request $request) // la neta no recuerdo de que era este
    {
        // Validar los datos de entrada
        $request->validate([
            'fecha' => 'required|date',
            'identificacion' => 'required',
            'nombre' => 'required',
            'descripcion' => 'required',
            'monto' => 'required|numeric',
            'tipo' => 'required',
            'cuenta' => 'required',
        ]);

        try {
            // Buscar la cuenta por su nombre
            $cuenta = cuentas::where('cuenta', $request->input('cuenta'))->first();

            // Si la cuenta no se encuentra, devolver un error
            if (!$cuenta) {
                return response()->json(['error' => 'La cuenta proporcionada no existe'], 404);
            }

            // Crear un nuevo registro en la tabla ingresos_egresos
            $ingreso_egreso = new ingresos_egresos();
            $ingreso_egreso->fecha = $request->input('fecha');
            $ingreso_egreso->identificacion = $request->input('identificacion');
            $ingreso_egreso->nombre = $request->input('nombre');
            $ingreso_egreso->descripcion = $request->input('descripcion');
            $ingreso_egreso->monto = $request->input('monto');
            $ingreso_egreso->tipo = $request->input('tipo');
            $ingreso_egreso->id_cuentas = $cuenta->id_cuentas;
            $ingreso_egreso->save(); // Guardar el ingreso/egreso primero

            return response()->json($ingreso_egreso, 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // ingreso o egreso de caja agricola
    public function createALLINEGCajaAG(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'fecha' => 'required|date',
            'identificacion' => 'required',
            'nombre' => 'required',
            'descripcion' => 'required',
            'monto' => 'required|numeric',
            'tipo' => 'required',
            'cuenta' => 'required',
        ]);

        try {
            // Buscar la cuenta por su nombre
            $cuenta = cuentas::where('cuenta', $request->input('cuenta'))->where('id_proyectos', 1)->first();

            // Si la cuenta no se encuentra, devolver un error
            if (!$cuenta) {
                return response()->json(['error' => 'La cuenta proporcionada no existe'], 404);
            }

            // Verificar si la cuenta pertenece al id_proyectos 1
             if ($cuenta->id_proyectos != 1) {
                 return response()->json(['error' => 'La cuenta no pertenece al proyecto con id 1'], 400);
             }

            // Crear un nuevo registro en la tabla ingresos_egresos
            $ingreso_egreso = new ingresos_egresos();
            $ingreso_egreso->fecha = $request->input('fecha');
            $ingreso_egreso->identificacion = $request->input('identificacion');
            $ingreso_egreso->nombre = $request->input('nombre');
            $ingreso_egreso->descripcion = $request->input('descripcion');
            $ingreso_egreso->monto = $request->input('monto');
            $ingreso_egreso->tipo = $request->input('tipo');
            $ingreso_egreso->id_cuentas = $cuenta->id_cuentas;
            $ingreso_egreso->save(); // Guardar el ingreso/egreso primero

            return response()->json($ingreso_egreso, 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // ingreso o egreso de caja capilla
    public function createALLINEGCajaCA(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'fecha' => 'required|date',
            'identificacion' => 'required',
            'nombre' => 'required',
            'descripcion' => 'required',
            'monto' => 'required|numeric',
            'tipo' => 'required',
            'cuenta' => 'required',
        ]);

        try {
            // Buscar la cuenta por su nombre
            $cuenta = cuentas::where('cuenta', $request->input('cuenta'))->where('id_proyectos', 2)->first();

            // Si la cuenta no se encuentra, devolver un error
            if (!$cuenta) {
                return response()->json(['error' => 'La cuenta proporcionada no existe'], 404);
            }

            // Verificar si la cuenta pertenece al id_proyectos 2
             if ($cuenta->id_proyectos != 2) {
                 return response()->json(['error' => 'La cuenta no pertenece al proyecto con id 2'], 400);
             }

            // Crear un nuevo registro en la tabla ingresos_egresos
            $ingreso_egreso = new ingresos_egresos();
            $ingreso_egreso->fecha = $request->input('fecha');
            $ingreso_egreso->identificacion = $request->input('identificacion');
            $ingreso_egreso->nombre = $request->input('nombre');
            $ingreso_egreso->descripcion = $request->input('descripcion');
            $ingreso_egreso->monto = $request->input('monto');
            $ingreso_egreso->tipo = $request->input('tipo');
            $ingreso_egreso->id_cuentas = $cuenta->id_cuentas;
            $ingreso_egreso->save(); // Guardar el ingreso/egreso primero

            return response()->json($ingreso_egreso, 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // TRASLADOS INTERNOS

    // traslado de Depósitos de Caja AGRÍCOLA

    public function createTrasDepCajaAG(Request $request)
    {
        try {
            // ingreso de bancos

            // Buscar la cuenta por su nombre
            $cuenta = cuentas::where('cuenta', 'Traslado a Bancos desde Caja')->where('id_clasificacion', '1')->where('id_proyectos', '1')->first(); // se ingresa automático

            // Si la cuenta no se encuentra, devolver un error
            if (!$cuenta) {
                return response()->json(['error' => 'La cuenta proporcionada no existe'], 404);
            }

            // Buscar la cuenta bancaria por su número
            $cuenta_bancaria = cuentas_bancarias::where('numero_cuenta', $request->input('cuenta_bancaria'))->first(); // ingreso manual

            // Si la cuenta bancaria no se encuentra, devolver un error
            if (!$cuenta_bancaria) {
                return response()->json(['error' => 'La cuenta bancaria proporcionada no existe'], 404);
            }

            // Crear un nuevo registro en la tabla ingresos_egresos
            $ingreso_egreso = new ingresos_egresos();
            $ingreso_egreso->fecha = $request->input('fecha'); // ingreso manual
            $ingreso_egreso->identificacion = 'Traslado Interno';
            $ingreso_egreso->nombre = 'Depósito de Caja';
            $ingreso_egreso->descripcion = $request->input('descripcion'); // ingreso manual
            $ingreso_egreso->monto = $request->input('monto'); // ingreso manual
            $ingreso_egreso->tipo = 'bancos';
            $ingreso_egreso->id_cuentas = $cuenta->id_cuentas;
            $ingreso_egreso->save(); // Guardar el ingreso/egreso primero

            // Obtener el id del ingreso/egreso recién creado
            $id_ingresos_egresos = $ingreso_egreso->id_ingresos_egresos;

            // Crear un nuevo registro en la tabla datos_de_pago_ingresos
            $datos_pago = new datos_de_pago_ingresos();
            $datos_pago->id_ingresos_egresos = $id_ingresos_egresos; // Asignar el id del ingreso/egreso
            $datos_pago->documento = 'Depósito'; // ingreso automático
            $datos_pago->numero_documento = $request->input('numero_documento'); // ingreso manual
            $datos_pago->fecha_emision = $ingreso_egreso->fecha; // ingreso desde fecha
            // Asociar la cuenta bancaria
            $datos_pago->id_cuentas_bancarias = $cuenta_bancaria->id_cuentas_bancarias;
            // Llenar los otros campos según el request
            $datos_pago->save();


            // egreso de caja
            // Crear un nuevo registro en la tabla ingresos_egresos

            // Buscar la cuenta por su nombre
            $cuentaEGRESO = cuentas::where('cuenta', 'Traslado a Bancos desde Caja')->where('id_clasificacion', '2')->where('id_proyectos', '1')->first();

            // Si la cuenta no se encuentra, devolver un error
            if (!$cuentaEGRESO) {
                return response()->json(['error' => 'La cuenta proporcionada no existe'], 404);
            }

            $egreso = new ingresos_egresos();
            $egreso->fecha = $ingreso_egreso->fecha;
            $egreso->identificacion = 'Traslado Interno';
            $egreso->nombre = 'Depósito de Caja';
            $egreso->descripcion = $ingreso_egreso->descripcion;
            $egreso->monto = $ingreso_egreso->monto;
            $egreso->tipo = 'caja';
            $egreso->id_cuentas = $cuentaEGRESO->id_cuentas;
            $egreso->save(); // Guardar el ingreso/egreso primero

            return response()->json($egreso, 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // traslado de Retiro de Bancos AGRÍCOLA

    public function createTrasRetBanAG(Request $request)
    {
        try {
            // egreso de bancos

            // Buscar la cuenta por su nombre
            $cuenta = cuentas::where('cuenta', 'Traslado a Caja desde Bancos')->where('id_clasificacion', '2')->where('id_proyectos', '1')->first(); // se ingresa automático

            // Si la cuenta no se encuentra, devolver un error
            if (!$cuenta) {
                return response()->json(['error' => 'La cuenta proporcionada no existe'], 404);
            }

            // Buscar la cuenta bancaria por su número
            $cuenta_bancaria = cuentas_bancarias::where('numero_cuenta', $request->input('cuenta_bancaria'))->first(); // ingreso manual

            // Si la cuenta bancaria no se encuentra, devolver un error
            if (!$cuenta_bancaria) {
                return response()->json(['error' => 'La cuenta bancaria proporcionada no existe'], 404);
            }

            // Crear un nuevo registro en la tabla ingresos_egresos
            $ingreso_egreso = new ingresos_egresos();
            $ingreso_egreso->fecha = $request->input('fecha'); // ingreso manual
            $ingreso_egreso->identificacion = 'Traslado Interno';
            $ingreso_egreso->nombre = 'Retiro de Bancos';
            $ingreso_egreso->descripcion = $request->input('descripcion'); // ingreso manual
            $ingreso_egreso->monto = $request->input('monto'); // ingreso manual
            $ingreso_egreso->tipo = 'bancos';
            $ingreso_egreso->id_cuentas = $cuenta->id_cuentas;
            $ingreso_egreso->save(); // Guardar el ingreso/egreso primero

            // Obtener el id del ingreso/egreso recién creado
            $id_ingresos_egresos = $ingreso_egreso->id_ingresos_egresos;

            // Crear un nuevo registro en la tabla datos_de_pago_ingresos
            $datos_pago = new datos_de_pago_egresos();
            $datos_pago->id_ingresos_egresos = $id_ingresos_egresos; // Asignar el id del ingreso/egreso
            $datos_pago->documento = 'Retiro'; // ingreso automático
            $datos_pago->numero_documento = $request->input('numero_documento'); // ingreso manual
            $datos_pago->fecha_emision = $ingreso_egreso->fecha; // ingreso desde fecha
            // Asociar la cuenta bancaria
            $datos_pago->id_cuentas_bancarias = $cuenta_bancaria->id_cuentas_bancarias;
            // Llenar los otros campos según el request
            $datos_pago->save();


            // ingreso de caja
            // Crear un nuevo registro en la tabla ingresos_egresos

            // Buscar la cuenta por su nombre
            $cuentaINGRESO = cuentas::where('cuenta', 'Traslado a Caja desde Bancos')->where('id_clasificacion', '1')->where('id_proyectos', '1')->first();

            // Si la cuenta no se encuentra, devolver un error
            if (!$cuentaINGRESO) {
                return response()->json(['error' => 'La cuenta proporcionada no existe'], 404);
            }

            $ingreso = new ingresos_egresos();
            $ingreso->fecha = $ingreso_egreso->fecha;
            $ingreso->identificacion = 'Traslado Interno';
            $ingreso->nombre = 'Retiro de Bancos';
            $ingreso->descripcion = $ingreso_egreso->descripcion;
            $ingreso->monto = $ingreso_egreso->monto;
            $ingreso->tipo = 'caja';
            $ingreso->id_cuentas = $cuentaINGRESO->id_cuentas;
            $ingreso->save(); // Guardar el ingreso/egreso primero
          
            return response()->json($ingreso, 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // traslado de Depósitos de Caja CAPILLA

    public function createTrasDepCajaCA(Request $request)
    {
        try {
            // ingreso de bancos

            // Buscar la cuenta por su nombre
            $cuenta = cuentas::where('cuenta', 'Traslado a Bancos desde Caja')->where('id_clasificacion', '1')->where('id_proyectos', '2')->first(); // se ingresa automático

            // Si la cuenta no se encuentra, devolver un error
            if (!$cuenta) {
                return response()->json(['error' => 'La cuenta proporcionada no existe'], 404);
            }

            // Buscar la cuenta bancaria por su número
            $cuenta_bancaria = cuentas_bancarias::where('numero_cuenta', $request->input('cuenta_bancaria'))->first(); // ingreso manual

            // Si la cuenta bancaria no se encuentra, devolver un error
            if (!$cuenta_bancaria) {
                return response()->json(['error' => 'La cuenta bancaria proporcionada no existe'], 404);
            }

            // Crear un nuevo registro en la tabla ingresos_egresos
            $ingreso_egreso = new ingresos_egresos();
            $ingreso_egreso->fecha = $request->input('fecha'); // ingreso manual
            $ingreso_egreso->identificacion = 'Traslado Interno';
            $ingreso_egreso->nombre = 'Depósito de Caja';
            $ingreso_egreso->descripcion = $request->input('descripcion'); // ingreso manual
            $ingreso_egreso->monto = $request->input('monto'); // ingreso manual
            $ingreso_egreso->tipo = 'bancos';
            $ingreso_egreso->id_cuentas = $cuenta->id_cuentas;
            $ingreso_egreso->save(); // Guardar el ingreso/egreso primero

            // Obtener el id del ingreso/egreso recién creado
            $id_ingresos_egresos = $ingreso_egreso->id_ingresos_egresos;

            // Crear un nuevo registro en la tabla datos_de_pago_ingresos
            $datos_pago = new datos_de_pago_ingresos();
            $datos_pago->id_ingresos_egresos = $id_ingresos_egresos; // Asignar el id del ingreso/egreso
            $datos_pago->documento = 'Depósito'; // ingreso automático
            $datos_pago->numero_documento = $request->input('numero_documento'); // ingreso manual
            $datos_pago->fecha_emision = $ingreso_egreso->fecha; // ingreso desde fecha
            // Asociar la cuenta bancaria
            $datos_pago->id_cuentas_bancarias = $cuenta_bancaria->id_cuentas_bancarias;
            // Llenar los otros campos según el request
            $datos_pago->save();


            // egreso de caja
            // Crear un nuevo registro en la tabla ingresos_egresos

            // Buscar la cuenta por su nombre
            $cuentaEGRESO = cuentas::where('cuenta', 'Traslado a Bancos desde Caja')->where('id_clasificacion', '2')->where('id_proyectos', '2')->first();

            // Si la cuenta no se encuentra, devolver un error
            if (!$cuentaEGRESO) {
                return response()->json(['error' => 'La cuenta proporcionada no existe'], 404);
            }

            $egreso = new ingresos_egresos();
            $egreso->fecha = $ingreso_egreso->fecha;
            $egreso->identificacion = 'Traslado Interno';
            $egreso->nombre = 'Depósito de Caja';
            $egreso->descripcion = $ingreso_egreso->descripcion;
            $egreso->monto = $ingreso_egreso->monto;
            $egreso->tipo = 'caja';
            $egreso->id_cuentas = $cuentaEGRESO->id_cuentas;
            $egreso->save(); // Guardar el ingreso/egreso primero

            return response()->json($egreso, 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // traslado de Retiro de Bancos CAPILLA

    public function createTrasRetBanCA(Request $request)
    {
        try {
            // egreso de bancos

            // Buscar la cuenta por su nombre
            $cuenta = cuentas::where('cuenta', 'Traslado a Caja desde Bancos')->where('id_clasificacion', '2')->where('id_proyectos', '2')->first(); // se ingresa automático

            // Si la cuenta no se encuentra, devolver un error
            if (!$cuenta) {
                return response()->json(['error' => 'La cuenta proporcionada no existe'], 404);
            }

            // Buscar la cuenta bancaria por su número
            $cuenta_bancaria = cuentas_bancarias::where('numero_cuenta', $request->input('cuenta_bancaria'))->first(); // ingreso manual

            // Si la cuenta bancaria no se encuentra, devolver un error
            if (!$cuenta_bancaria) {
                return response()->json(['error' => 'La cuenta bancaria proporcionada no existe'], 404);
            }

            // Crear un nuevo registro en la tabla ingresos_egresos
            $ingreso_egreso = new ingresos_egresos();
            $ingreso_egreso->fecha = $request->input('fecha'); // ingreso manual
            $ingreso_egreso->identificacion = 'Traslado Interno';
            $ingreso_egreso->nombre = 'Retiro de Bancos';
            $ingreso_egreso->descripcion = $request->input('descripcion'); // ingreso manual
            $ingreso_egreso->monto = $request->input('monto'); // ingreso manual
            $ingreso_egreso->tipo = 'bancos';
            $ingreso_egreso->id_cuentas = $cuenta->id_cuentas;
            $ingreso_egreso->save(); // Guardar el ingreso/egreso primero

            // Obtener el id del ingreso/egreso recién creado
            $id_ingresos_egresos = $ingreso_egreso->id_ingresos_egresos;

            // Crear un nuevo registro en la tabla datos_de_pago_ingresos
            $datos_pago = new datos_de_pago_egresos();
            $datos_pago->id_ingresos_egresos = $id_ingresos_egresos; // Asignar el id del ingreso/egreso
            $datos_pago->documento = 'Retiro'; // ingreso automático
            $datos_pago->numero_documento = $request->input('numero_documento'); // ingreso manual
            $datos_pago->fecha_emision = $ingreso_egreso->fecha; // ingreso desde fecha
            // Asociar la cuenta bancaria
            $datos_pago->id_cuentas_bancarias = $cuenta_bancaria->id_cuentas_bancarias;
            // Llenar los otros campos según el request
            $datos_pago->save();


            // ingreso de caja
            // Crear un nuevo registro en la tabla ingresos_egresos

            // Buscar la cuenta por su nombre
            $cuentaINGRESO = cuentas::where('cuenta', 'Traslado a Caja desde Bancos')->where('id_clasificacion', '1')->where('id_proyectos', '2')->first();

            // Si la cuenta no se encuentra, devolver un error
            if (!$cuentaINGRESO) {
                return response()->json(['error' => 'La cuenta proporcionada no existe'], 404);
            }

            $ingreso = new ingresos_egresos();
            $ingreso->fecha = $ingreso_egreso->fecha;
            $ingreso->identificacion = 'Traslado Interno';
            $ingreso->nombre = 'Retiro de Bancos';
            $ingreso->descripcion = $ingreso_egreso->descripcion;
            $ingreso->monto = $ingreso_egreso->monto;
            $ingreso->tipo = 'caja';
            $ingreso->id_cuentas = $cuentaINGRESO->id_cuentas;
            $ingreso->save(); // Guardar el ingreso/egreso primero
          
            return response()->json($ingreso, 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }


    // Informe Libro Caja Agricola
    public function getWithCuentasByDate(Request $request)
    {
        try {
            // Validar las fechas de entrada
            $request->validate([
                'fechaInicial' => 'required|date',
                'fechaFinal' => 'required|date'
            ]);

            // Obtener las fechas del request
            $fechaInicial = $request->input('fechaInicial');
            $fechaFinal = $request->input('fechaFinal');

            // Calcular la fecha anterior a la fechaInicial
            $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));

            // Obtener el saldo inicial hasta la fecha anterior
            $ingresosAnteriores = ingresos_egresos::whereHas('cuentas', function ($query) {
                    $query->where('id_proyectos', 1);
                })
                ->where('tipo', 'caja')
                ->where('fecha', '<', $fechaInicial)
                ->where('nomenclatura', 'like', 'IN%')
                ->sum('monto');

            $egresosAnteriores = ingresos_egresos::whereHas('cuentas', function ($query) {
                    $query->where('id_proyectos', 1);
                })
                ->where('tipo', 'caja')
                ->where('fecha', '<', $fechaInicial)
                ->where('nomenclatura', 'like', 'EG%')
                ->sum('monto');

            $saldoInicial = $ingresosAnteriores - $egresosAnteriores;

            // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
            $data = ingresos_egresos::with('cuentas')
                ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                ->where('tipo', 'caja')
                ->whereHas('cuentas', function ($query) {
                    $query->where('id_proyectos', 1);
                })
                ->get()
                ->map(function ($ingreso_egreso) {
                    return [
                        'nomenclatura' => $ingreso_egreso->nomenclatura,
                        'fecha' => $ingreso_egreso->fecha,
                        'descripcion' => $ingreso_egreso->descripcion,
                        'monto' => $ingreso_egreso->monto,
                        'tipo' => $ingreso_egreso->tipo,
                        'cuenta' => $ingreso_egreso->cuentas->cuenta
                    ];
                });

            // Agregar la fila del saldo inicial al principio del array
            $data->prepend([
                'nomenclatura' => '',
                'fecha' => $fechaAnterior,
                'descripcion' => 'Saldo anterior ' . $fechaAnterior,
                'monto' => $saldoInicial,
                'tipo' => 'caja',
                'cuenta' => 'Saldo inicial',
                'acredita' => '',
                'debita' => '',
                'total' => $saldoInicial
            ]);

            // Calcular el total acumulado
            $totalAcumulado = $saldoInicial;
            $data = $data->map(function ($item) use (&$totalAcumulado) {
                $monto = floatval($item['monto']); // Convertir a número para evitar errores de tipo

                if (strpos($item['nomenclatura'], 'IN') !== false) {
                    $item['acredita'] = $monto;
                    $item['debita'] = '';
                    $totalAcumulado += $monto;
                } elseif (strpos($item['nomenclatura'], 'EG') !== false) {
                    $item['acredita'] = '';
                    $item['debita'] = $monto;
                    $totalAcumulado -= $monto;
                } else {
                    $item['acredita'] = '';
                    $item['debita'] = '';
                }
                $item['total'] = $totalAcumulado;
                return $item;
            });

            // Agregar la fila de suma total al final del array
            $data->push([
                'nomenclatura' => '',
                'fecha' => '',
                'descripcion' => '',
                'monto' => '',
                'tipo' => 'caja',
                'cuenta' => 'Suma total Caja',
                'acredita' => '',
                'debita' => '',
                'total' => $totalAcumulado
            ]);

            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    //Informe libro bancos

    // Informe Libro Caja Bancos Agricola
    public function getWithCuentasBancosByDate(Request $request)
    {
        try {
            // Validar las fechas de entrada y la cuenta bancaria
            $request->validate([
                'fechaInicial' => 'required|date',
                'fechaFinal' => 'required|date',
                'banco_y_cuenta' => 'required'
            ]);

            // Obtener las fechas del request
            $fechaInicial = $request->input('fechaInicial');
            $fechaFinal = $request->input('fechaFinal');
            $cuentaBancaria = $request->input('banco_y_cuenta');

            // Calcular la fecha anterior a la fechaInicial
            $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));

            // Obtener la cuenta bancaria
            $cuenta = cuentas_bancarias::where('numero_cuenta', $cuentaBancaria)->first();

            if (!$cuenta) {
                return response()->json(['error' => 'Cuenta bancaria no encontrada'], 404);
            }

            // Obtener los IDs de ingresos y egresos relacionados con la cuenta bancaria
            $ingresosIds = datos_de_pago_ingresos::where('id_cuentas_bancarias', $cuenta->id_cuentas_bancarias)
                ->pluck('id_ingresos_egresos')
                ->toArray();

            $egresosIds = datos_de_pago_egresos::where('id_cuentas_bancarias', $cuenta->id_cuentas_bancarias)
                ->pluck('id_ingresos_egresos')
                ->toArray();

            $ids = array_merge($ingresosIds, $egresosIds);

            // Obtener el saldo inicial hasta la fecha anterior
            $ingresosAnteriores = ingresos_egresos::where('tipo', 'bancos')
                ->whereIn('id_ingresos_egresos', $ids)
                ->whereHas('cuentas', function ($query) {
                    $query->where('id_proyectos', 1);
                })
                ->where('fecha', '<', $fechaInicial)
                ->where('nomenclatura', 'like', 'IN%')
                ->sum('monto');

            $egresosAnteriores = ingresos_egresos::where('tipo', 'bancos')
                ->whereIn('id_ingresos_egresos', $ids)
                ->whereHas('cuentas', function ($query) {
                    $query->where('id_proyectos', 1);
                })
                ->where('fecha', '<', $fechaInicial)
                ->where('nomenclatura', 'like', 'EG%')
                ->sum('monto');

            $saldoInicial = $ingresosAnteriores - $egresosAnteriores;

            // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
            $data = ingresos_egresos::with('cuentas')
                ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                ->where('tipo', 'bancos')
                ->whereIn('id_ingresos_egresos', $ids)
                ->whereHas('cuentas', function ($query) {
                    $query->where('id_proyectos', 1);
                })
                ->get()
                ->map(function ($ingreso_egreso) {
                    $cuentaBancariaIngreso = $ingreso_egreso->datos_de_pago_ingresos->first()->cuentas_bancarias->numero_cuenta ?? null;
                    $cuentaBancariaEgreso = $ingreso_egreso->datos_de_pago_egresos->first()->cuentas_bancarias->numero_cuenta ?? null;
                    $cuentaBancaria = $cuentaBancariaIngreso ?? $cuentaBancariaEgreso;

                    return [
                        'nomenclatura' => $ingreso_egreso->nomenclatura,
                        'fecha' => $ingreso_egreso->fecha,
                        'descripcion' => $ingreso_egreso->descripcion,
                        'monto' => $ingreso_egreso->monto,
                        'tipo' => $ingreso_egreso->tipo,
                        'cuenta' => $ingreso_egreso->cuentas->cuenta,
                    ];
                });

            // Agregar la fila del saldo inicial al principio del array
            $data->prepend([
                'nomenclatura' => '',
                'fecha' => $fechaAnterior,
                'descripcion' => 'Saldo anterior ' . $fechaAnterior,
                'monto' => $saldoInicial,
                'tipo' => 'bancos',
                'cuenta' => 'Saldo inicial',
                'acredita' => '',
                'debita' => '',
                'total' => $saldoInicial
            ]);

            // Calcular el total acumulado
            $totalAcumulado = $saldoInicial;
            $data = $data->map(function ($item) use (&$totalAcumulado) {
                $monto = floatval($item['monto']); // Convertir a número para evitar errores de tipo

                if (strpos($item['nomenclatura'], 'IN') !== false) {
                    $item['acredita'] = $monto;
                    $item['debita'] = '';
                    $totalAcumulado += $monto;
                } elseif (strpos($item['nomenclatura'], 'EG') !== false) {
                    $item['acredita'] = '';
                    $item['debita'] = $monto;
                    $totalAcumulado -= $monto;
                } else {
                    $item['acredita'] = '';
                    $item['debita'] = '';
                }
                $item['total'] = $totalAcumulado;
                return $item;
            });

            // Agregar la fila de suma total al final del array
            $data->push([
                'nomenclatura' => '',
                'fecha' => '',
                'descripcion' => '',
                'monto' => '',
                'tipo' => 'bancos',
                'cuenta' => 'Suma total Bancos',
                'acredita' => '',
                'debita' => '',
                'total' => $totalAcumulado
            ]);

            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

        
    //Informe Libro Diario Agricola
    // Informe Libro Diario Agricola
    public function getWithCuentasLibroDiario(Request $request)
    {
        try {
            // Validar las fechas de entrada
            $request->validate([
                'fechaInicial' => 'required|date',
                'fechaFinal' => 'required|date'
            ]);

            // Obtener las fechas del request
            $fechaInicial = $request->input('fechaInicial');
            $fechaFinal = $request->input('fechaFinal');

            // Calcular la fecha anterior a la fechaInicial
            $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));

            // Obtener el saldo inicial hasta la fecha anterior
            $ingresosAnteriores = ingresos_egresos::where('fecha', '<', $fechaInicial)
                ->whereHas('cuentas', function ($query) {
                    $query->where('id_proyectos', 1);
                })
                ->where(function ($query) {
                    $query->where('tipo', 'bancos')
                        ->orWhere('tipo', 'caja');
                })
                ->where('nomenclatura', 'like', 'IN%')
                ->sum('monto');

            $egresosAnteriores = ingresos_egresos::where('fecha', '<', $fechaInicial)
                ->whereHas('cuentas', function ($query) {
                    $query->where('id_proyectos', 1);
                })
                ->where(function ($query) {
                    $query->where('tipo', 'bancos')
                        ->orWhere('tipo', 'caja');
                })
                ->where('nomenclatura', 'like', 'EG%')
                ->sum('monto');

            $saldoInicial = $ingresosAnteriores - $egresosAnteriores;

            // Consultar los datos filtrados por fecha, tipo "bancos" o "caja" y id_proyecto
            $data = ingresos_egresos::with(['cuentas', 'datos_de_pago_ingresos', 'datos_de_pago_egresos'])
                ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                ->whereHas('cuentas', function ($query) {
                    $query->where('id_proyectos', 1);
                })
                ->where(function ($query) {
                    $query->where('tipo', 'bancos')
                        ->orWhere('tipo', 'caja');
                })
                ->get()
                ->map(function ($ingreso_egreso) {
                    $numeroDocumento = '-';
                    if ($ingreso_egreso->tipo === 'bancos') {
                        $numeroDocumento = $ingreso_egreso->datos_de_pago_ingresos->first()->numero_documento ?? $ingreso_egreso->datos_de_pago_egresos->first()->numero_documento ?? '-';
                    }

                    return [
                        'nomenclatura' => $ingreso_egreso->nomenclatura,
                        'fecha' => $ingreso_egreso->fecha,
                        'descripcion' => $ingreso_egreso->descripcion,
                        'monto' => $ingreso_egreso->monto,
                        'tipo' => $ingreso_egreso->tipo,
                        'cuenta' => $ingreso_egreso->cuentas->cuenta,
                        'numero_documento' => $numeroDocumento
                    ];
                });

            // Agregar la fila del saldo inicial al principio del array
            $data->prepend([
                'nomenclatura' => '',
                'fecha' => $fechaAnterior,
                'descripcion' => 'Saldo anterior ' . $fechaAnterior,
                'monto' => $saldoInicial,
                'tipo' => 'bancos',
                'cuenta' => 'Saldo inicial',
                'acredita' => '',
                'debita' => '',
                'total' => $saldoInicial,
                'numero_documento' => '-'
            ]);

            // Calcular el total acumulado
            $totalAcumulado = $saldoInicial;
            $data = $data->map(function ($item) use (&$totalAcumulado) {
                $monto = floatval($item['monto']); // Convertir a número para evitar errores de tipo

                if (strpos($item['nomenclatura'], 'IN') !== false) {
                    $item['acredita'] = $monto;
                    $item['debita'] = '';
                    $totalAcumulado += $monto;
                } elseif (strpos($item['nomenclatura'], 'EG') !== false) {
                    $item['acredita'] = '';
                    $item['debita'] = $monto;
                    $totalAcumulado -= $monto;
                } else {
                    $item['acredita'] = '';
                    $item['debita'] = '';
                }
                $item['total'] = $totalAcumulado;
                return $item;
            });

            // Agregar la fila de suma total al final del array
            $data->push([
                'nomenclatura' => '',
                'fecha' => '',
                'descripcion' => '',
                'monto' => '',
                'tipo' => 'bancos',
                'cuenta' => 'Suma total Bancos',
                'acredita' => '',
                'debita' => '',
                'total' => $totalAcumulado,
                'numero_documento' => '-'
            ]);

            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // informe de libro caja de capilla
    public function getWithCuentasByDateCA(Request $request)
    {
        try {
            // Validar las fechas de entrada
            $request->validate([
                'fechaInicial' => 'required|date',
                'fechaFinal' => 'required|date'
            ]);

            // Obtener las fechas del request
            $fechaInicial = $request->input('fechaInicial');
            $fechaFinal = $request->input('fechaFinal');

            // Calcular la fecha anterior a la fechaInicial
            $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));

            // Obtener el saldo inicial hasta la fecha anterior
            $ingresosAnteriores = ingresos_egresos::whereHas('cuentas', function ($query) {
                    $query->where('id_proyectos', 2); // proyecto capilla
                })
                ->where('tipo', 'caja')
                ->where('fecha', '<', $fechaInicial)
                ->where('nomenclatura', 'like', 'IN%')
                ->sum('monto');

            $egresosAnteriores = ingresos_egresos::whereHas('cuentas', function ($query) {
                    $query->where('id_proyectos', 2); // proyecto capilla
                })
                ->where('tipo', 'caja')
                ->where('fecha', '<', $fechaInicial)
                ->where('nomenclatura', 'like', 'EG%')
                ->sum('monto');

            $saldoInicial = $ingresosAnteriores - $egresosAnteriores;

            // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
            $data = ingresos_egresos::with('cuentas')
                ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                ->where('tipo', 'caja')
                ->whereHas('cuentas', function ($query) {
                    $query->where('id_proyectos', 2);
                })
                ->get()
                ->map(function ($ingreso_egreso) {
                    return [
                        'nomenclatura' => $ingreso_egreso->nomenclatura,
                        'fecha' => $ingreso_egreso->fecha,
                        'descripcion' => $ingreso_egreso->descripcion,
                        'monto' => $ingreso_egreso->monto,
                        'tipo' => $ingreso_egreso->tipo,
                        'cuenta' => $ingreso_egreso->cuentas->cuenta
                    ];
                });

            // Agregar la fila del saldo inicial al principio del array
            $data->prepend([
                'nomenclatura' => '',
                'fecha' => $fechaAnterior,
                'descripcion' => 'Saldo anterior ' . $fechaAnterior,
                'monto' => $saldoInicial,
                'tipo' => 'caja',
                'cuenta' => 'Saldo inicial',
                'acredita' => '',
                'debita' => '',
                'total' => $saldoInicial
            ]);

            // Calcular el total acumulado
            $totalAcumulado = $saldoInicial;
            $data = $data->map(function ($item) use (&$totalAcumulado) {
                $monto = floatval($item['monto']); // Convertir a número para evitar errores de tipo

                if (strpos($item['nomenclatura'], 'IN') !== false) {
                    $item['acredita'] = $monto;
                    $item['debita'] = '';
                    $totalAcumulado += $monto;
                } elseif (strpos($item['nomenclatura'], 'EG') !== false) {
                    $item['acredita'] = '';
                    $item['debita'] = $monto;
                    $totalAcumulado -= $monto;
                } else {
                    $item['acredita'] = '';
                    $item['debita'] = '';
                }
                $item['total'] = $totalAcumulado;
                return $item;
            });

            // Agregar la fila de suma total al final del array
            $data->push([
                'nomenclatura' => '',
                'fecha' => '',
                'descripcion' => '',
                'monto' => '',
                'tipo' => 'caja',
                'cuenta' => 'Suma total Caja',
                'acredita' => '',
                'debita' => '',
                'total' => $totalAcumulado
            ]);

            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // Informe Libro Caja Bancos Capilla
    public function getWithCuentasBancosByDateCA(Request $request)
    {
        try {
            // Validar las fechas de entrada y la cuenta bancaria
            $request->validate([
                'fechaInicial' => 'required|date',
                'fechaFinal' => 'required|date',
                'banco_y_cuenta' => 'required'
            ]);

            // Obtener las fechas del request
            $fechaInicial = $request->input('fechaInicial');
            $fechaFinal = $request->input('fechaFinal');
            $cuentaBancaria = $request->input('banco_y_cuenta');

            // Calcular la fecha anterior a la fechaInicial
            $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));

            // Obtener la cuenta bancaria
            $cuenta = cuentas_bancarias::where('numero_cuenta', $cuentaBancaria)->first();

            if (!$cuenta) {
                return response()->json(['error' => 'Cuenta bancaria no encontrada'], 404);
            }

            // Obtener los IDs de ingresos y egresos relacionados con la cuenta bancaria
            $ingresosIds = datos_de_pago_ingresos::where('id_cuentas_bancarias', $cuenta->id_cuentas_bancarias)
                ->pluck('id_ingresos_egresos')
                ->toArray();

            $egresosIds = datos_de_pago_egresos::where('id_cuentas_bancarias', $cuenta->id_cuentas_bancarias)
                ->pluck('id_ingresos_egresos')
                ->toArray();

            $ids = array_merge($ingresosIds, $egresosIds);

            // Obtener el saldo inicial hasta la fecha anterior
            $ingresosAnteriores = ingresos_egresos::where('tipo', 'bancos')
                ->whereIn('id_ingresos_egresos', $ids)
                ->whereHas('cuentas', function ($query) {
                    $query->where('id_proyectos', 2);
                })
                ->where('fecha', '<', $fechaInicial)
                ->where('nomenclatura', 'like', 'IN%')
                ->sum('monto');

            $egresosAnteriores = ingresos_egresos::where('tipo', 'bancos')
                ->whereIn('id_ingresos_egresos', $ids)
                ->whereHas('cuentas', function ($query) {
                    $query->where('id_proyectos', 2);
                })
                ->where('fecha', '<', $fechaInicial)
                ->where('nomenclatura', 'like', 'EG%')
                ->sum('monto');

            $saldoInicial = $ingresosAnteriores - $egresosAnteriores;

            // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
            $data = ingresos_egresos::with('cuentas')
                ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                ->where('tipo', 'bancos')
                ->whereIn('id_ingresos_egresos', $ids)
                ->whereHas('cuentas', function ($query) {
                    $query->where('id_proyectos', 2);
                })
                ->get()
                ->map(function ($ingreso_egreso) {
                    $cuentaBancariaIngreso = $ingreso_egreso->datos_de_pago_ingresos->first()->cuentas_bancarias->numero_cuenta ?? null;
                    $cuentaBancariaEgreso = $ingreso_egreso->datos_de_pago_egresos->first()->cuentas_bancarias->numero_cuenta ?? null;
                    $cuentaBancaria = $cuentaBancariaIngreso ?? $cuentaBancariaEgreso;

                    return [
                        'nomenclatura' => $ingreso_egreso->nomenclatura,
                        'fecha' => $ingreso_egreso->fecha,
                        'descripcion' => $ingreso_egreso->descripcion,
                        'monto' => $ingreso_egreso->monto,
                        'tipo' => $ingreso_egreso->tipo,
                        'cuenta' => $ingreso_egreso->cuentas->cuenta,
                    ];
                });

            // Agregar la fila del saldo inicial al principio del array
            $data->prepend([
                'nomenclatura' => '',
                'fecha' => $fechaAnterior,
                'descripcion' => 'Saldo anterior ' . $fechaAnterior,
                'monto' => $saldoInicial,
                'tipo' => 'bancos',
                'cuenta' => 'Saldo inicial',
                'acredita' => '',
                'debita' => '',
                'total' => $saldoInicial
            ]);

            // Calcular el total acumulado
            $totalAcumulado = $saldoInicial;
            $data = $data->map(function ($item) use (&$totalAcumulado) {
                $monto = floatval($item['monto']); // Convertir a número para evitar errores de tipo

                if (strpos($item['nomenclatura'], 'IN') !== false) {
                    $item['acredita'] = $monto;
                    $item['debita'] = '';
                    $totalAcumulado += $monto;
                } elseif (strpos($item['nomenclatura'], 'EG') !== false) {
                    $item['acredita'] = '';
                    $item['debita'] = $monto;
                    $totalAcumulado -= $monto;
                } else {
                    $item['acredita'] = '';
                    $item['debita'] = '';
                }
                $item['total'] = $totalAcumulado;
                return $item;
            });

            // Agregar la fila de suma total al final del array
            $data->push([
                'nomenclatura' => '',
                'fecha' => '',
                'descripcion' => '',
                'monto' => '',
                'tipo' => 'bancos',
                'cuenta' => 'Suma total Bancos',
                'acredita' => '',
                'debita' => '',
                'total' => $totalAcumulado
            ]);

            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // Informe Libro Diario Capilla
    public function getWithCuentasLibroDiarioCA(Request $request)
    {
        try {
            // Validar las fechas de entrada
            $request->validate([
                'fechaInicial' => 'required|date',
                'fechaFinal' => 'required|date'
            ]);

            // Obtener las fechas del request
            $fechaInicial = $request->input('fechaInicial');
            $fechaFinal = $request->input('fechaFinal');

            // Calcular la fecha anterior a la fechaInicial
            $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));

            // Obtener el saldo inicial hasta la fecha anterior
            $ingresosAnteriores = ingresos_egresos::where('fecha', '<', $fechaInicial)
                ->whereHas('cuentas', function ($query) {
                    $query->where('id_proyectos', 2);
                })
                ->where(function ($query) {
                    $query->where('tipo', 'bancos')
                        ->orWhere('tipo', 'caja');
                })
                ->where('nomenclatura', 'like', 'IN%')
                ->sum('monto');

            $egresosAnteriores = ingresos_egresos::where('fecha', '<', $fechaInicial)
                ->whereHas('cuentas', function ($query) {
                    $query->where('id_proyectos', 2); // iiihhh
                })
                ->where(function ($query) {
                    $query->where('tipo', 'bancos')
                        ->orWhere('tipo', 'caja');
                })
                ->where('nomenclatura', 'like', 'EG%')
                ->sum('monto');

            $saldoInicial = $ingresosAnteriores - $egresosAnteriores;

            // Consultar los datos filtrados por fecha, tipo "bancos" o "caja" y id_proyecto
            $data = ingresos_egresos::with(['cuentas', 'datos_de_pago_ingresos', 'datos_de_pago_egresos'])
                ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                ->whereHas('cuentas', function ($query) {
                    $query->where('id_proyectos', 2);
                })
                ->where(function ($query) {
                    $query->where('tipo', 'bancos')
                        ->orWhere('tipo', 'caja');
                })
                ->get()
                ->map(function ($ingreso_egreso) {
                    $numeroDocumento = '-';
                    if ($ingreso_egreso->tipo === 'bancos') {
                        $numeroDocumento = $ingreso_egreso->datos_de_pago_ingresos->first()->numero_documento ?? $ingreso_egreso->datos_de_pago_egresos->first()->numero_documento ?? '-';
                    }

                    return [
                        'nomenclatura' => $ingreso_egreso->nomenclatura,
                        'fecha' => $ingreso_egreso->fecha,
                        'descripcion' => $ingreso_egreso->descripcion,
                        'monto' => $ingreso_egreso->monto,
                        'tipo' => $ingreso_egreso->tipo,
                        'cuenta' => $ingreso_egreso->cuentas->cuenta,
                        'numero_documento' => $numeroDocumento
                    ];
                });

            // Agregar la fila del saldo inicial al principio del array
            $data->prepend([
                'nomenclatura' => '',
                'fecha' => $fechaAnterior,
                'descripcion' => 'Saldo anterior ' . $fechaAnterior,
                'monto' => $saldoInicial,
                'tipo' => 'bancos',
                'cuenta' => 'Saldo inicial',
                'acredita' => '',
                'debita' => '',
                'total' => $saldoInicial,
                'numero_documento' => '-'
            ]);

            // Calcular el total acumulado
            $totalAcumulado = $saldoInicial;
            $data = $data->map(function ($item) use (&$totalAcumulado) {
                $monto = floatval($item['monto']); // Convertir a número para evitar errores de tipo

                if (strpos($item['nomenclatura'], 'IN') !== false) {
                    $item['acredita'] = $monto;
                    $item['debita'] = '';
                    $totalAcumulado += $monto;
                } elseif (strpos($item['nomenclatura'], 'EG') !== false) {
                    $item['acredita'] = '';
                    $item['debita'] = $monto;
                    $totalAcumulado -= $monto;
                } else {
                    $item['acredita'] = '';
                    $item['debita'] = '';
                }
                $item['total'] = $totalAcumulado;
                return $item;
            });

            // Agregar la fila de suma total al final del array
            $data->push([
                'nomenclatura' => '',
                'fecha' => '',
                'descripcion' => '',
                'monto' => '',
                'tipo' => 'bancos',
                'cuenta' => 'Suma total Bancos',
                'acredita' => '',
                'debita' => '',
                'total' => $totalAcumulado,
                'numero_documento' => '-'
            ]);

            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // metodo para el reporte final de ingresos y egresos AGRICOLA a
    public function getReporteFinalAG(Request $request)
    {
        // Validar las fechas de entrada
        $validaciondata = $request->validate([
            'tipo' => 'required|string',
            'mes' => 'required|string|in:enero,febrero,marzo,abril,mayo,junio,julio,agosto,septiembre,octubre,noviembre,diciembre',
            'contador' => 'required|string',
            'responsable' => 'required|string',
            'economa' => 'required|string'
        ]);

        $tipo = $validaciondata['tipo'];
        $mes = $validaciondata['mes'];
        $contador = $validaciondata['contador'];
        $responsable = $validaciondata['responsable'];
        $economa = $validaciondata['economa'];

        if ($tipo == 'mensual')
        {
            // Obtener el año actual
            $añoActual = Carbon::now()->year;

            // Inicializar las variables de fecha
            $fechaInicial = null;
            $fechaFinal = null;

            // Opciones de meses y asignación de fechas
            switch($mes) {
                case 'enero':
                    $fechaInicial = Carbon::createFromDate($añoActual-1, 12, 31);
                    $fechaFinal = Carbon::createFromDate($añoActual, 1, 31);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta para caja
                    $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values(); 
                    // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta para bancos
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;
                case 'febrero':
                    $fechaInicial = Carbon::createFromDate($añoActual, 1, 31);
                    $fechaFinal = Carbon::createFromDate($añoActual, 2, Carbon::createFromDate($añoActual, 2, 1)->daysInMonth);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));
                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                     // Agrupar y sumar montos por cuenta para caja
                     $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values(); 
                    // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta para bancos
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;
                case 'marzo':
                    $fechaInicial = Carbon::createFromDate($añoActual, 2, Carbon::createFromDate($añoActual, 2, 1)->daysInMonth);
                    $fechaFinal = Carbon::createFromDate($añoActual, 3, 31);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));
                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                     // Agrupar y sumar montos por cuenta para caja
                     $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values(); 
                    // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta para bancos
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;
                case 'abril':
                    $fechaInicial = Carbon::createFromDate($añoActual, 3, 31);
                    $fechaFinal = Carbon::createFromDate($añoActual, 4, 30);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));
                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta para caja
                    $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values(); 
                    // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta para bancos
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;
                case 'mayo':
                    $fechaInicial = Carbon::createFromDate($añoActual, 4, 30);
                    $fechaFinal = Carbon::createFromDate($añoActual, 5, 31);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                
                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta para caja
                    $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values(); 
                    // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta para bancos
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values();

                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;
 
                    break;

                case 'junio':
                    $fechaInicial = Carbon::createFromDate($añoActual, 5, 31);
                    $fechaFinal = Carbon::createFromDate($añoActual, 6, 30);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                     // Agrupar y sumar montos por cuenta para caja
                     $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values(); 
                    // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta para bancos
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;
                case 'julio':
                    $fechaInicial = Carbon::createFromDate($añoActual, 6, 30);
                    $fechaFinal = Carbon::createFromDate($añoActual, 7, 31);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));
                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                     // Agrupar y sumar montos por cuenta para caja
                     $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values(); 
                    // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta para bancos
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;
                case 'agosto':
                    $fechaInicial = Carbon::createFromDate($añoActual, 7, 31);
                    $fechaFinal = Carbon::createFromDate($añoActual, 8, 31);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));
                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                     // Agrupar y sumar montos por cuenta para caja
                     $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values(); 
                    // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta para bancos
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;
                case 'septiembre':
                    $fechaInicial = Carbon::createFromDate($añoActual, 8, 31);
                    $fechaFinal = Carbon::createFromDate($añoActual, 9, 30);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));
                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                     // Agrupar y sumar montos por cuenta para caja
                     $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values(); 
                    // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta para bancos
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;
                case 'octubre':
                    $fechaInicial = Carbon::createFromDate($añoActual, 9, 30);
                    $fechaFinal = Carbon::createFromDate($añoActual, 10, 31);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));
                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                     // Agrupar y sumar montos por cuenta para caja
                     $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values(); 
                    // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta para bancos
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;
                case 'noviembre':
                    $fechaInicial = Carbon::createFromDate($añoActual, 10, 31);
                    $fechaFinal = Carbon::createFromDate($añoActual, 11, 30);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));
                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta para caja
                    $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values(); 
                    // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta para bancos
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;
                case 'diciembre':
                    $fechaInicial = Carbon::createFromDate($añoActual, 11, 30);
                    $fechaFinal = Carbon::createFromDate($añoActual, 12, 31);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));
                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta para caja
                    $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values(); 
                    // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta para bancos
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;
                default:
                    return response()->json(['error' => 'Mes inválido'], 400);
            }

            // Aquí puedes usar $fechaInicial y $fechaFinal para tus consultas o lógica adicional
            // Retornar las fechas en formato JSON
            return response()->json([
                'fecha_anterior' => $fechaAnterior,
                'mes' => $mes,
                'fecha_inicial' => $fechaInicial->toDateString(),
                'fecha_final' => $fechaFinal->toDateString(),
                'saldo_inicial_bancos' => $saldoInicialBancos,
                'saldo_inicial_caja' => $saldoInicialCaja,
                'saldo_inicial' => $saldoInicial,
                'total_ingresos_caja' => $totalIngresosCaja,
                'total_egresos_caja' => $totalEgresosCaja,
                'total_ingresos_bancos' => $totalIngresosBancos,
                'total_egresos_bancos' => $totalEgresosBancos,
                'total_general_ingresos' => $totalGeneralIngresos,
                'total_general_egresos' => $totalGeneralEgresos,
                'data_caja' => $dataGroupedCaja,
                'data_bancos' => $dataGroupedBancos,
                'total_saldo_final' => $saldoFinal,
                //'total_saldo_final2' => $saldoFinalsuma,
                'total_saldo_final_caja' => $saldoFinalCaja,
                'total_saldo_final_bancos' => $saldoFinalBancos,
                'responsable' => $responsable,
                'contador' => $contador,
                'economa' => $economa
            ]);
        } 
        if ($tipo == 'trimestral')
        {
                // Obtener el año actual
                $añoActual = Carbon::now()->year;

                // Inicializar las variables de fecha
                $fechaInicial = null;
                $fechaFinal = null;
 
                switch($mes){
                    
                case 'enero':
                    $fechaInicial = Carbon::createFromDate($añoActual-1, 12, 31);
                    $fechaFinal = Carbon::createFromDate($añoActual, 3, 31);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                     // Agrupar y sumar montos por cuenta para caja
                     $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values(); 
                    // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta para bancos
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;
                case 'abril':
                    $fechaInicial = Carbon::createFromDate($añoActual, 3, 31);
                    $fechaFinal = Carbon::createFromDate($añoActual, 6, 30);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));
                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                     // Agrupar y sumar montos por cuenta para caja
                     $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values(); 
                    // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta para bancos
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;
                case 'julio':
                    $fechaInicial = Carbon::createFromDate($añoActual, 6, 30);
                    $fechaFinal = Carbon::createFromDate($añoActual, 9, 30);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));
                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                     // Agrupar y sumar montos por cuenta para caja
                     $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values(); 
                    // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta para bancos
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;

                case 'octubre':
                    $fechaInicial = Carbon::createFromDate($añoActual, 9, 30);
                    $fechaFinal = Carbon::createFromDate($añoActual, 12, 31);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));
                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta para caja
                    $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values(); 
                    // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta para bancos
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;
                    
                 default:
                    return response()->json(['error' => 'Mes inválido'], 400);
                 
                }
 
                return response()->json([
                    'fecha_anterior' => $fechaAnterior,
                    'mes' => $mes,
                    'fecha_inicial' => $fechaInicial->toDateString(),
                    'fecha_final' => $fechaFinal->toDateString(),
                    'saldo_inicial_bancos' => $saldoInicialBancos,
                    'saldo_inicial_caja' => $saldoInicialCaja,
                    'saldo_inicial' => $saldoInicial,
                    'total_ingresos_caja' => $totalIngresosCaja,
                    'total_egresos_caja' => $totalEgresosCaja,
                    'total_ingresos_bancos' => $totalIngresosBancos,
                    'total_egresos_bancos' => $totalEgresosBancos,
                    'total_general_ingresos' => $totalGeneralIngresos,
                    'total_general_egresos' => $totalGeneralEgresos,
                    'data_caja' => $dataGroupedCaja,
                    'data_bancos' => $dataGroupedBancos,
                    'total_saldo_final' => $saldoFinal,
                    //'total_saldo_final2' => $saldoFinalsuma,
                    'total_saldo_final_caja' => $saldoFinalCaja,
                    'total_saldo_final_bancos' => $saldoFinalBancos,
                    'responsable' => $responsable,
                    'contador' => $contador,
                    'economa' => $economa
                ]);

        }
        if ($tipo == 'semestral')
        {
                // Obtener el año actual
                $añoActual = Carbon::now()->year;

                // Inicializar las variables de fecha
                $fechaInicial = null;
                $fechaFinal = null;
 
                switch($mes){
                    
                case 'enero':
                    $fechaInicial = Carbon::createFromDate($añoActual-1, 12, 31);
                    $fechaFinal = Carbon::createFromDate($añoActual, 6, 30);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta para caja
                    $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values(); 
                    // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta para bancos
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;
                case 'julio':
                    $fechaInicial = Carbon::createFromDate($añoActual, 6, 30);
                    $fechaFinal = Carbon::createFromDate($añoActual, 12, 31);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));
                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                     // Agrupar y sumar montos por cuenta para caja
                     $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values(); 
                    // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 1);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta para bancos
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        // Solo incluir cuentas con ingresos o egresos distintos de cero
                        if ($ingresos > 0 || $egresos > 0) {
                            $result = [
                                'cuenta' => $group->first()->cuentas->cuenta
                            ];
                            
                            if ($ingresos > 0) {
                                $result['ingresos'] = number_format($ingresos, 2);
                            }
                            
                            if ($egresos > 0) {
                                $result['egresos'] = number_format($egresos, 2);
                            }
                            
                            return $result;
                        }
                    })->filter()->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;

                    default:
                    return response()->json(['error' => 'Mes inválido'], 400);
             }
 
                return response()->json([
                    'fecha_anterior' => $fechaAnterior,
                    'mes' => $mes,
                    'fecha_inicial' => $fechaInicial->toDateString(),
                    'fecha_final' => $fechaFinal->toDateString(),
                    'saldo_inicial_bancos' => $saldoInicialBancos,
                    'saldo_inicial_caja' => $saldoInicialCaja,
                    'saldo_inicial' => $saldoInicial,
                    'total_ingresos_caja' => $totalIngresosCaja,
                    'total_egresos_caja' => $totalEgresosCaja,
                    'total_ingresos_bancos' => $totalIngresosBancos,
                    'total_egresos_bancos' => $totalEgresosBancos,
                    'total_general_ingresos' => $totalGeneralIngresos,
                    'total_general_egresos' => $totalGeneralEgresos,
                    'data_caja' => $dataGroupedCaja,
                    'data_bancos' => $dataGroupedBancos,
                    'total_saldo_final' => $saldoFinal,
                    //'total_saldo_final2' => $saldoFinalsuma,
                    'total_saldo_final_caja' => $saldoFinalCaja,
                    'total_saldo_final_bancos' => $saldoFinalBancos,
                    'responsable' => $responsable,
                    'contador' => $contador,
                    'economa' => $economa
                ]);
        }
        if ($tipo = 'anual')
        {
            // Obtener el año actual
            $añoActual = Carbon::now()->year;

            // Inicializar las variables de fecha
            $fechaInicial = null;
            $fechaFinal = null;

            switch($mes){
                
            case 'enero':
                $fechaInicial = Carbon::createFromDate($añoActual-1, 12, 31);
                $fechaFinal = Carbon::createFromDate($añoActual, 12, 31);
                // Calcular la fecha anterior a la fechaInicial
                $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));

                // Obtener el saldo inicial hasta la fecha anterior
                $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                ->whereHas('cuentas', function ($query) {
                    $query->where('id_proyectos', 1);
                })
                ->where(function ($query) {
                    $query->where('tipo', 'bancos');
                })
                ->where('nomenclatura', 'like', 'IN%')
                ->sum('monto');

                $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                ->whereHas('cuentas', function ($query) {
                    $query->where('id_proyectos', 1);
                })
                ->where(function ($query) {
                    $query->where('tipo', 'bancos');
                })
                ->where('nomenclatura', 'like', 'EG%')
                ->sum('monto');

                // Obtener el saldo inicial hasta la fecha anterior
                $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                ->whereHas('cuentas', function ($query) {
                    $query->where('id_proyectos', 1);
                })
                ->where(function ($query) {
                    $query->where('tipo', 'Caja');
                })
                ->where('nomenclatura', 'like', 'IN%')
                ->sum('monto');

                $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                ->whereHas('cuentas', function ($query) {
                    $query->where('id_proyectos', 1);
                })
                ->where(function ($query) {
                    $query->where('tipo', 'caja');
                })
                ->where('nomenclatura', 'like', 'EG%')
                ->sum('monto');

                $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                $dataCaja = ingresos_egresos::with('cuentas')
                ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                ->where('tipo', 'caja')
                ->whereHas('cuentas', function ($query) {
                    $query->where('id_proyectos', 1);
                })
                ->get();
                // Agrupar y sumar montos por cuenta para caja
                $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                    $ingresos = $group->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');
                    
                    $egresos = $group->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');
                    
                    // Solo incluir cuentas con ingresos o egresos distintos de cero
                    if ($ingresos > 0 || $egresos > 0) {
                        $result = [
                            'cuenta' => $group->first()->cuentas->cuenta
                        ];
                        
                        if ($ingresos > 0) {
                            $result['ingresos'] = number_format($ingresos, 2);
                        }
                        
                        if ($egresos > 0) {
                            $result['egresos'] = number_format($egresos, 2);
                        }
                        
                        return $result;
                    }
                })->filter()->values(); 
                // datos de bancos
                // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                $dataBancos = ingresos_egresos::with('cuentas')
                ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                ->where('tipo', 'bancos')
                ->whereHas('cuentas', function ($query) {
                    $query->where('id_proyectos', 1);
                })
                ->get();

                // Agrupar y sumar montos por cuenta para bancos
                $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                    $ingresos = $group->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');
                    
                    $egresos = $group->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');
                    
                    // Solo incluir cuentas con ingresos o egresos distintos de cero
                    if ($ingresos > 0 || $egresos > 0) {
                        $result = [
                            'cuenta' => $group->first()->cuentas->cuenta
                        ];
                        
                        if ($ingresos > 0) {
                            $result['ingresos'] = number_format($ingresos, 2);
                        }
                        
                        if ($egresos > 0) {
                            $result['egresos'] = number_format($egresos, 2);
                        }
                        
                        return $result;
                    }
                })->filter()->values();
                
                // Calcular totales
                $totalIngresosCaja = $dataCaja->filter(function ($item) {
                    return strpos($item->nomenclatura, 'IN') === 0;
                })->sum('monto');

                $totalEgresosCaja = $dataCaja->filter(function ($item) {
                    return strpos($item->nomenclatura, 'EG') === 0;
                })->sum('monto');

                $totalIngresosBancos = $dataBancos->filter(function ($item) {
                    return strpos($item->nomenclatura, 'IN') === 0;
                })->sum('monto');

                $totalEgresosBancos = $dataBancos->filter(function ($item) {
                    return strpos($item->nomenclatura, 'EG') === 0;
                })->sum('monto');

                $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                //saldo final
                $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                break;

                default:
                return response()->json(['error' => 'Mes inválido'], 400);
         }

            return response()->json([
                'fecha_anterior' => $fechaAnterior,
                'mes' => $mes,
                'fecha_inicial' => $fechaInicial->toDateString(),
                'fecha_final' => $fechaFinal->toDateString(),
                'saldo_inicial_bancos' => $saldoInicialBancos,
                'saldo_inicial_caja' => $saldoInicialCaja,
                'saldo_inicial' => $saldoInicial,
                'total_ingresos_caja' => $totalIngresosCaja,
                'total_egresos_caja' => $totalEgresosCaja,
                'total_ingresos_bancos' => $totalIngresosBancos,
                'total_egresos_bancos' => $totalEgresosBancos,
                'total_general_ingresos' => $totalGeneralIngresos,
                'total_general_egresos' => $totalGeneralEgresos,
                'data_caja' => $dataGroupedCaja,
                'data_bancos' => $dataGroupedBancos,
                'total_saldo_final' => $saldoFinal,
                'total_saldo_final_caja' => $saldoFinalCaja,
                'total_saldo_final_bancos' => $saldoFinalBancos,
                'responsable' => $responsable,
                'contador' => $contador,
                'economa' => $economa
            ]);

        }else {
            return response()->json(['error' => 'Tipo inválido'], 400);
        }
    }

    // reporte final d capilla
    
    public function getReporteFinalCA(Request $request)
    {
        // Validar las fechas de entrada
        $validaciondata = $request->validate([
            'tipo' => 'required|string',
            'mes' => 'required|string|in:enero,febrero,marzo,abril,mayo,junio,julio,agosto,septiembre,octubre,noviembre,diciembre',
            'responsable' => 'required|string',
            'sirviente' => 'required|string',
            'economa' => 'required|string'
        ]);

        $tipo = $validaciondata['tipo'];
        $mes = $validaciondata['mes'];
        $sirviente = $validaciondata['sirviente'];
        $responsable = $validaciondata['responsable'];
        $economa = $validaciondata['economa'];

        if ($tipo == 'mensual')
        {
            // Obtener el año actual
            $añoActual = Carbon::now()->year;

            // Inicializar las variables de fecha
            $fechaInicial = null;
            $fechaFinal = null;

            // Opciones de meses y asignación de fechas
            switch($mes) {
                case 'enero':
                    $fechaInicial = Carbon::createFromDate($añoActual-1, 12, 31);
                    $fechaFinal = Carbon::createFromDate($añoActual, 1, 31);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2),
                        ];
                    })->values();


                     // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2)
                        ];
                    })->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;
                case 'febrero':
                    $fechaInicial = Carbon::createFromDate($añoActual, 1, 31);
                    $fechaFinal = Carbon::createFromDate($añoActual, 2, Carbon::createFromDate($añoActual, 2, 1)->daysInMonth);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));
                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2),
                        ];
                    })->values();


                     // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2)
                        ];
                    })->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;
                case 'marzo':
                    $fechaInicial = Carbon::createFromDate($añoActual, 2, Carbon::createFromDate($añoActual, 2, 1)->daysInMonth);
                    $fechaFinal = Carbon::createFromDate($añoActual, 3, 31);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));
                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2),
                        ];
                    })->values();


                     // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2)
                        ];
                    })->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;
                case 'abril':
                    $fechaInicial = Carbon::createFromDate($añoActual, 3, 31);
                    $fechaFinal = Carbon::createFromDate($añoActual, 4, 30);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));
                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2),
                        ];
                    })->values();


                     // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2)
                        ];
                    })->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;
                case 'mayo':
                    $fechaInicial = Carbon::createFromDate($añoActual, 4, 30);
                    $fechaFinal = Carbon::createFromDate($añoActual, 5, 31);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                
                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2)
                        ];
                    })->values();

                    // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2)
                        ];
                    })->values();

                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;
 
                    break;

                case 'junio':
                    $fechaInicial = Carbon::createFromDate($añoActual, 5, 31);
                    $fechaFinal = Carbon::createFromDate($añoActual, 6, 30);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2),
                        ];
                    })->values();


                     // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2)
                        ];
                    })->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;
                case 'julio':
                    $fechaInicial = Carbon::createFromDate($añoActual, 6, 30);
                    $fechaFinal = Carbon::createFromDate($añoActual, 7, 31);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));
                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2),
                        ];
                    })->values();


                     // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2)
                        ];
                    })->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;
                case 'agosto':
                    $fechaInicial = Carbon::createFromDate($añoActual, 7, 31);
                    $fechaFinal = Carbon::createFromDate($añoActual, 8, 31);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));
                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2),
                        ];
                    })->values();


                     // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2)
                        ];
                    })->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;
                case 'septiembre':
                    $fechaInicial = Carbon::createFromDate($añoActual, 8, 31);
                    $fechaFinal = Carbon::createFromDate($añoActual, 9, 30);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));
                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2),
                        ];
                    })->values();


                     // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2)
                        ];
                    })->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;
                case 'octubre':
                    $fechaInicial = Carbon::createFromDate($añoActual, 9, 30);
                    $fechaFinal = Carbon::createFromDate($añoActual, 10, 31);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));
                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2),
                        ];
                    })->values();


                     // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2)
                        ];
                    })->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;
                case 'noviembre':
                    $fechaInicial = Carbon::createFromDate($añoActual, 10, 31);
                    $fechaFinal = Carbon::createFromDate($añoActual, 11, 30);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));
                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2),
                        ];
                    })->values();


                     // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2)
                        ];
                    })->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;
                case 'diciembre':
                    $fechaInicial = Carbon::createFromDate($añoActual, 11, 30);
                    $fechaFinal = Carbon::createFromDate($añoActual, 12, 31);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));
                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2),
                        ];
                    })->values();


                     // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2)
                        ];
                    })->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;
                default:
                    return response()->json(['error' => 'Mes inválido'], 400);
            }

            // Aquí puedes usar $fechaInicial y $fechaFinal para tus consultas o lógica adicional
            // Retornar las fechas en formato JSON
            return response()->json([
                'fecha_anterior' => $fechaAnterior,
                'mes' => $mes,
                'fecha_inicial' => $fechaInicial->toDateString(),
                'fecha_final' => $fechaFinal->toDateString(),
                'saldo_inicial_bancos' => $saldoInicialBancos,
                'saldo_inicial_caja' => $saldoInicialCaja,
                'saldo_inicial' => $saldoInicial,
                'total_ingresos_caja' => $totalIngresosCaja,
                'total_egresos_caja' => $totalEgresosCaja,
                'total_ingresos_bancos' => $totalIngresosBancos,
                'total_egresos_bancos' => $totalEgresosBancos,
                'total_general_ingresos' => $totalGeneralIngresos,
                'total_general_egresos' => $totalGeneralEgresos,
                'data_caja' => $dataGroupedCaja,
                'data_bancos' => $dataGroupedBancos,
                'total_saldo_final' => $saldoFinal,
                //'total_saldo_final2' => $saldoFinalsuma,
                'total_saldo_final_caja' => $saldoFinalCaja,
                'total_saldo_final_bancos' => $saldoFinalBancos,
                'responsable' => $responsable,
                'sirviente' => $sirviente,
                'economa' => $economa
            ]);
        } 
        if ($tipo == 'trimestral')
        {
                // Obtener el año actual
                $añoActual = Carbon::now()->year;

                // Inicializar las variables de fecha
                $fechaInicial = null;
                $fechaFinal = null;
 
                switch($mes){
                    
                case 'enero':
                    $fechaInicial = Carbon::createFromDate($añoActual-1, 12, 31);
                    $fechaFinal = Carbon::createFromDate($añoActual, 3, 31);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2),
                        ];
                    })->values();


                     // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2)
                        ];
                    })->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;
                case 'abril':
                    $fechaInicial = Carbon::createFromDate($añoActual, 3, 31);
                    $fechaFinal = Carbon::createFromDate($añoActual, 6, 30);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));
                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2),
                        ];
                    })->values();


                     // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2)
                        ];
                    })->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;
                case 'julio':
                    $fechaInicial = Carbon::createFromDate($añoActual, 6, 30);
                    $fechaFinal = Carbon::createFromDate($añoActual, 9, 30);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));
                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2),
                        ];
                    })->values();


                     // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2)
                        ];
                    })->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;

                case 'octubre':
                    $fechaInicial = Carbon::createFromDate($añoActual, 9, 30);
                    $fechaFinal = Carbon::createFromDate($añoActual, 12, 31);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));
                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2),
                        ];
                    })->values();


                        // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2)
                        ];
                    })->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;
                    
                 default:
                    return response()->json(['error' => 'Mes inválido'], 400);
                 
                }
 
                return response()->json([
                    'fecha_anterior' => $fechaAnterior,
                    'mes' => $mes,
                    'fecha_inicial' => $fechaInicial->toDateString(),
                    'fecha_final' => $fechaFinal->toDateString(),
                    'saldo_inicial_bancos' => $saldoInicialBancos,
                    'saldo_inicial_caja' => $saldoInicialCaja,
                    'saldo_inicial' => $saldoInicial,
                    'total_ingresos_caja' => $totalIngresosCaja,
                    'total_egresos_caja' => $totalEgresosCaja,
                    'total_ingresos_bancos' => $totalIngresosBancos,
                    'total_egresos_bancos' => $totalEgresosBancos,
                    'total_general_ingresos' => $totalGeneralIngresos,
                    'total_general_egresos' => $totalGeneralEgresos,
                    'data_caja' => $dataGroupedCaja,
                    'data_bancos' => $dataGroupedBancos,
                    'total_saldo_final' => $saldoFinal,
                    //'total_saldo_final2' => $saldoFinalsuma,
                    'total_saldo_final_caja' => $saldoFinalCaja,
                    'total_saldo_final_bancos' => $saldoFinalBancos,
                    'responsable' => $responsable,
                    'sirviente' => $sirviente,
                    'economa' => $economa
                ]);

        }
        if ($tipo == 'semestral')
        {
                // Obtener el año actual
                $añoActual = Carbon::now()->year;

                // Inicializar las variables de fecha
                $fechaInicial = null;
                $fechaFinal = null;
 
                switch($mes){
                    
                case 'enero':
                    $fechaInicial = Carbon::createFromDate($añoActual-1, 12, 31);
                    $fechaFinal = Carbon::createFromDate($añoActual, 6, 30);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2),
                        ];
                    })->values();


                     // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2)
                        ];
                    })->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;
                case 'julio':
                    $fechaInicial = Carbon::createFromDate($añoActual, 6, 30);
                    $fechaFinal = Carbon::createFromDate($añoActual, 12, 31);
                    // Calcular la fecha anterior a la fechaInicial
                    $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));
                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'bancos');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    // Obtener el saldo inicial hasta la fecha anterior
                    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'Caja');
                    })
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->where(function ($query) {
                        $query->where('tipo', 'caja');
                    })
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                    $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                    $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                    // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                    $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2),
                        ];
                    })->values();


                     // datos de bancos
                    // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                    $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) {
                        $query->where('id_proyectos', 2);
                    })
                    ->get();

                    // Agrupar y sumar montos por cuenta
                    $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                        $ingresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'IN') === 0;
                        })->sum('monto');
                        
                        $egresos = $group->filter(function ($item) {
                            return strpos($item->nomenclatura, 'EG') === 0;
                        })->sum('monto');
                        
                        return [
                            'cuenta' => $group->first()->cuentas->cuenta,
                            'ingresos' => number_format($ingresos, 2),
                            'egresos' => number_format($egresos, 2)
                        ];
                    })->values();
                    
                    // Calcular totales
                    $totalIngresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosCaja = $dataCaja->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalIngresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $totalEgresosBancos = $dataBancos->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                    $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                    //saldo final
                    $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                    $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                    $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                    break;

                    default:
                    return response()->json(['error' => 'Mes inválido'], 400);
             }
 
                return response()->json([
                    'fecha_anterior' => $fechaAnterior,
                    'mes' => $mes,
                    'fecha_inicial' => $fechaInicial->toDateString(),
                    'fecha_final' => $fechaFinal->toDateString(),
                    'saldo_inicial_bancos' => $saldoInicialBancos,
                    'saldo_inicial_caja' => $saldoInicialCaja,
                    'saldo_inicial' => $saldoInicial,
                    'total_ingresos_caja' => $totalIngresosCaja,
                    'total_egresos_caja' => $totalEgresosCaja,
                    'total_ingresos_bancos' => $totalIngresosBancos,
                    'total_egresos_bancos' => $totalEgresosBancos,
                    'total_general_ingresos' => $totalGeneralIngresos,
                    'total_general_egresos' => $totalGeneralEgresos,
                    'data_caja' => $dataGroupedCaja,
                    'data_bancos' => $dataGroupedBancos,
                    'total_saldo_final' => $saldoFinal,
                    //'total_saldo_final2' => $saldoFinalsuma,
                    'total_saldo_final_caja' => $saldoFinalCaja,
                    'total_saldo_final_bancos' => $saldoFinalBancos,
                    'responsable' => $responsable,
                    'sirviente' => $sirviente,
                    'economa' => $economa
                ]);

        }
        if ($tipo = 'anual')
        {
            // Obtener el año actual
            $añoActual = Carbon::now()->year;

            // Inicializar las variables de fecha
            $fechaInicial = null;
            $fechaFinal = null;

            switch($mes){
                
            case 'enero':
                $fechaInicial = Carbon::createFromDate($añoActual-1, 12, 31);
                $fechaFinal = Carbon::createFromDate($añoActual, 12, 31);
                // Calcular la fecha anterior a la fechaInicial
                $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));

                // Obtener el saldo inicial hasta la fecha anterior
                $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                ->whereHas('cuentas', function ($query) {
                    $query->where('id_proyectos', 2);
                })
                ->where(function ($query) {
                    $query->where('tipo', 'bancos');
                })
                ->where('nomenclatura', 'like', 'IN%')
                ->sum('monto');

                $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                ->whereHas('cuentas', function ($query) {
                    $query->where('id_proyectos', 2);
                })
                ->where(function ($query) {
                    $query->where('tipo', 'bancos');
                })
                ->where('nomenclatura', 'like', 'EG%')
                ->sum('monto');

                // Obtener el saldo inicial hasta la fecha anterior
                $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                ->whereHas('cuentas', function ($query) {
                    $query->where('id_proyectos', 2);
                })
                ->where(function ($query) {
                    $query->where('tipo', 'Caja');
                })
                ->where('nomenclatura', 'like', 'IN%')
                ->sum('monto');

                $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                ->whereHas('cuentas', function ($query) {
                    $query->where('id_proyectos', 2);
                })
                ->where(function ($query) {
                    $query->where('tipo', 'caja');
                })
                ->where('nomenclatura', 'like', 'EG%')
                ->sum('monto');

                $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                // Consultar los datos filtrados por fecha, tipo "caja" y id_proyecto
                $dataCaja = ingresos_egresos::with('cuentas')
                ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                ->where('tipo', 'caja')
                ->whereHas('cuentas', function ($query) {
                    $query->where('id_proyectos', 2);
                })
                ->get();

                // Agrupar y sumar montos por cuenta
                $dataGroupedCaja = $dataCaja->groupBy('cuentas.cuenta')->map(function ($group) {
                    $ingresos = $group->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');
                    
                    $egresos = $group->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');
                    
                    return [
                        'cuenta' => $group->first()->cuentas->cuenta,
                        'ingresos' => number_format($ingresos, 2),
                        'egresos' => number_format($egresos, 2),
                    ];
                })->values();


                 // datos de bancos
                // Consultar los datos filtrados por fecha, tipo "bancos" y id_proyecto
                $dataBancos = ingresos_egresos::with('cuentas')
                ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                ->where('tipo', 'bancos')
                ->whereHas('cuentas', function ($query) {
                    $query->where('id_proyectos', 2);
                })
                ->get();

                // Agrupar y sumar montos por cuenta
                $dataGroupedBancos = $dataBancos->groupBy('cuentas.cuenta')->map(function ($group) {
                    $ingresos = $group->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');
                    
                    $egresos = $group->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');
                    
                    return [
                        'cuenta' => $group->first()->cuentas->cuenta,
                        'ingresos' => number_format($ingresos, 2),
                        'egresos' => number_format($egresos, 2)
                    ];
                })->values();
                
                // Calcular totales
                $totalIngresosCaja = $dataCaja->filter(function ($item) {
                    return strpos($item->nomenclatura, 'IN') === 0;
                })->sum('monto');

                $totalEgresosCaja = $dataCaja->filter(function ($item) {
                    return strpos($item->nomenclatura, 'EG') === 0;
                })->sum('monto');

                $totalIngresosBancos = $dataBancos->filter(function ($item) {
                    return strpos($item->nomenclatura, 'IN') === 0;
                })->sum('monto');

                $totalEgresosBancos = $dataBancos->filter(function ($item) {
                    return strpos($item->nomenclatura, 'EG') === 0;
                })->sum('monto');

                $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                //saldo final
                $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                $saldoFinalsuma = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

                break;

                default:
                return response()->json(['error' => 'Mes inválido'], 400);
         }

            return response()->json([
                'fecha_anterior' => $fechaAnterior,
                'mes' => $mes,
                'fecha_inicial' => $fechaInicial->toDateString(),
                'fecha_final' => $fechaFinal->toDateString(),
                'saldo_inicial_bancos' => $saldoInicialBancos,
                'saldo_inicial_caja' => $saldoInicialCaja,
                'saldo_inicial' => $saldoInicial,
                'total_ingresos_caja' => $totalIngresosCaja,
                'total_egresos_caja' => $totalEgresosCaja,
                'total_ingresos_bancos' => $totalIngresosBancos,
                'total_egresos_bancos' => $totalEgresosBancos,
                'total_general_ingresos' => $totalGeneralIngresos,
                'total_general_egresos' => $totalGeneralEgresos,
                'data_caja' => $dataGroupedCaja,
                'data_bancos' => $dataGroupedBancos,
                'total_saldo_final' => $saldoFinal,
                //'total_saldo_final2' => $saldoFinalsuma,
                'total_saldo_final_caja' => $saldoFinalCaja,
                'total_saldo_final_bancos' => $saldoFinalBancos,
                'responsable' => $responsable,
                'sirviente' => $sirviente,
                'economa' => $economa
            ]);
        }else {
            return response()->json(['error' => 'Tipo inválido'], 400);
        }
    }

    public function anticipoAG(Request $request){
  // Aqui vamos a validar los datos (los campos calculados se asignan automáticamente)
        $request->validate([
            'fecha' => 'required|date',
            'identificacion' => 'required',
            'nombre' => 'required',
            'descripcion' => 'required',
            'monto' => 'required|numeric',
            'tipo' => 'required|in:caja,banco',
            'cuenta' => 'required',
            
            'documento' => 'required_if:tipo,banco',
            'numero_documento' => 'required_if:tipo,banco',
            'fecha_emision' => 'required_if:tipo,banco',
            'id_cuentas_bancarias' => 'required_if:tipo,banco',
        ], [
            'documento.required_if'            => 'El documento es obligatorio cuando el tipo es banco.',
            'numero_documento.required_if'     => 'El número de documento es obligatorio cuando el tipo es banco.',
            'fecha_emision.required_if'        => 'La fecha de emisión es obligatoria cuando el tipo es banco.',
            'id_cuentas_bancarias.required_if' => 'La cuenta bancaria es obligatoria cuando el tipo es banco.',
        ]);

        try{
            return DB::transaction(function () use ($request) {
            // Buscar la cuenta por su nombre
            $cuenta = cuentas::where('cuenta', 'Anticipo de compras y gastos')->where('id_clasificacion', '2')->where('id_proyectos', '1')->first();
            // Si la cuenta no se encuentra, devolver un error
            if (!$cuenta) {
                return response()->json(['error' => 'La cuenta proporcionada no existe'], 404);
            }

            // Verificar si la cuenta pertenece al id_proyectos 1
             if ($cuenta->id_proyectos != 1) {
                 return response()->json(['error' => 'La cuenta no pertenece al proyecto agricola'], 400);
             }

                 $tipo = strtolower($request->input('tipo'));

            // Crear un nuevo registro en la tabla ingresos_egresos
            $ingreso_egreso = new ingresos_egresos();
            $ingreso_egreso->fecha = $request->input('fecha');
            $ingreso_egreso->identificacion = $request->input('identificacion');
            $ingreso_egreso->nombre = $request->input('nombre');
            $ingreso_egreso->descripcion = $request->input('descripcion');
            $ingreso_egreso->monto = $request->input('monto');
            $ingreso_egreso->tipo = $tipo;
            $ingreso_egreso->id_cuentas = $cuenta->id_cuentas;

            // Asignar montos calculados automáticamente según la solicitud:
            // - monto_debe debe igualar el monto ingresado
            // - monto_haber queda en 0.00
            // - es_pendiente pasa a 1
            $ingreso_egreso->monto_debe = $request->input('monto');
            $ingreso_egreso->monto_haber = 0.00;
            $ingreso_egreso->es_pendiente = 1;

            // Guardar el ingreso/egreso
            $ingreso_egreso->save();

            // Obtener el id del ingreso/egreso recién creado
            $id_ingresos_egresos = $ingreso_egreso->id_ingresos_egresos;

              if ($tipo === 'banco') {
                $cuenta_bancaria = cuentas_bancarias::find($request->input('id_cuentas_bancarias'));
                if (!$cuenta_bancaria) {
                    throw new \RuntimeException('La cuenta bancaria indicada no existe.');
                }

                $datos_pago = new datos_de_pago_ingresos();
                $datos_pago->id_ingresos_egresos  = $ingreso_egreso->id_ingresos_egresos; 
                $datos_pago->documento            = $request->input('documento');
                $datos_pago->numero_documento     = $request->input('numero_documento');
                $datos_pago->fecha_emision        = $request->input('fecha_emision');
                $datos_pago->id_cuentas_bancarias = $cuenta_bancaria->id_cuentas_bancarias;
                $datos_pago->save();

                // Devuelve ambos cuando es banco
                return response()->json([
                    'ingreso_egreso' => $ingreso_egreso,
                    'datos_pago'     => $datos_pago,
                ], 201);
            }

            // Retornar el recurso creado
            return response()->json($ingreso_egreso, 201);

              });
        }catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function anticipoCA(Request $request){

        // Aqui vamos a validar los datos (los campos calculados se asignan automáticamente)
        $request->validate([
            'fecha' => 'required|date',
            'identificacion' => 'required',
            'nombre' => 'required',
            'descripcion' => 'required',
            'monto' => 'required|numeric',
            'tipo' => 'required|in:caja,banco',
            'cuenta' => 'required',
            
            'documento' => 'required_if:tipo,banco',
            'numero_documento' => 'required_if:tipo,banco',
            'fecha_emision' => 'required_if:tipo,banco',
            'id_cuentas_bancarias' => 'required_if:tipo,banco',
        ], [
            'documento.required_if'            => 'El documento es obligatorio cuando el tipo es banco.',
            'numero_documento.required_if'     => 'El número de documento es obligatorio cuando el tipo es banco.',
            'fecha_emision.required_if'        => 'La fecha de emisión es obligatoria cuando el tipo es banco.',
            'id_cuentas_bancarias.required_if' => 'La cuenta bancaria es obligatoria cuando el tipo es banco.',
        ]);

        try{
            return DB::transaction(function () use ($request) {
            // Buscar la cuenta por su nombre
            $cuenta = cuentas::where('cuenta', 'Anticipo de compras y gastos')->where('id_clasificacion', '2')->where('id_proyectos', '2')->first();
            // Si la cuenta no se encuentra, devolver un error
            if (!$cuenta) {
                return response()->json(['error' => 'La cuenta proporcionada no existe'], 404);
            }

            // Verificar si la cuenta pertenece al id_proyectos 2
             if ($cuenta->id_proyectos != 2) {
                 return response()->json(['error' => 'La cuenta no pertenece al proyecto capilla'], 400);
             }

                 $tipo = strtolower($request->input('tipo'));

            // Crear un nuevo registro en la tabla ingresos_egresos
            $ingreso_egreso = new ingresos_egresos();
            $ingreso_egreso->fecha = $request->input('fecha');
            $ingreso_egreso->identificacion = $request->input('identificacion');
            $ingreso_egreso->nombre = $request->input('nombre');
            $ingreso_egreso->descripcion = $request->input('descripcion');
            $ingreso_egreso->monto = $request->input('monto');
            $ingreso_egreso->tipo = $tipo;
            $ingreso_egreso->id_cuentas = $cuenta->id_cuentas;

            // Asignar montos calculados automáticamente según la solicitud:
            // - monto_debe debe igualar el monto ingresado
            // - monto_haber queda en 0.00
            // - es_pendiente pasa a 1
            $ingreso_egreso->monto_debe = $request->input('monto');
            $ingreso_egreso->monto_haber = 0.00;
            $ingreso_egreso->es_pendiente = 1;

            // Guardar el ingreso/egreso
            $ingreso_egreso->save();

            // Obtener el id del ingreso/egreso recién creado
            $id_ingresos_egresos = $ingreso_egreso->id_ingresos_egresos;

              if ($tipo === 'banco') {
                $cuenta_bancaria = cuentas_bancarias::find($request->input('id_cuentas_bancarias'));
                if (!$cuenta_bancaria) {
                    throw new \RuntimeException('La cuenta bancaria indicada no existe.');
                }

                $datos_pago = new datos_de_pago_ingresos();
                $datos_pago->id_ingresos_egresos  = $ingreso_egreso->id_ingresos_egresos; 
                $datos_pago->documento            = $request->input('documento');
                $datos_pago->numero_documento     = $request->input('numero_documento');
                $datos_pago->fecha_emision        = $request->input('fecha_emision');
                $datos_pago->id_cuentas_bancarias = $cuenta_bancaria->id_cuentas_bancarias;
                $datos_pago->save();

                // Devuelve ambos cuando es banco
                return response()->json([
                    'ingreso_egreso' => $ingreso_egreso,
                    'datos_pago'     => $datos_pago,
                ], 201);
            }

            // Retornar el recurso creado
            return response()->json($ingreso_egreso, 201);

              });
        }catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
        
    }
}
