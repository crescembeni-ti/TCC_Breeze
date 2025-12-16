<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('tarefas', function (Blueprint $table) {
    $table->id();
    
    // CORREÇÃO AQUI: Use foreignId()
    $table->foreignId('os_id')
          ->constrained('order_service') // Referencia a tabela 'oss'
          ->onDelete('cascade');
    
    // CORREÇÃO AQUI: Use foreignId() para o user_id também
    $table->foreignId('user_id')
          ->constrained('users') // Referencia a tabela 'users'
          ->onDelete('cascade');
    
    // ... outras colunas ...
    $table->string('titulo'); 
    $table->text('descricao');
    $table->string('status')->default('pendente');
    $table->date('data_prevista')->nullable();
    
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarefas');
    }
};
