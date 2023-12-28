<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('t_gaps_appui', function (Blueprint $table){
            $table->uuid('id')->primary();
            $table->foreignUuid('gapid')->constrained('t_gaps_bloc1');
            $table->string('key')->nullable();
            $table->string('value')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->boolean('status')->default(false);
            $table->boolean('deleted')->default(false);
        });
    }

    public function down()
    {
        Schema::dropIfExists('t_gaps_appui');
    }
};
