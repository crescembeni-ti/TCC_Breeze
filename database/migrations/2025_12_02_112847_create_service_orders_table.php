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
    Schema::create('service_orders', function (Blueprint $table) {
        $table->id();
        // Liga a OS ao Contato (Solicitação)
        $table->foreignId('contact_id')->constrained()->onDelete('cascade');
        
        // Dados da OS
        $table->date('data_vistoria')->nullable();
        $table->date('data_execucao')->nullable();
        
        // Campos JSON para salvar as listas de checkbox
        $table->json('motivos')->nullable();       // Risco, Conflito...
        $table->json('servicos')->nullable();      // Poda, Remoção...
        $table->json('equipamentos')->nullable();  // Motosserra, EPI...
        $table->json('procedimentos')->nullable(); // Segurança, Sinalização...
        
        $table->text('observacoes')->nullable();
        
        // Quem gerou a OS (Analista)
        $table->foreignId('supervisor_id')->nullable()->constrained('analyst');
        
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};
