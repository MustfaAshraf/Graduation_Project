<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('timelines', function (Blueprint $table) {
            $table->id();
            $table->date('start_study'); // بداية الدراسة
            $table->date('end_registration'); // انتهاء مدة التسجيل والحذف
            $table->date('quiz_1'); // Quiz 1
            $table->date('mid_exam'); // امتحانات نصف الفصل
            $table->date('quiz_2'); // Quiz 2
            $table->date('oral_practical_exams'); // الامتحانات الشفهية والعملية
            $table->date('end_study'); // نهاية الدراسة
            $table->date('start_final_exams'); // بداية الامتحانات النظرية
            $table->date('end_final_exams'); // نهاية الامتحانات النظرية
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('timelines');
    }
};
