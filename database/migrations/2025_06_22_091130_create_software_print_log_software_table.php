<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('software_print_log_software', function (Blueprint $table) {
            $table->id();
            $table->foreignId('software_print_log_id')->constrained('software_print_logs')->onDelete('cascade');
            $table->foreignId('software_id')->constrained('software')->onDelete('cascade');
            $table->timestamps();
        });
    }  
    public function down(): void
    {
        Schema::dropIfExists('software_print_log_software');
    }
};
