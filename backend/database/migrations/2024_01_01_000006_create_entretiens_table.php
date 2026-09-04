<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entretiens', function (Blueprint $table) {
            $table->smallIncrements('id_entretien');
            $table->dateTime('date_entretien');
            $table->string('type_entretien', 30);       // Téléphonique / Visio / Présentiel
            $table->string('resultat_entretien', 30)->nullable(); // En attente / Réussi / Échoué
            $table->string('lien_visio', 255)->nullable();
            $table->text('remarque')->nullable();
            $table->unsignedSmallInteger('id_candidature')->unique(); // 0..1 relation
            $table->unsignedSmallInteger('id_admin');
            $table->timestamps();
            $table->foreign('id_candidature')->references('id_candidature')->on('candidatures')->onDelete('cascade');
            $table->foreign('id_admin')->references('id_admin')->on('admins')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entretiens');
    }
};
