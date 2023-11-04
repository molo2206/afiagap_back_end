<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('t_menage_critere', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('menageid')->constrained('t_menages')->nullable();
            $table->foreignUuid('cretereid')->constrained('t_critere_vulnerable')->nullable();
            $table->timestamps();
            $table->boolean('status')->default(false);
            $table->boolean('deleted')->default(false);
        });
    }

    public function down()
    {
        Schema::dropIfExists('t_menage_critere');
    }
};
