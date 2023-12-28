<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('t_images_publications', function (Blueprint $table){
            $table->uuid('id')->primary();
            $table->foreignUuid('publicationid')->constrained('t_publications');
            $table->text('name')->nullable();
            $table->string('legend')->nullable();
            $table->boolean('status')->default(false);
            $table->boolean('deleted')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('t_images_publications');
    }
};
