<?php

namespace App\Utils;

use App\Models\cuentas; // Necesario si queremos saber el tipo de clasificación 

class CuentasPorPagarCobrar
{
    /**
     * Prepara los montos DEBE/HABER y el indicador de pendiente 
     * basándose en si la transacción es una Cuenta Pendiente.
     * * @param float $monto El monto total del flujo de efectivo.
     * @param string $tipoMovimiento El tipo de movimiento (ej: 'INGRESOS' o 'EGRESOS').
     * @param bool $esPendiente Indica si se debe tratar como Cuenta Pendiente (true) o como flujo final (false).
     * @return array Array asociativo con 'monto_debe', 'monto_haber', 'es_pendiente'.
     */
    public static function prepararMontosContables(float $monto, string $tipoMovimiento, bool $esPendiente)
    {
        $montoDebe = 0.00;
        $montoHaber = 0.00;
        
        // 1. Si NO es una cuenta pendiente, los campos DEBE/HABER se mantienen en cero.
        if (!$esPendiente) {
            return [
                'monto_debe' => $montoDebe,
                'monto_haber' => $montoHaber,
                'es_pendiente' => 0,
            ];
        }

        // 2. Si SÍ es una cuenta pendiente, aplicamos tu lógica:
        
        // INGRESOS (Dinero entra) y es Cta. por Cobrar -> Registra el pendiente en HABER (Pasivo)
        if (strtoupper($tipoMovimiento) === 'INGRESOS') {
            $montoHaber = $monto;
        } 
        // EGRESOS (Dinero sale) y es Cta. por Pagar -> Registra el pendiente en DEBE (Activo)
        elseif (strtoupper($tipoMovimiento) === 'EGRESOS') {
            $montoDebe = $monto;
        }

        // Devolvemos los montos preparados y la bandera 'es_pendiente' en 1 (true)
        return [
            'monto_debe' => $montoDebe,
            'monto_haber' => $montoHaber,
            'es_pendiente' => 1,
        ];
    }
}