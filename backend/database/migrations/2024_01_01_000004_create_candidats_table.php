<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidats', function (Blueprint $table) {
            $table->smallIncrements('id_candidat');
            $table->string('nom_complet', 100);
            $table->string('email', 100)->unique();
            $table->string('telephone', 20);
            $table->string('cv', 255);                          // chemin vers le fichier CV
            $table->text('lettre_motivation')->nullable();
            $table->string('linkedin_url', 255)->nullable();
            $table->string('portfolio_url', 255)->nullable();
            $table->text('note')->nullable();                   // note interne admin
            $table->date('date_inscription')->useCurrent();
            $table->unsignedSmallInteger('id_admin')->nullable();
            $table->timestamps();
            $table->foreign('id_admin')->references('id_admin')->on('admins')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidats');
    }
};
