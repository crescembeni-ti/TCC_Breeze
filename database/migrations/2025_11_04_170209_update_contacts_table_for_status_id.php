<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            // Remove a coluna 'status' antiga, se ela existir
            if (Schema::hasColumn('contacts', 'status')) {
                $table->dropColumn('status');
            }

            // 1. ADICIONA A NOVA CHAVE ESTRANGEIRA 'status_id'
            $table->foreignId('status_id')
                  ->nullable()
                  ->after('user_id')
                  ->constrained('statuses') // Aponta para a tabela 'statuses'
                  ->onDelete('set null'); // Se um status for apagado, fica nulo

            // 2. ADICIONA A COLUNA 'justificativa' (para indeferido)
            $table->text('justificativa')->nullable()->after('descricao');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
            $table->dropColumn('status_id');
            $table->dropColumn('justificativa');
            $table->string('status')->default('novo'); // Recria a coluna antiga
        });
    }
};