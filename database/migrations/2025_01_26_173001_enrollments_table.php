<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('national_id');
            $table->string('id_photo_f')->nullable();
            $table->string('id_photo_b')->nullable();
            $table->string('nomination_card_photo')->nullable();
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('enrollments');


    }
};
