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
        // 1. Carpetas (Projects)
        Schema::create('folders', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary(); // ID original de Make
            $table->string('name');
            $table->timestamps();
        });

        // 2. Escenarios
        Schema::create('scenarios', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name');
            $table->unsignedBigInteger('folder_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('set null');
        });

        // 3. Historial de Ejecuciones (Lo más importante)
        Schema::create('execution_logs', function (Blueprint $table) {
            $table->string('id')->primary(); // ID largo de Make
            $table->unsignedBigInteger('scenario_id');
            $table->integer('operations'); // Créditos gastados
            $table->decimal('cost', 10, 6)->default(0); // Costo calculado
            $table->integer('duration_ms')->default(0);
            $table->integer('status')->default(1); // 1: Success, 2: Warning, 3: Error
            $table->timestamp('executed_at'); // Fecha real de ejecución
            $table->timestamps();

            $table->foreign('scenario_id')->references('id')->on('scenarios')->onDelete('cascade');
            $table->index('executed_at'); // Para filtrar rápido por fechas
        });
        
        // 4. Configuración (Precio del plan)
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('execution_logs');
        Schema::dropIfExists('scenarios');
        Schema::dropIfExists('folders');
        Schema::dropIfExists('settings');
    }
};
