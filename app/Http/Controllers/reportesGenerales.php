<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\ingresos_egresos;
use App\Models\cuentas;

class reportesGenerales extends Controller
{

   public function reporteGeneralAgricola(Request $request)
    {
        try {
            $idProyecto = 1;

            $validated = $request->validate([
                'tipo' => 'required|string|in:mensual,trimestral,semestral,anual',

                'mes' => 'nullable|string|in:enero,febrero,marzo,abril,mayo,junio,julio,agosto,septiembre,octubre,noviembre,diciembre',
                'year' => 'nullable|integer|min:2000|max:2100',

                'fecha_inicio' => 'nullable|date',
                'fecha_fin'    => 'nullable|date|after_or_equal:fecha_inicio',
            ]);

            $tipo = $validated['tipo'];

            if ($tipo === 'anual') {
                if (empty($validated['fecha_inicio']) || empty($validated['fecha_fin'])) {
                    return response()->json([
                        'error' => 'Para ANUAL debes enviar fecha_inicio y fecha_fin'
                    ], 400);
                }

                $fechaInicial = Carbon::parse($validated['fecha_inicio']);
                $fechaFinal   = Carbon::parse($validated['fecha_fin']);

                return $this->generateReportByFechas($idProyecto, $fechaInicial, $fechaFinal);
            }

        
            if (empty($validated['mes'])) {
                return response()->json([
                    'error' => 'El mes es obligatorio para este período'
                ], 400);
            }

            $mes  = $validated['mes'];
            $year = $validated['year'] ?? Carbon::now()->year;

            return $this->generateReportBalanceAgricola($idProyecto, $tipo, $mes, $year);

        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    private function generateReportBalanceAgricola($idProyecto, $tipo, $mes, $year)
    {
        $fechaInicial = null;
        $fechaFinal   = null;

        switch ($tipo) {
            case 'mensual':
                switch ($mes) {
                    case 'enero':
                        $fechaInicial = Carbon::createFromDate($year - 1, 12, 31);
                        $fechaFinal   = Carbon::createFromDate($year, 1, 31);
                        break;
                    case 'febrero':
                        $fechaInicial = Carbon::createFromDate($year, 1, 31);
                        $fechaFinal   = Carbon::createFromDate($year, 2, Carbon::create($year, 2, 1)->daysInMonth);
                        break;
                    case 'marzo':
                        $fechaInicial = Carbon::createFromDate($year, 2, Carbon::create($year, 2, 1)->daysInMonth);
                        $fechaFinal   = Carbon::createFromDate($year, 3, 31);
                        break;
                    case 'abril':
                        $fechaInicial = Carbon::createFromDate($year, 3, 31);
                        $fechaFinal   = Carbon::createFromDate($year, 4, 30);
                        break;
                    case 'mayo':
                        $fechaInicial = Carbon::createFromDate($year, 4, 30);
                        $fechaFinal   = Carbon::createFromDate($year, 5, 31);
                        break;
                    case 'junio':
                        $fechaInicial = Carbon::createFromDate($year, 5, 31);
                        $fechaFinal   = Carbon::createFromDate($year, 6, 30);
                        break;
                    case 'julio':
                        $fechaInicial = Carbon::createFromDate($year, 6, 30);
                        $fechaFinal   = Carbon::createFromDate($year, 7, 31);
                        break;
                    case 'agosto':
                        $fechaInicial = Carbon::createFromDate($year, 7, 31);
                        $fechaFinal   = Carbon::createFromDate($year, 8, 31);
                        break;
                    case 'septiembre':
                        $fechaInicial = Carbon::createFromDate($year, 8, 31);
                        $fechaFinal   = Carbon::createFromDate($year, 9, 30);
                        break;
                    case 'octubre':
                        $fechaInicial = Carbon::createFromDate($year, 9, 30);
                        $fechaFinal   = Carbon::createFromDate($year, 10, 31);
                        break;
                    case 'noviembre':
                        $fechaInicial = Carbon::createFromDate($year, 10, 31);
                        $fechaFinal   = Carbon::createFromDate($year, 11, 30);
                        break;
                    case 'diciembre':
                        $fechaInicial = Carbon::createFromDate($year, 11, 30);
                        $fechaFinal   = Carbon::createFromDate($year, 12, 31);
                        break;
                }
                break;

            case 'trimestral':
                switch ($mes) {
                    case 'enero':
                        $fechaInicial = Carbon::createFromDate($year - 1, 12, 31);
                        $fechaFinal   = Carbon::createFromDate($year, 3, 31);
                        break;
                    case 'abril':
                        $fechaInicial = Carbon::createFromDate($year, 3, 31);
                        $fechaFinal   = Carbon::createFromDate($year, 6, 30);
                        break;
                    case 'julio':
                        $fechaInicial = Carbon::createFromDate($year, 6, 30);
                        $fechaFinal   = Carbon::createFromDate($year, 9, 30);
                        break;
                    case 'octubre':
                        $fechaInicial = Carbon::createFromDate($year, 9, 30);
                        $fechaFinal   = Carbon::createFromDate($year, 12, 31);
                        break;
                }
                break;

            case 'semestral':
                if ($mes === 'enero') {
                    $fechaInicial = Carbon::createFromDate($year - 1, 12, 31);
                    $fechaFinal   = Carbon::createFromDate($year, 6, 30);
                } elseif ($mes === 'julio') {
                    $fechaInicial = Carbon::createFromDate($year, 6, 30);
                    $fechaFinal   = Carbon::createFromDate($year, 12, 31);
                }
                break;
        }

        if (!$fechaInicial || !$fechaFinal) {
            return response()->json(['error' => 'No se pudo calcular el rango de fechas'], 400);
        }

        return $this->generateReportByFechas($idProyecto, $fechaInicial, $fechaFinal);
    }
    private function generateReportByFechas($idProyecto, $fechaInicial, $fechaFinal)
    {
        $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
            ->whereHas('cuentas', fn($q) => $q->where('id_proyectos', $idProyecto))
            ->where('tipo', 'caja')
            ->where('nomenclatura', 'like', 'IN%')
            ->sum('monto');

        $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
            ->whereHas('cuentas', fn($q) => $q->where('id_proyectos', $idProyecto))
            ->where('tipo', 'caja')
            ->where('nomenclatura', 'like', 'EG%')
            ->sum('monto');

        $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
            ->whereHas('cuentas', fn($q) => $q->where('id_proyectos', $idProyecto))
            ->where('tipo', 'bancos')
            ->where('nomenclatura', 'like', 'IN%')
            ->sum('monto');

        $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
            ->whereHas('cuentas', fn($q) => $q->where('id_proyectos', $idProyecto))
            ->where('tipo', 'bancos')
            ->where('nomenclatura', 'like', 'EG%')
            ->sum('monto');

        $saldoInicialCaja   = $ingresosAntCaja - $egresosAntCaja;
        $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
        $saldoInicial       = $saldoInicialCaja + $saldoInicialBancos;

        $cuentasProyecto = cuentas::where('id_proyectos', $idProyecto)->get();

        $dataCaja = $cuentasProyecto->map(function ($cuenta) use ($fechaInicial, $fechaFinal) {
            $ing = ingresos_egresos::where('id_cuentas', $cuenta->id_cuentas)
                ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                ->where('tipo', 'caja')
                ->where('nomenclatura', 'like', 'IN%')
                ->sum('monto');

            $eg = ingresos_egresos::where('id_cuentas', $cuenta->id_cuentas)
                ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                ->where('tipo', 'caja')
                ->where('nomenclatura', 'like', 'EG%')
                ->sum('monto');

            return [
                'cuenta'      => $cuenta->cuenta,
                'tipo_cuenta' => $cuenta->tipo_cuenta ?? null,
                'corriente'   => $cuenta->corriente ?? null,
                'ingresos'    => number_format($ing, 2, '.', ''),
                'egresos'     => number_format($eg, 2, '.', ''),
            ];
        })->values();

        $dataBancos = $cuentasProyecto->map(function ($cuenta) use ($fechaInicial, $fechaFinal) {
            $ing = ingresos_egresos::where('id_cuentas', $cuenta->id_cuentas)
                ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                ->where('tipo', 'bancos')
                ->where('nomenclatura', 'like', 'IN%')
                ->sum('monto');

            $eg = ingresos_egresos::where('id_cuentas', $cuenta->id_cuentas)
                ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
                ->where('tipo', 'bancos')
                ->where('nomenclatura', 'like', 'EG%')
                ->sum('monto');

            return [
                'cuenta'      => $cuenta->cuenta,
                'tipo_cuenta' => $cuenta->tipo_cuenta ?? null,
                'corriente'   => $cuenta->corriente ?? null,
                'ingresos'    => number_format($ing, 2, '.', ''),
                'egresos'     => number_format($eg, 2, '.', ''),
            ];
        })->values();

        $totalIngresosCaja   = $dataCaja->sum(fn($x) => (float)$x['ingresos']);
        $totalEgresosCaja    = $dataCaja->sum(fn($x) => (float)$x['egresos']);
        $totalIngresosBancos = $dataBancos->sum(fn($x) => (float)$x['ingresos']);
        $totalEgresosBancos  = $dataBancos->sum(fn($x) => (float)$x['egresos']);

        $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
        $totalGeneralEgresos  = $totalEgresosCaja + $totalEgresosBancos;
        $saldoFinalCaja   = $saldoInicialCaja + $totalIngresosCaja - $totalEgresosCaja;
        $saldoFinalBancos = $saldoInicialBancos + $totalIngresosBancos - $totalEgresosBancos;
        $saldoFinal       = $saldoFinalCaja + $saldoFinalBancos;

        return response()->json([
            'id_proyecto'              => $idProyecto,
            'fecha_inicial'            => $fechaInicial->toDateString(),
            'fecha_final'              => $fechaFinal->toDateString(),

            'saldo_inicial'            => $saldoInicial,
            'saldo_inicial_caja'       => $saldoInicialCaja,
            'saldo_inicial_bancos'     => $saldoInicialBancos,

            'total_general_ingresos'   => $totalGeneralIngresos,
            'total_ingresos_caja'      => $totalIngresosCaja,
            'total_ingresos_bancos'    => $totalIngresosBancos,

            'total_general_egresos'    => $totalGeneralEgresos,
            'total_egresos_caja'       => $totalEgresosCaja,
            'total_egresos_bancos'     => $totalEgresosBancos,

            'data_caja'                => $dataCaja,
            'data_bancos'              => $dataBancos,

            'total_saldo_final'        => $saldoFinal,
            'total_saldo_final_caja'   => $saldoFinalCaja,
            'total_saldo_final_bancos' => $saldoFinalBancos,
        ], 200);
    }

    // --------------------CAPILLA----------------------


   // ✅ Reporte general para proyecto capilla (id_proyectos = 2)
public function reporteGeneralCapilla(Request $request)
{
    try {
        $idProyecto = 2;

        $validated = $request->validate([
            'tipo' => 'required|string|in:mensual,trimestral,semestral,anual',

            'mes'  => 'nullable|string|in:enero,febrero,marzo,abril,mayo,junio,julio,agosto,septiembre,octubre,noviembre,diciembre',
            'year' => 'nullable|integer|min:2000|max:2100',

            'fecha_inicio' => 'nullable|date',
            'fecha_fin'    => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        $tipo = $validated['tipo'];

        // ✅ ANUAL → usa fechas (NO mes)
        if ($tipo === 'anual') {

            if (empty($validated['fecha_inicio']) || empty($validated['fecha_fin'])) {
                return response()->json([
                    'error' => 'Para ANUAL debes enviar fecha_inicio y fecha_fin'
                ], 400);
            }

            $fechaInicial = Carbon::parse($validated['fecha_inicio']);
            $fechaFinal   = Carbon::parse($validated['fecha_fin']);

            return $this->generateReportCapillaByFechas($idProyecto, $fechaInicial, $fechaFinal);
        }

        // ✅ Mensual/Trimestral/Semestral → mes + year obligatorios
        if (empty($validated['mes'])) {
            return response()->json([
                'error' => 'El mes es obligatorio para este período'
            ], 400);
        }

        $mes  = $validated['mes'];
        $year = $validated['year'] ?? Carbon::now()->year;

        return $this->generateReportBalanceCapilla($idProyecto, $tipo, $mes, $year);

    } catch (\Throwable $th) {
        return response()->json(['error' => $th->getMessage()], 500);
    }
}


// ✅ Calcula fechas según mensual/trimestral/semestral + mes + year
private function generateReportBalanceCapilla($idProyecto, $tipo, $mes, $year)
{
    $fechaInicial = null;
    $fechaFinal   = null;

    switch ($tipo) {

        case 'mensual':
            switch ($mes) {
                case 'enero':
                    $fechaInicial = Carbon::createFromDate($year - 1, 12, 31);
                    $fechaFinal   = Carbon::createFromDate($year, 1, 31);
                    break;
                case 'febrero':
                    $fechaInicial = Carbon::createFromDate($year, 1, 31);
                    $fechaFinal   = Carbon::createFromDate($year, 2, Carbon::create($year, 2, 1)->daysInMonth);
                    break;
                case 'marzo':
                    $fechaInicial = Carbon::createFromDate($year, 2, Carbon::create($year, 2, 1)->daysInMonth);
                    $fechaFinal   = Carbon::createFromDate($year, 3, 31);
                    break;
                case 'abril':
                    $fechaInicial = Carbon::createFromDate($year, 3, 31);
                    $fechaFinal   = Carbon::createFromDate($year, 4, 30);
                    break;
                case 'mayo':
                    $fechaInicial = Carbon::createFromDate($year, 4, 30);
                    $fechaFinal   = Carbon::createFromDate($year, 5, 31);
                    break;
                case 'junio':
                    $fechaInicial = Carbon::createFromDate($year, 5, 31);
                    $fechaFinal   = Carbon::createFromDate($year, 6, 30);
                    break;
                case 'julio':
                    $fechaInicial = Carbon::createFromDate($year, 6, 30);
                    $fechaFinal   = Carbon::createFromDate($year, 7, 31);
                    break;
                case 'agosto':
                    $fechaInicial = Carbon::createFromDate($year, 7, 31);
                    $fechaFinal   = Carbon::createFromDate($year, 8, 31);
                    break;
                case 'septiembre':
                    $fechaInicial = Carbon::createFromDate($year, 8, 31);
                    $fechaFinal   = Carbon::createFromDate($year, 9, 30);
                    break;
                case 'octubre':
                    $fechaInicial = Carbon::createFromDate($year, 9, 30);
                    $fechaFinal   = Carbon::createFromDate($year, 10, 31);
                    break;
                case 'noviembre':
                    $fechaInicial = Carbon::createFromDate($year, 10, 31);
                    $fechaFinal   = Carbon::createFromDate($year, 11, 30);
                    break;
                case 'diciembre':
                    $fechaInicial = Carbon::createFromDate($year, 11, 30);
                    $fechaFinal   = Carbon::createFromDate($year, 12, 31);
                    break;
            }
            break;

        case 'trimestral':
            switch ($mes) {
                case 'enero':
                    $fechaInicial = Carbon::createFromDate($year - 1, 12, 31);
                    $fechaFinal   = Carbon::createFromDate($year, 3, 31);
                    break;
                case 'abril':
                    $fechaInicial = Carbon::createFromDate($year, 3, 31);
                    $fechaFinal   = Carbon::createFromDate($year, 6, 30);
                    break;
                case 'julio':
                    $fechaInicial = Carbon::createFromDate($year, 6, 30);
                    $fechaFinal   = Carbon::createFromDate($year, 9, 30);
                    break;
                case 'octubre':
                    $fechaInicial = Carbon::createFromDate($year, 9, 30);
                    $fechaFinal   = Carbon::createFromDate($year, 12, 31);
                    break;
            }
            break;

        case 'semestral':
            if ($mes === 'enero') {
                $fechaInicial = Carbon::createFromDate($year - 1, 12, 31);
                $fechaFinal   = Carbon::createFromDate($year, 6, 30);
            } elseif ($mes === 'julio') {
                $fechaInicial = Carbon::createFromDate($year, 6, 30);
                $fechaFinal   = Carbon::createFromDate($year, 12, 31);
            }
            break;
    }

    if (!$fechaInicial || !$fechaFinal) {
        return response()->json(['error' => 'No se pudo calcular el rango de fechas'], 400);
    }

    return $this->generateReportCapillaByFechas($idProyecto, $fechaInicial, $fechaFinal);
}


// ✅ Generador REAL del reporte Capilla (NO TOCA TU FORMATO, devuelve saldo/data/totales)
private function generateReportCapillaByFechas($idProyecto, $fechaInicial, $fechaFinal)
{
    // 🔥 saldos iniciales antes del rango
    $ingresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
        ->whereHas('cuentas', fn($q) => $q->where('id_proyectos', $idProyecto))
        ->where('tipo', 'caja')
        ->where('nomenclatura', 'like', 'IN%')
        ->sum('monto');

    $egresosAntCaja = ingresos_egresos::where('fecha', '<', $fechaInicial)
        ->whereHas('cuentas', fn($q) => $q->where('id_proyectos', $idProyecto))
        ->where('tipo', 'caja')
        ->where('nomenclatura', 'like', 'EG%')
        ->sum('monto');

    $ingresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
        ->whereHas('cuentas', fn($q) => $q->where('id_proyectos', $idProyecto))
        ->where('tipo', 'bancos')
        ->where('nomenclatura', 'like', 'IN%')
        ->sum('monto');

    $egresosAntBancos = ingresos_egresos::where('fecha', '<', $fechaInicial)
        ->whereHas('cuentas', fn($q) => $q->where('id_proyectos', $idProyecto))
        ->where('tipo', 'bancos')
        ->where('nomenclatura', 'like', 'EG%')
        ->sum('monto');

    $saldoInicialCaja   = $ingresosAntCaja - $egresosAntCaja;
    $saldoInicialBancos = $ingresosAntBancos - $egresosAntBancos;
    $saldoInicial       = $saldoInicialCaja + $saldoInicialBancos;

    // ✅ cuentas del proyecto
    $cuentasProyecto = cuentas::where('id_proyectos', $idProyecto)->get();

    // ✅ data de caja
    $dataCaja = $cuentasProyecto->map(function ($cuenta) use ($fechaInicial, $fechaFinal) {
        $ing = ingresos_egresos::where('id_cuentas', $cuenta->id_cuentas)
            ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
            ->where('tipo', 'caja')
            ->where('nomenclatura', 'like', 'IN%')
            ->sum('monto');

        $eg = ingresos_egresos::where('id_cuentas', $cuenta->id_cuentas)
            ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
            ->where('tipo', 'caja')
            ->where('nomenclatura', 'like', 'EG%')
            ->sum('monto');

        return [
            'cuenta'      => $cuenta->cuenta,
            'tipo_cuenta' => $cuenta->tipo_cuenta ?? null,
            'corriente'   => $cuenta->corriente ?? null,
            'ingresos'    => number_format($ing, 2, '.', ''),
            'egresos'     => number_format($eg, 2, '.', ''),
        ];
    })->values();

    // ✅ data de bancos
    $dataBancos = $cuentasProyecto->map(function ($cuenta) use ($fechaInicial, $fechaFinal) {
        $ing = ingresos_egresos::where('id_cuentas', $cuenta->id_cuentas)
            ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
            ->where('tipo', 'bancos')
            ->where('nomenclatura', 'like', 'IN%')
            ->sum('monto');

        $eg = ingresos_egresos::where('id_cuentas', $cuenta->id_cuentas)
            ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
            ->where('tipo', 'bancos')
            ->where('nomenclatura', 'like', 'EG%')
            ->sum('monto');

        return [
            'cuenta'      => $cuenta->cuenta,
            'tipo_cuenta' => $cuenta->tipo_cuenta ?? null,
            'corriente'   => $cuenta->corriente ?? null,
            'ingresos'    => number_format($ing, 2, '.', ''),
            'egresos'     => number_format($eg, 2, '.', ''),
        ];
    })->values();

    // ✅ totales
    $totalIngresosCaja   = $dataCaja->sum(fn($x) => (float)$x['ingresos']);
    $totalEgresosCaja    = $dataCaja->sum(fn($x) => (float)$x['egresos']);
    $totalIngresosBancos = $dataBancos->sum(fn($x) => (float)$x['ingresos']);
    $totalEgresosBancos  = $dataBancos->sum(fn($x) => (float)$x['egresos']);

    $totalGeneralIngresos = $totalIngresosCaja + $totalIngresosBancos;
    $totalGeneralEgresos  = $totalEgresosCaja + $totalEgresosBancos;

    $saldoFinalCaja   = $saldoInicialCaja + $totalIngresosCaja - $totalEgresosCaja;
    $saldoFinalBancos = $saldoInicialBancos + $totalIngresosBancos - $totalEgresosBancos;
    $saldoFinal       = $saldoFinalCaja + $saldoFinalBancos;

    return response()->json([
        'id_proyecto'              => $idProyecto,
        'fecha_inicial'            => $fechaInicial->toDateString(),
        'fecha_final'              => $fechaFinal->toDateString(),

        'saldo_inicial'            => $saldoInicial,
        'saldo_inicial_caja'       => $saldoInicialCaja,
        'saldo_inicial_bancos'     => $saldoInicialBancos,

        'total_general_ingresos'   => $totalGeneralIngresos,
        'total_ingresos_caja'      => $totalIngresosCaja,
        'total_ingresos_bancos'    => $totalIngresosBancos,

        'total_general_egresos'    => $totalGeneralEgresos,
        'total_egresos_caja'       => $totalEgresosCaja,
        'total_egresos_bancos'     => $totalEgresosBancos,

        'data_caja'                => $dataCaja,
        'data_bancos'              => $dataBancos,

        'total_saldo_final'        => $saldoFinal,
        'total_saldo_final_caja'   => $saldoFinalCaja,
        'total_saldo_final_bancos' => $saldoFinalBancos,
    ], 200);
}

}
