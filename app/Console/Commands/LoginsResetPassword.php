<?php

namespace App\Console\Commands;

use App\Models\logins;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;

class LoginsResetPassword extends Command
{
    protected $signature = 'logins:reset-password {usuario : Valor de la columna usuarios en la tabla logins}';

    protected $description = 'Re-encripta la contraseña de un login con el APP_KEY actual (pregunta la contraseña de forma oculta, no queda en el historial de la shell)';

    public function handle(): int
    {
        $usuario = $this->argument('usuario');

        $login = logins::where('usuarios', $usuario)->first();
        if (!$login) {
            $this->error("No existe ningún login con usuarios = '{$usuario}'");
            return self::FAILURE;
        }

        $password = $this->secret("Nueva contraseña para '{$usuario}'");
        $confirm = $this->secret('Confírmala de nuevo');

        if ($password !== $confirm) {
            $this->error('Las contraseñas no coinciden, no se guardó nada.');
            return self::FAILURE;
        }

        if ($password === '' || $password === null) {
            $this->error('La contraseña no puede estar vacía.');
            return self::FAILURE;
        }

        $login->contrasenias = Crypt::encryptString($password);
        $login->save();

        $this->info("Contraseña de '{$usuario}' re-encriptada con el APP_KEY actual.");
        return self::SUCCESS;
    }
}
