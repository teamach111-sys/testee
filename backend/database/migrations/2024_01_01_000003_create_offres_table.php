<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offres', function (Blueprint $table) {
            $table->smallIncrements('id_offre');
            $table->string('titre_offre', 100);
            $table->text('description_offre');
            $table->string('type_contrat', 20);
            $table->date('date_publication');
            $table->date('delai_candidature')->nullable();
            $table->timestamp('date_modification')->nullable();
            $table->text('competences_requises')->nullable();
            $table->text('avantages')->nullable();
            $table->string('localisation', 100)->nullable();
            $table->boolean('est_publiee')->default(true);
            $table->unsignedSmallInteger('id_departement');
            $table->unsignedSmallInteger('id_admin');
            $table->timestamps();
            $table->foreign('id_departement')->references('id_departement')->on('departements')->onDelete('restrict');
            $table->foreign('id_admin')->references('id_admin')->on('admins')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offres');
    }
};
