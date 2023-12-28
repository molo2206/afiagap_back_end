<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('t_reponse_indicateur_projet', function (Blueprint $table){
            $table->uuid('id')->primary();
            $table->foreignUuid('projetid')->constrained('t_projets');
            $table->foreignUuid('indicateurid')->constrained('t_indicateur');
            $table->timestamps();
            $table->boolean('status')->default(false);
            $table->boolean('deleted')->default(false);
        });
    }

    public function down()
    {
        Schema::dropIfExists('t_reponse_indicateur_projet');
    }
};
