<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{    
    public function up(): void
    {
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('activity_name'); // login, logout, borrow, return
            $table->string('status')->nullable(); // active, inactive
            $table->text('description')->nullable();
            $table->text('remarks')->nullable(); 

            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps(); 
        });
    }

   
    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};
