<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('first_term')->nullable();
            $table->string('second_term')->nullable();
            $table->string('third_term')->nullable();
            $table->string('fourth_term')->nullable();
            $table->string('fifth_term')->nullable();
            $table->string('sixth_term')->nullable();
            $table->string('seventh_term')->nullable();
            $table->string('eighth_term')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('expenses');
    }
};
