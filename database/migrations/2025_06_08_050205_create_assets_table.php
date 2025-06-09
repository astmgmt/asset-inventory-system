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
            $table->string('asset_code', 20)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->integer('quantity');
            $table->string('serial_number', 20)->unique();
            $table->string('model_number', 50);
            $table->foreignId('category_id')->constrained('asset_categories');
            $table->foreignId('condition_id')->constrained('asset_conditions');
            $table->foreignId('location_id')->constrained('asset_locations');
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->date('warranty_expiration');
            $table->boolean('is_disposed')->default(false);
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
