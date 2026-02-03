<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trees', function (Blueprint $table) {
            for (\$i = 1; \$i <= 20; \$i++) {
                \$table->decimal("cap\$i", 10, 2)->nullable();
                \$table->decimal("dap\$i", 10, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('trees', function (Blueprint $table) {
            for (\$i = 1; \$i <= 20; \$i++) {
                \$table->dropColumn(["cap\$i", "dap\$i"]);
            }
        });
    }
};
