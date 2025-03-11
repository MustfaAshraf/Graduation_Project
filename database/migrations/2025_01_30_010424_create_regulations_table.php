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
            $table->string('regulation');
            $table->string('lectures_tables');
            $table->string('academic_guide');
            $table->string('teams_guide');
            $table->string('postgraduate_guide');
            $table->enum('role', ['1', '2', '3','4','5']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('regulations');
    }
};
