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
            $table->foreignId('provincia_candidatura')->nullable()->constrained('opcao','id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidatura', function (Blueprint $table) {
            $table->dropForeign(['provincia_candidatura']);
    
            $table->dropColumn([
                'provincia_candidatura'
            ]);
        });
    }
};
