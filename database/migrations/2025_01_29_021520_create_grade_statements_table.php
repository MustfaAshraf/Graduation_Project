<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('grade_statements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('semester');
            $table->string('university_id');
            $table->string('purpose')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('grade_statements');
    }
};
