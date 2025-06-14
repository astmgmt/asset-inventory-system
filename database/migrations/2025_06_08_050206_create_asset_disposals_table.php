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
            $table->enum('method', [
                'sold',
                'scrapped',
                'donated',
                'recycled',
                'traded_in',
                'returned',
                'transferred',
                'lost_stolen'
            ])->nullable();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->string('document_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected']);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('reason');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
   
    public function down(): void
    {
        Schema::dropIfExists('asset_disposals');
    }
};
