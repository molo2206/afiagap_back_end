<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('t_reponse_enceinte', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('questionid')->constrained('t_question_enceinte')->nullable();
            $table->foreignUuid('personneid')->constrained('t_personnes')->nullable();
            $table->enum('reponse', ['1', '0']);
            $table->timestamps();
            $table->boolean('status')->default(false);
            $table->boolean('deleted')->default(false);
        });
    }

    public function down()
    {
        Schema::dropIfExists('t_reponse_enceinte');
    }
};
