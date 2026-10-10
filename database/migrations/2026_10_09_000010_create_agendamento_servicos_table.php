<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('agendamento_servicos')) {
            return;
        }

        Schema::create('agendamento_servicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agendamento_id')->constrained('agendamentos')->cascadeOnDelete();
            $table->foreignId('servico_id')->constrained('servicos')->cascadeOnDelete();
            $table->decimal('valor', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['agendamento_id', 'servico_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agendamento_servicos');
    }
};
