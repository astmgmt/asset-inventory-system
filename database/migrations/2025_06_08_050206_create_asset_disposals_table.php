<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_disposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->foreignId('disposed_by')->constrained('users')->onDelete('cascade');
            $table->date('disposal_date');
            $table->text('reason');
            $table->timestamps();
        });
    }
   
    public function down(): void
    {
        Schema::dropIfExists('asset_disposals');
    }
};
