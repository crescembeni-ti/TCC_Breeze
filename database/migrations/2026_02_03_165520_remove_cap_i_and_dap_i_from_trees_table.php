<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('trees', function (Blueprint $table) {

            // 🔥 Remove as colunas erradas, se existirem
            if (Schema::hasColumn('trees', 'cap$i')) {
                $table->dropColumn('cap$i');
            }

            if (Schema::hasColumn('trees', 'dap$i')) {
                $table->dropColumn('dap$i');
            }

            // 🌱 Criar CAP2 até CAP20 (19 colunas)
            for ($i = 2; $i <= 20; $i++) {
                if (!Schema::hasColumn('trees', 'cap' . $i)) {
                    $table->decimal('cap' . $i, 10, 2)->nullable();
                }
            }

            // 🌳 Criar DAP1 até DAP20 (20 colunas)
            for ($i = 1; $i <= 20; $i++) {
                if (!Schema::hasColumn('trees', 'dap' . $i)) {
                    $table->decimal('dap' . $i, 10, 2)->nullable();
                }
            }
        });
    }

    public function down()
    {
        Schema::table('trees', function (Blueprint $table) {

            // Remove CAP2 → CAP20
            for ($i = 2; $i <= 20; $i++) {
                if (Schema::hasColumn('trees', 'cap' . $i)) {
                    $table->dropColumn('cap' . $i);
                }
            }

            // Remove DAP1 → DAP20
            for ($i = 1; $i <= 20; $i++) {
                if (Schema::hasColumn('trees', 'dap' . $i)) {
                    $table->dropColumn('dap' . $i);
                }
            }
        });
    }
};
