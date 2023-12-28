<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_type_reponses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('name')->nullable();
            $table->timestamps();
            $table->boolean('status')->default(false);
            $table->boolean('deleted')->default(false);
        });
    }


    public function down()
    {
        Schema::dropIfExists('t_type_reponses');
    }
};
