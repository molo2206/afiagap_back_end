<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('t_projets', function (Blueprint $table){
            $table->uuid('id')->primary();
            $table->string('title_projet');
            $table->foreignUuid('provinceid')->constrained('t_province');
            $table->foreignUuid('zoneid')->constrained('t_zone');
            $table->foreignUuid('airid')->constrained('t_aire_sante');
            $table->foreignUuid('orgid')->constrained('t_organisation');
            $table->foreignUuid('org_make_repport')->constrained('t_organisation');
            $table->foreignUuid('org_make_oeuvre')->constrained('t_organisation');
            $table->string('identifiant_project');
            $table->string('typeprojetid');
            $table->string('type_intervention');
            $table->string('axe_strategique');
            $table->string('odd');
            $table->string('description_activite');
            $table->string('statut_activite');
            $table->string('modalite');
            $table->string('src_financement');
            $table->string('cohp_relais');
            $table->date('date_debut_projet');
            $table->date('date_fin_projet');
            $table->string('type_benef');
            $table->timestamps();
            $table->boolean('etat_top')->default(false);
            $table->boolean('status')->default(false);
            $table->boolean('deleted')->default(false);
        });
    }

    public function down()
    {
        Schema::dropIfExists('t_projets');
    }
};
