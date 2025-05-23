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
            $table->string('title_en')->nullable(); // English title for the notification
            $table->text('body');
            $table->text('body_en')->nullable(); // English body for the notification
            $table->json('data')->nullable(); // Additional data for the notification
            $table->enum('type', ['1', '2', '3']); // Type of notification
            $table->boolean('is_read')->default(false); // Flag to check if the notification is read
            $table->timestamp('read_at')->nullable(); // Timestamp when the notification was read
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};
