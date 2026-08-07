<?php

namespace App\Console\Commands;

use App\Models\logins;
use Illuminate\Console\Command;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Crypt;

class LoginsEncryptionAudit extends Command
{
    /**
     * --old-key puede repetirse para probar varias APP_KEY antiguas (formato base64:... igual que en .env).
     * --fix re-encripta con el APP_KEY actual cualquier fila que sí se pudo descifrar con una --old-key.
     */
    protected $signature = 'logins:encryption-audit
        {--old-key=* : APP_KEY(s) candidatas usadas anteriormente, formato base64:...}
        {--fix : Re-encripta con el APP_KEY actual las filas recuperadas con una --old-key}';

    protected $description = 'Detecta y opcionalmente repara contraseñas de logins cifradas con un APP_KEY distinto al actual (error "MAC invalid")';

    public function handle(): int
    {
        $oldKeys = $this->buildOldEncrypters();
        $fix = (bool) $this->option('fix');

        $ok = 0;
        $recovered = 0;
        $unrecoverable = [];

        foreach (logins::all() as $login) {
            try {
                Crypt::decryptString($login->contrasenias);
                $ok++;
                continue;
            } catch (DecryptException $e) {
                // sigue abajo: intentar con keys antiguas
            }

            $plain = null;
            foreach ($oldKeys as $label => $encrypter) {
                try {
                    $plain = $encrypter->decryptString($login->contrasenias);
                    break;
                } catch (DecryptException $e) {
                    continue;
                }
            }

            if ($plain === null) {
                $unrecoverable[] = $login->usuarios;
                continue;
            }

            $recovered++;
            if ($fix) {
                $login->contrasenias = Crypt::encryptString($plain);
                $login->save();
                $this->line("Reparado: {$login->usuarios}" . (isset($label) ? " (key: {$label})" : ''));
            } else {
                $this->line("Recuperable con --fix: {$login->usuarios}" . (isset($label) ? " (key: {$label})" : ''));
            }
        }

        $this->newLine();
        $this->info("OK con APP_KEY actual: {$ok}");
        $this->info(($fix ? 'Reparados' : 'Recuperables (usa --fix para aplicar)') . ": {$recovered}");

        if (count($unrecoverable) > 0) {
            $this->error('Irrecuperables (ninguna key probada los descifra), requieren reset de contraseña: ' . implode(', ', $unrecoverable));
        }

        return self::SUCCESS;
    }

    /**
     * @return array<string, Encrypter>
     */
    private function buildOldEncrypters(): array
    {
        $encrypters = [];
        foreach ($this->option('old-key') as $i => $key) {
            $key = trim($key);
            if (str_starts_with($key, 'base64:')) {
                $key = base64_decode(substr($key, 7));
            }
            $encrypters['old-key-' . ($i + 1)] = new Encrypter($key, config('app.cipher'));
        }

        return $encrypters;
    }
}
