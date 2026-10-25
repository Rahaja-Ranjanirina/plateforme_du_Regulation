<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reponses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sondage_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // le visiteur
            $table->unsignedTinyInteger('sms');      // note sur 10
            $table->unsignedTinyInteger('voix');     // note sur 10
            $table->unsignedTinyInteger('internet'); // note sur 10
            $table->unsignedTinyInteger('credit');   // note sur 10
            $table->boolean('satisfait');            // 0 ou 1
            $table->timestamps();

            $table->unique(['sondage_id', 'user_id']); // une seule réponse par visiteur et par sondage
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reponses');
    }
};