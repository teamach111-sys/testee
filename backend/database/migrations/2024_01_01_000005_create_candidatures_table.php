<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidatures', function (Blueprint $table) {
            $table->smallIncrements('id_candidature');
            $table->date('date_candidature');
            $table->string('statut_candidature', 30)->default('Nouveau');
            $table->text('remarque')->nullable();
            $table->unsignedSmallInteger('id_candidat');
            $table->unsignedSmallInteger('id_offre');
            $table->unsignedSmallInteger('id_admin')->nullable();
            $table->timestamps();
            $table->foreign('id_candidat')->references('id_candidat')->on('candidats')->onDelete('cascade');
            $table->foreign('id_offre')->references('id_offre')->on('offres')->onDelete('cascade');
            $table->foreign('id_admin')->references('id_admin')->on('admins')->onDelete('set null');
            $table->unique(['id_candidat', 'id_offre']); // un candidat ne postule qu'une fois par offre
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidatures');
    }
};
