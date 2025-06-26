<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code', 30)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->integer('quantity');
            $table->integer('reserved_quantity')->default(0);
            $table->string('serial_number', 20)->unique()->nullable();
            $table->string('model_number', 50);
            $table->foreignId('category_id')->constrained('asset_categories');
            $table->foreignId('condition_id')->constrained('asset_conditions');
            $table->foreignId('location_id')->constrained('asset_locations');
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->date('warranty_expiration');
            $table->boolean('is_disposed')->default(false);
            $table->boolean('expiry_flag')->default(false);
            $table->enum('expiry_status', ['active', 'warning_3m', 'warning_2m', 'warning_1m', 'expired'])->default('active');
            $table->timestamp('last_notified_at')->nullable();
            $table->boolean('show_status')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
