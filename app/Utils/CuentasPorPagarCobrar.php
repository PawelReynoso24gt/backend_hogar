<?php

namespace App\Utils;

class CuentasPorPagarCobrar
{
    /**
     * Prepara los montos DEBE/HABER y el indicador de pendiente 
     * basándose en si la transacción es una Cuenta Pendiente.
     *
     * @param float $monto El monto total del flujo de efectivo.
     * @param string $tipoMovimiento El tipo de movimiento (ej: 'INGRESOS' o 'EGRESOS').
     * @param bool $esPendiente Indica si se debe tratar como Cuenta Pendiente (true) o como flujo final (false).
     * @return array Array asociativo con 'monto_debe', 'monto_haber', 'es_pendiente'.
     */
    public static function prepararMontosContables(float $monto, string $tipoMovimiento, bool $esPendiente): array
    {
        // 1. Cláusula de guarda: si no es pendiente, retorna ceros de inmediato.
        if (!$esPendiente) {
            return [
                'monto_debe'   => 0.00,
                'monto_haber'  => 0.00,
                'es_pendiente' => 0,
            ];
        }

        // 2. Normalizamos el texto una sola vez.
        $movimiento = strtoupper($tipoMovimiento);

        // 3. Retornamos asignando directamente el monto según el tipo de movimiento.
        return [
            'monto_debe'   => $movimiento === 'EGRESOS' ? $monto : 0.00,
            'monto_haber'  => $movimiento === 'INGRESOS' ? $monto : 0.00,
            'es_pendiente' => 1,
        ];
    }
}