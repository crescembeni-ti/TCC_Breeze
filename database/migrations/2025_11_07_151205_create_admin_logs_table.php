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
    Schema::create('admin_logs', function (Blueprint $table) {
        $table->id();
        // Salva o ID do admin que fez a ação
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        // Salva a ação (ex: "create_tree", "delete_user")
        $table->string('action'); 
        // Salva a descrição (ex: "Admin Maurício cadastrou a árvore Ipê Amarelo")
        $table->string('description'); 
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_logs');
    }
};
