<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_qrcode_log_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_qrcode_log_id')->constrained('asset_qrcode_logs')->onDelete('cascade');
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('asset_qrcode_log_assets');
    }
};
