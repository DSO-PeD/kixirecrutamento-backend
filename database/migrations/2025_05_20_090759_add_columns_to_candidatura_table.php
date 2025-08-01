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
        Schema::table('candidatura', function (Blueprint $table) {
            $table->foreignId('minimo_experiencia')->nullable()->constrained('opcao','id');
            $table->foreignId('certificacao')->nullable()->constrained('opcao','id');
            $table->foreignId('capacidade_elaborar_planos')->nullable()->constrained('opcao','id');
            $table->foreignId('capacidade_redigir_relatorio')->nullable()->constrained('opcao','id');
            $table->foreignId('capacidade_avaliar')->nullable()->constrained('opcao','id');
            $table->foreignId('analise_processo')->nullable()->constrained('opcao','id');
            $table->foreignId('dominio_normas')->nullable()->constrained('opcao','id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidatura', function (Blueprint $table) {
            $table->dropForeign(['minimo_experiencia']);
            $table->dropForeign(['certificacao']);
            $table->dropForeign(['capacidade_elaborar_planos']);
            $table->dropForeign(['capacidade_redigir_relatorio']);
            $table->dropForeign(['capacidade_avaliar']);
            $table->dropForeign(['analise_processo']);
            $table->dropForeign(['dominio_normas']);
    
            $table->dropColumn([
                'minimo_experiencia',
                'certificacao',
                'capacidade_elaborar_planos',
                'capacidade_redigir_relatorio',
                'capacidade_avaliar',
                'analise_processo',
                'dominio_normas'
            ]);
        });
    }
};
