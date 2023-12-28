<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('t_image_alert', function (Blueprint $table){
            $table->uuid('id')->primary();
            $table->foreignUuid('alertid')->constrained('t_gaps_bloc1')->nullable();
            $table->text('image')->nullable();
            $table->timestamps();
            $table->boolean('status')->default(false);
            $table->boolean('deleted')->default(false);
        });
    }
    public function down()
    {
        Schema::dropIfExists('t_image_alert');
    }
};
