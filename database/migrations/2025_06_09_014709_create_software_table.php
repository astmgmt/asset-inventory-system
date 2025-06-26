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
        Schema::create('software', function (Blueprint $table) {
            $table->id();
            $table->string('software_code', 50)->unique();
            $table->string('software_name', 100);
            $table->text('description')->nullable();
            $table->integer('quantity')->default(1);
            $table->integer('reserved_quantity')->default(0);
            $table->string('license_key', 100);
            $table->date('expiry_date');
            $table->foreignId('added_by')->constrained('users')->onDelete('cascade');
            $table->boolean('expiry_flag')->default(false);
            $table->enum('expiry_status', ['active', 'warning_3m', 'warning_2m', 'warning_1m', 'expired'])->default('active');
            $table->timestamp('last_notified_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('software');
    }
};
