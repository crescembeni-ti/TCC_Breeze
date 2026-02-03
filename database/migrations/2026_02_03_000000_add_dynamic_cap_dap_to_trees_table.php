<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trees', function (Blueprint $table) {
            // A coluna 'cap' já existe na tabela original, então criamos apenas o dap1 correspondente a ela
            $table->decimal("dap1", 10, 2)->nullable()->after('cap');
            
            // Criamos as colunas a partir do 2 até o 20
            for ($i = 2; $i <= 20; $i++) {
                $table->decimal("cap$i", 10, 2)->nullable();
                $table->decimal("dap$i", 10, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('trees', function (Blueprint $table) {
            $table->dropColumn("dap1");
            for ($i = 2; $i <= 20; $i++) {
                $table->dropColumn(["cap$i", "dap$i"]);
            }
        });
    }
};
