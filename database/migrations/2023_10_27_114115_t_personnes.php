<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('t_personnes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nom');
            $table->string('postnom');
            $table->string('prenom');
            $table->enum('sexe', ['Masculin', 'Feminin']);
            $table->foreignUuid('roleid')->constrained('t_roles_menage');
            $table->foreignUuid('typepersonneid')->constrained('t_type_personnes');
            $table->string('nom_pere')->nullable();
            $table->string('nom_mere')->nullable();
            $table->string('probleme_sante')->nullable();
            $table->string('lieu_naissance')->nullable();
            $table->date('datenaiss');
            $table->enum('sous_moustiquaire', ['oui', 'non'])->nullable();
            $table->string('photo')->nullable();
            $table->string('bar_code')->nullable();
            $table->text('empreinte_digital')->nullable();
            $table->timestamps();
            $table->boolean('status')->default(false);
            $table->boolean('deleted')->default(false);
        });
    }
    public function down()
    {
        Schema::dropIfExists('t_personnes');
    }
};
