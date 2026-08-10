<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('regras_disponibilidade', 'almoco_inicio')) {
            return;
        }

        Schema::table('regras_disponibilidade', function (Blueprint $table) {
            $table->time('almoco_inicio')->nullable()->after('horario_fim');
            $table->time('almoco_fim')->nullable()->after('almoco_inicio');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('regras_disponibilidade', 'almoco_inicio')) {
            return;
        }

        Schema::table('regras_disponibilidade', function (Blueprint $table) {
            $table->dropColumn(['almoco_inicio', 'almoco_fim']);
        });
    }
};
