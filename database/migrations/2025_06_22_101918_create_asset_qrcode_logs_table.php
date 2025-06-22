<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_qrcode_logs', function (Blueprint $table) {
            $table->id();
            $table->string('print_code')->unique(); // Format: QRC-YYYYMMDD-000001
            $table->date('date_from');
            $table->date('date_to');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->json('asset_snapshot_data'); // Snapshot of printed assets for tracking
            $table->timestamps();
        });
    }   
    public function down(): void
    {
        Schema::dropIfExists('asset_qrcode_logs');
    }
};
