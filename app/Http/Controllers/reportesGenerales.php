<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\ingresos_egresos;
use App\Models\cuentas;

class reportesGenerales extends Controller
{
    // Reporte general para proyecto agrícola (id_proyectos = 1)
    public function reporteGeneralAgricola(Request $request)
    {
        $validated = $request->validate([
            'tipo' => 'required|string',
            'mes' => 'required|string|in:enero,febrero,marzo,abril,mayo,junio,julio,agosto,septiembre,octubre,noviembre,diciembre',
        ]);

        return $this->generateReport(1, $validated['tipo'], $validated['mes']);
    }

    // Reporte general para proyecto capilla (id_proyectos = 2)
    public function reporteGeneralCapilla(Request $request)
    {
        $validated = $request->validate([
            'tipo' => 'required|string',
            'mes' => 'required|string|in:enero,febrero,marzo,abril,mayo,junio,julio,agosto,septiembre,octubre,noviembre,diciembre',
        ]);

        return $this->generateReport(2, $validated['tipo'], $validated['mes']);
    }

    // Generador común del reporte, parametrizado por id_proyectos
    private function generateReport(int $projectId, string $tipo, string $mes)
    {
        try {
            // Variables comunes
            $añoActual = Carbon::now()->year;
            $fechaInicial = null;
            $fechaFinal = null;
            $fechaAnterior = null;

            // Helper para calcular saldos iniciales por tipo (bancos/caja)
            $calcIniciales = function ($fechaInicial) use ($projectId) {
                $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) use ($projectId) { $query->where('id_proyectos', $projectId); })
                    ->where('tipo', 'bancos')
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) use ($projectId) { $query->where('id_proyectos', $projectId); })
                    ->where('tipo', 'bancos')
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) use ($projectId) { $query->where('id_proyectos', $projectId); })
                    ->where('tipo', 'caja')
                    ->where('nomenclatura', 'like', 'IN%')
                    ->sum('monto');

                $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
                    ->whereHas('cuentas', function ($query) use ($projectId) { $query->where('id_proyectos', $projectId); })
                    ->where('tipo', 'caja')
                    ->where('nomenclatura', 'like', 'EG%')
                    ->sum('monto');

                $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
                $saldoInicialCaja = $ingresosAntCaja - $egresosAntCaja;
                $saldoInicial = $saldoInicialBancos + $saldoInicialCaja;

                return [$saldoInicialBancos, $saldoInicialCaja, $saldoInicial];
            };

            // Helper para agrupar y formatear datos por tipo (caja/bancos)
            $groupAndFormat = function ($collection) {
                return $collection->groupBy('cuentas.cuenta')->map(function ($group) {
                    $ingresos = $group->filter(function ($item) {
                        return strpos($item->nomenclatura, 'IN') === 0;
                    })->sum('monto');

                    $egresos = $group->filter(function ($item) {
                        return strpos($item->nomenclatura, 'EG') === 0;
                    })->sum('monto');

                    if ($ingresos > 0 || $egresos > 0) {
                        $result = ['cuenta' => $group->first()->cuentas->cuenta];
                        if ($ingresos > 0) $result['ingresos'] = number_format($ingresos, 2);
                        if ($egresos > 0) $result['egresos'] = number_format($egresos, 2);
                        return $result;
                    }
                })->filter()->values();
            };

            // Para cada tipo calculamos fechaInicial y fechaFinal; replicamos la lógica original
            if ($tipo === 'mensual') {
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

                $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));

                list($saldoInicialBancos, $saldoInicialCaja, $saldoInicial) = $calcIniciales($fechaInicial);

                $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) use ($projectId) { $query->where('id_proyectos', $projectId); })
                    ->get();

                $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) use ($projectId) { $query->where('id_proyectos', $projectId); })
                    ->get();

                $dataGroupedCaja = $groupAndFormat($dataCaja);
                $dataGroupedBancos = $groupAndFormat($dataBancos);

                $totalIngresosCaja = $dataCaja->filter(function ($item) { return strpos($item->nomenclatura, 'IN') === 0; })->sum('monto');
                $totalEgresosCaja = $dataCaja->filter(function ($item) { return strpos($item->nomenclatura, 'EG') === 0; })->sum('monto');
                $totalIngresosBancos = $dataBancos->filter(function ($item) { return strpos($item->nomenclatura, 'IN') === 0; })->sum('monto');
                $totalEgresosBancos = $dataBancos->filter(function ($item) { return strpos($item->nomenclatura, 'EG') === 0; })->sum('monto');

                $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

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
                ]);
            }

            // Trimestral
            if ($tipo === 'trimestral') {
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
                        return response()->json(['error' => 'Mes inválido'], 400);
                }

                $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));
                list($saldoInicialBancos, $saldoInicialCaja, $saldoInicial) = $calcIniciales($fechaInicial);

                $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) use ($projectId) { $query->where('id_proyectos', $projectId); })
                    ->get();

                $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) use ($projectId) { $query->where('id_proyectos', $projectId); })
                    ->get();

                $dataGroupedCaja = $groupAndFormat($dataCaja);
                $dataGroupedBancos = $groupAndFormat($dataBancos);

                $totalIngresosCaja = $dataCaja->filter(function ($item) { return strpos($item->nomenclatura, 'IN') === 0; })->sum('monto');
                $totalEgresosCaja = $dataCaja->filter(function ($item) { return strpos($item->nomenclatura, 'EG') === 0; })->sum('monto');
                $totalIngresosBancos = $dataBancos->filter(function ($item) { return strpos($item->nomenclatura, 'IN') === 0; })->sum('monto');
                $totalEgresosBancos = $dataBancos->filter(function ($item) { return strpos($item->nomenclatura, 'EG') === 0; })->sum('monto');

                $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

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
                ]);
            }

            // Semestral
            if ($tipo === 'semestral') {
                switch ($mes) {
                    case 'enero':
                        $fechaInicial = Carbon::createFromDate($añoActual - 1, 12, 31);
                        $fechaFinal = Carbon::createFromDate($añoActual, 6, 30);
                        break;
                    case 'julio':
                        $fechaInicial = Carbon::createFromDate($añoActual, 6, 30);
                        $fechaFinal = Carbon::createFromDate($añoActual, 12, 31);
                        break;
                    default:
                        return response()->json(['error' => 'Mes inválido'], 400);
                }

                $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));
                list($saldoInicialBancos, $saldoInicialCaja, $saldoInicial) = $calcIniciales($fechaInicial);

                $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) use ($projectId) { $query->where('id_proyectos', $projectId); })
                    ->get();

                $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) use ($projectId) { $query->where('id_proyectos', $projectId); })
                    ->get();

                $dataGroupedCaja = $groupAndFormat($dataCaja);
                $dataGroupedBancos = $groupAndFormat($dataBancos);

                $totalIngresosCaja = $dataCaja->filter(function ($item) { return strpos($item->nomenclatura, 'IN') === 0; })->sum('monto');
                $totalEgresosCaja = $dataCaja->filter(function ($item) { return strpos($item->nomenclatura, 'EG') === 0; })->sum('monto');
                $totalIngresosBancos = $dataBancos->filter(function ($item) { return strpos($item->nomenclatura, 'IN') === 0; })->sum('monto');
                $totalEgresosBancos = $dataBancos->filter(function ($item) { return strpos($item->nomenclatura, 'EG') === 0; })->sum('monto');

                $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

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
                ]);
            }

            // Anual
            if ($tipo === 'anual') {
                $fechaInicial = Carbon::createFromDate($añoActual - 1, 12, 31);
                $fechaFinal = Carbon::createFromDate($añoActual, 12, 31);
                $fechaAnterior = date('Y-m-d', strtotime($fechaInicial . ' -1 day'));

                list($saldoInicialBancos, $saldoInicialCaja, $saldoInicial) = $calcIniciales($fechaInicial);

                $dataCaja = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'caja')
                    ->whereHas('cuentas', function ($query) use ($projectId) { $query->where('id_proyectos', $projectId); })
                    ->get();

                $dataBancos = ingresos_egresos::with('cuentas')
                    ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                    ->where('tipo', 'bancos')
                    ->whereHas('cuentas', function ($query) use ($projectId) { $query->where('id_proyectos', $projectId); })
                    ->get();

                $dataGroupedCaja = $groupAndFormat($dataCaja);
                $dataGroupedBancos = $groupAndFormat($dataBancos);

                $totalIngresosCaja = $dataCaja->filter(function ($item) { return strpos($item->nomenclatura, 'IN') === 0; })->sum('monto');
                $totalEgresosCaja = $dataCaja->filter(function ($item) { return strpos($item->nomenclatura, 'EG') === 0; })->sum('monto');
                $totalIngresosBancos = $dataBancos->filter(function ($item) { return strpos($item->nomenclatura, 'IN') === 0; })->sum('monto');
                $totalEgresosBancos = $dataBancos->filter(function ($item) { return strpos($item->nomenclatura, 'EG') === 0; })->sum('monto');

                $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
                $totalGeneralEgresos = $totalEgresosCaja + $totalEgresosBancos;

                $saldoFinal = ($saldoInicial + $totalGeneralIngresos) - $totalGeneralEgresos;
                $saldoFinalCaja = ($saldoInicialCaja + $totalIngresosCaja) - $totalEgresosCaja;
                $saldoFinalBancos = ($saldoInicialBancos + $totalIngresosBancos) - $totalEgresosBancos;

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
                ]);
            }

            return response()->json(['error' => 'Tipo inválido'], 400);

        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
}
