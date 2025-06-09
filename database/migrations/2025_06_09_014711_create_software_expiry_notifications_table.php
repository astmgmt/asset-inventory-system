<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('software_expiry_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('software_id')->constrained('software')->onDelete('cascade');
            $table->date('notify_date');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('notification_id')->constrained('notifications');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('software_expiry_notifications');
    }
};
