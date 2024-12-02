<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('image');
            $table->string('instructor');
            $table->decimal('price', 8, 2);
            $table->unsignedInteger('ratings_count')->default(0); // عدد التقييمات
            $table->decimal('ratings_sum', 10, 2)->default(0); // مجموع التقييمات
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('courses');
    }
};
