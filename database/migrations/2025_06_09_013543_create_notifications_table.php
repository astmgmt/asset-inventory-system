<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_id')->constrained('notification_types')->onDelete('cascade');
            $table->text('message');
            $table->boolean('voice_alert')->default(false);
            $table->boolean('email_alert')->default(false);
            $table->boolean('sms_alert')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
