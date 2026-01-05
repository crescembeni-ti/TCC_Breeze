<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('trees', function (Blueprint $table) {
        // Cria a coluna admin_id que pode ser nula (caso o adm seja excluído, não apaga a árvore)
        $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
        
        // OBS: Se a sua tabela de administradores se chamar 'users', mude 'admins' para 'users'.
    });
}

public function down()
{
    Schema::table('trees', function (Blueprint $table) {
        $table->dropForeign(['admin_id']);
        $table->dropColumn('admin_id');
    });
}
};
