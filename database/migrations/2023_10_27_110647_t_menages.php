<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('t_menages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code_menage');
            $table->string('adresse_actuel');
            $table->integer('taille');
            $table->string('habitation');
            $table->string('origine');
            $table->foreignUuid('critereid')->constrained('t_critere_vulnerable');
            $table->timestamps();
            $table->boolean('status')->default(false);
            $table->boolean('deleted')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('t_menages');
    }
};
