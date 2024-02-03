<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('t_detail_projet_vaccines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('autreid')->constrained('t_autres_projet');
            $table->foreignUuid('typevaccinid')->constrained('t_type_vaccins');
            $table->integer('nbr_vaccine')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('t_detail_projet_vaccines');
    }
};
