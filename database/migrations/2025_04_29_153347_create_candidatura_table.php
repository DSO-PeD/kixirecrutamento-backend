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
        Schema::create('candidatura', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('email');
            $table->foreignId('onde_viu_vaga')->constrained('opcao','id');
            $table->date('nascimento');
            $table->foreignId('genero')->constrained('opcao','id');
            $table->string('numero_bilhete')->unique();
            $table->string('anexo_foto');
            $table->string('anexo_bilhete');
            $table->string('anexo_cv');
            $table->string('links_profissional')->nullable();
            $table->string('telefone1',9);
            $table->string('telefone2',9);
            $table->string('morada');
            $table->foreignId('grau_academico')->constrained('opcao','id');
            $table->string('area_formacao');
            $table->foreignId('ingles')->constrained('opcao','id');
            $table->string('referencias');
            $table->string('experiencias')->nullable();
            $table->foreignId('trabalho_actual')->constrained('opcao','id');
            $table->foreignId('word')->constrained('opcao','id')->nullable();
            $table->foreignId('excel')->constrained('opcao','id')->nullable();
            $table->foreignId('software_designer')->constrained('opcao','id')->nullable();
            $table->foreignId('primavera')->constrained('opcao','id')->nullable();
            $table->foreignId('vaga_id')->constrained('vagas','id');

            $table->foreignId('capacidade_monitoria_avaliacao')->constrained('opcao','id');
            $table->string('experiencia_gestao_dados');
            $table->string('experiencia_transformacao_digital');
            $table->string('experiencia_gestao_startups');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidatura');
    }
};
