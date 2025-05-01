<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('regulations', function (Blueprint $table) {
            $table->id();
            $table->string('regulation')->nullable();
            $table->string('lectures_tables')->nullable();
            $table->string('academic_guide')->nullable();
            $table->string('teams_guide')->nullable();
            $table->string('postgraduate_guide')->nullable();
            $table->string('ai_regulation')->nullable();
            $table->string('cybersecurity_regulation')->nullable();
            $table->string('medical_regulation')->nullable();
            $table->enum('role', ['1', '2', '3','4','5','6','7','8'])->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('regulations');
    }
};
