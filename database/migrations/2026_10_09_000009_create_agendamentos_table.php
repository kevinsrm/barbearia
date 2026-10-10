<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('agendamentos')) {
            return;
        }

        Schema::create('agendamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('funcionario_id')->nullable()->constrained('funcionarios')->cascadeOnDelete();
            // Dados de contato usados quando o agendamento vem do site e o cliente ainda não tem cadastro.
            $table->string('cliente_nome')->nullable();
            $table->string('cliente_telefone', 30)->nullable();
            $table->date('data')->index();
            $table->string('hora', 5)->index();
            $table->unsignedSmallInteger('duracao')->default(30);
            $table->string('status', 20)->default('pendente')->index();
            $table->string('origem', 20)->default('painel');
            $table->decimal('valor_total', 10, 2)->default(0);
            $table->decimal('desconto', 10, 2)->default(0);
            $table->text('observacoes')->nullable();
            $table->timestamps();

            $table->index(['funcionario_id', 'data']);
            $table->index(['data', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agendamentos');
    }
};
