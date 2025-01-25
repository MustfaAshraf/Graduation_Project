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
            
            // English columns
            $table->string('title_en');
            $table->text('description_en');
            $table->string('instructor_en');
            $table->text('instructor_description_en');

            // Arabic columns
            $table->string('title_ar');
            $table->text('description_ar');
            $table->string('instructor_ar');
            $table->text('instructor_description_ar');
            
            // Common columns
            $table->string('image');
            $table->decimal('price', 8, 2);
            $table->unsignedInteger('ratings_count')->default(0); 
            $table->decimal('ratings_sum', 10, 2)->default(0);
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('courses');
    }
};
