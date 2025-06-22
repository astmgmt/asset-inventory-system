<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{   
    public function up(): void
    {
        Schema::create('software_print_logs', function (Blueprint $table) {
            $table->id();
            $table->string('print_code')->unique();
            $table->date('date_from');
            $table->date('date_to');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->json('software_snapshot_data'); 
            $table->timestamps();
        });
    }   
    public function down(): void
    {
        Schema::dropIfExists('software_print_logs');
    }
};
