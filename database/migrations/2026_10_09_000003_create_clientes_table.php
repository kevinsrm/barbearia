<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('clientes')) {
            return;
        }

        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pessoa_id')->unique()->constrained('pessoas')->cascadeOnDelete();
            $table->string('codigo_cliente', 30)->nullable()->unique();
            $table->unsignedSmallInteger('pontuacao')->default(0);
            $table->string('status', 20)->default('ativo')->index();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
