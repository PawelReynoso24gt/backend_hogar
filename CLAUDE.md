# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

Laravel 10 (PHP 8.1) backend for "Hogar" — an accounting/bookkeeping API tracking income and expenses (`ingresos_egresos`), bank accounts, and financial reports across two organizational projects: **Agrícola (AG, `id_proyectos = 1`)** and **Capilla (CA, `id_proyectos = 2`)**. There is no frontend in this repo; it's consumed by a separate SPA client.

## Commands

```bash
composer install              # install PHP deps
npm install                   # install JS deps (Vite build only, no app frontend)

php artisan serve             # run dev server
php artisan test              # run tests (or: vendor/bin/phpunit)
php artisan test --filter=TestName   # run a single test
vendor/bin/pint                # format PHP code (Laravel Pint, installed but no custom pint.json)

php artisan migrate           # run migrations (see Database note below)
php artisan tinker            # REPL
```

No `composer test`/`lint` scripts are defined — use the artisan/vendor commands above directly. `tests/Feature` and `tests/Unit` only contain Laravel's default `ExampleTest.php` — there is no real project test suite to run or extend as a pattern.

## Architecture

### Routing: business logic lives in `routes/web.php`, not `api.php`

`routes/api.php` only defines `/api/authenticate`, `/api/user`, and `/api/me`. Every other endpoint (logins, proyectos, clasificacion, bancos, cuentas, ingresos/egresos, cuentas_bancarias, pago_pendientes, saldar_anticipos) is registered in `routes/web.php` under the `web` middleware group, wrapped in `Route::middleware('auth:sanctum')->group(...)`. When adding or looking for an endpoint, check `web.php` first.

### Auth is custom, not Laravel's default `User` model

- The `logins` model (`app/Models/logins.php`, table `logins`, PK `id_login`) is the Sanctum-authenticatable entity — `App\Models\User` exists but is unused scaffolding.
- Passwords are stored reversibly via `Crypt::encryptString`/`decryptString` (not hashed) — see `LoginController::authenticate`/`create`. This is intentional in this codebase (passwords are decrypted and displayed via `getByNombre`), not a bug to "fix" opportunistically.
- Roles are plain integers on `logins.id_rol` (1 = Administrador, 2 = Usuario normal, 3 = Auditor). Authorization is enforced ad hoc in each controller method via `$request->user()->id_rol` checks (e.g. `if ($request->user()->id_rol != 1) { ... 403 ... }`), not via middleware or policies.
- `App\Services\AuthorizationService` (bound to `AuthorizationServiceInterface` in `AppServiceProvider`) defines a `hasPermission($user, $permission)` capability map by role ID, but it is **not wired into most controllers** — most authorization is still the inline `id_rol` checks described above. Don't assume `hasPermission` is the enforced path unless you see it actually called.
- Several `LoginController` methods have comments flagging IDOR fixes/history (e.g. `getById`, `getByNombre`, `update`, `delete` all check that the requester is either role 1 or the resource owner) — preserve this ownership check pattern when touching these methods.

### No custom migrations — models map to a pre-existing schema

`database/migrations/` only contains Laravel's stock migrations (users, password resets, failed jobs, personal access tokens). All domain tables (`logins`, `proyectos`, `cuentas`, `cuentas_bancarias`, `ingresos_egresos`, `clasificacion`, `bancos`, `pago_pendientes`, `datos_de_pago_ingresos`, `datos_de_pago_egresos`, `roles`, `logs`) exist in the target database without corresponding migration files. Don't assume `php artisan migrate:fresh` will produce a working schema — the database is managed outside this repo's migrations.

### The AG/CA duplication pattern

Because there are exactly two projects (Agrícola and Capilla), most accounting logic in `ingresos_egresosController.php` and `reportesGenerales.php` is **duplicated per project** rather than parameterized: `getAllCuentasIngresoAG`/`getAllCuentasIngresoCA`, `createALLINAG`/`createALLINCA`, `getReporteFinalAG`/`getReporteFinalCA`, `reporteGeneralAgricola`/`reporteGeneralCapilla`, `anticipoAG`/`anticipoCA`, etc. When fixing a bug in one `*AG`/`*CA` method pair, check whether the same bug exists in its counterpart — they are usually near-identical copies, not shared code.

`ingresos_egresosController.php` is ~8,700 lines and is the largest and most central controller by far — it handles ledger entries, "libro diario" (journal), "partida contable" (accounting entries), pending-payment reconciliation (`pendienteSaldado`), and final/general reports (balance sheets by month/quarter/semester/year, built via date-range switch statements in `reportesGenerales.php`).

### Key domain models and relationships

- `cuentas` (chart of accounts) belongs to `clasificacion` (ingreso/egreso classification) and `proyectos` (AG/CA).
- `ingresos_egresos` (ledger transactions) belongs to `cuentas`; has many `datos_de_pago_ingresos`, `datos_de_pago_egresos`, and `pago_pendientes` (installment/pending payments on a debt).
- `App\Utils\CuentasPorPagarCobrar::prepararMontosContables()` is the shared helper for computing `monto_debe`/`monto_haber`/`es_pendiente` on a transaction — reuse it rather than reimplementing debit/credit logic.
- Controllers follow a uniform pattern: every public method wraps its body in `try { ... } catch (\Throwable $th) { return response()->json(['error' => $th->getMessage()], 500); }` and returns raw JSON (no API Resources/Transformers).

### Dead code to be aware of

- `app/Http/Controllers/rolesController.php` and its `roles` model exist but have no registered routes in `web.php` or `api.php` — role data is currently only read via the hardcoded `id_rol` integers on `logins`, not through this controller.
- The `logs` model (`app/Models/logs.php`) maps to a `logs` table but is never referenced anywhere in `app/` — there is no active audit-logging code path despite the table's presence in the schema.
