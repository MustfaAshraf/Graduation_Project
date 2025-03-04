<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('device_token'); // Link notifications to users via device_token
            $table->string('title');
            $table->text('body');
            $table->json('data')->nullable(); // Additional data for the notification
            $table->timestamp('read_at')->nullable(); // Timestamp when the notification was read
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};
