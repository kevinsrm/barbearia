<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pessoas')) {
            return;
        }

        Schema::create('pessoas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('tipo_pessoa', 20)->default('cliente')->index();
            $table->string('cpf', 20)->nullable()->unique();
            $table->string('telefone', 30)->nullable()->index();
            $table->string('email')->nullable()->unique();
            $table->date('data_nascimento')->nullable();
            $table->string('endereco')->nullable();
            $table->string('numero', 20)->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();
            $table->string('cep', 15)->nullable();
            $table->text('observacoes')->nullable();
            $table->boolean('ativo')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pessoas');
    }
};
