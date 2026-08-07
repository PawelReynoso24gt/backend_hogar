<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingresos_egresos', function (Blueprint $table) {
            $table->boolean('estado')->default(1)->after('es_pendiente');
        });
    }

    public function down(): void
    {
        Schema::table('ingresos_egresos', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
    }
};
