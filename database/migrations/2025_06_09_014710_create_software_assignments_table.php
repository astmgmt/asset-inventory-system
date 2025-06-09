<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('software_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('software_id')->constrained('software');
            $table->foreignId('assigned_to_user_id')->constrained('users');
            $table->date('assigned_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('software_assignments');
    }
};
