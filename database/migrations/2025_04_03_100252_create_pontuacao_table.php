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
        Schema::create('pontuacao', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('ponto');
            $table->unsignedBigInteger('pergunta_id');
            $table->unsignedBigInteger('vaga_id');
            $table->unsignedBigInteger('opcao_id')->nullable();
            $table->foreign('pergunta_id')->references('id')->on('perguntas')->onDelete('cascade');
            $table->foreign('opcao_id')->references('id')->on('opcao')->onDelete('cascade');
            $table->foreign('vaga_id')->references('id')->on('vagas')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pontuacao');
    }
};
