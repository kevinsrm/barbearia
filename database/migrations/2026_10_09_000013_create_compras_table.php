<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('compras')) {
            return;
        }

        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fornecedor_id')->nullable()->constrained('fornecedores')->nullOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->dateTime('data')->index();
            $table->decimal('valor_total', 10, 2)->default(0);
            $table->string('forma_pagamento', 30)->default('boleto')->index();
            $table->string('status', 20)->default('recebida')->index();
            $table->unsignedSmallInteger('prazo_pagamento')->default(30);
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
