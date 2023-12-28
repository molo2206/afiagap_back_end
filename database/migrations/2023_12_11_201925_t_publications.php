<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('t_publications', function (Blueprint $table){
            $table->uuid('id')->primary();
            $table->text('title_projet')->nullable();
            $table->text('content')->nullable();
            $table->string('auteur')->nullable();
            $table->text('image')->nullable();
            $table->string('legend')->nullable();
            $table->text('url')->nullable();
            $table->enum('verify',['oui','non']);
            $table->boolean('status')->default(false);
            $table->boolean('delete')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('t_publications');
    }
};
