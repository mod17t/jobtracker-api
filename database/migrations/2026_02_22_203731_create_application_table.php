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
        Schema::create('applications', function (Blueprint $table) {
            //clé primaire
            $table->id();
            //clé de l'user qui à postuler.   application(anglais) = postuler(français)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // stocké le nom de l'entreprise dans laquelle on postule
            $table->string('company');
            // stocké l'intitulé du poste
            $table->string('position');
            // stocké le nom de la plateforme sur laquelle on a postulé
            $table->string('platform');
            // stocké la date d'envoi de la candidature | format: année/mois/jour
            $table->date('applied_at');
            // stocké le status de la demande 
            $table->enum('status', ['envoyee', 'relance', 'entretien', 'refus', 'acceptee'])->default('envoyee');
            // stocké la date de relance
            $table->date('follow_up_at')->nullable();
            // stoké l'url du lien
            $table->string('url')->nullable();
            // stocké les notes personnelles ou remarques
            $table->text('notes')->nullable();
            // localisation de l'offre
            $table->string('location')-> nullable();
            //salaire visé
            $table->integer('salary')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
