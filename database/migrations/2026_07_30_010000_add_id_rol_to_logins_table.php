<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logins', function (Blueprint $table) {
            $table->integer('id_rol')->default(2)->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('logins', function (Blueprint $table) {
            $table->dropColumn('id_rol');
        });
    }
};
