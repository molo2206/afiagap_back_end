<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('t_activite_projets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('projetid')->constrained('t_projets');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('t_activite_projets');
    }
    
};
