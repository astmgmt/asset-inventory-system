<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_borrow_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrow_transaction_id')->constrained('asset_borrow_transactions')->onDelete('cascade');

            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->unsignedInteger('quantity');
            $table->text('purpose')->nullable();
            $table->enum('status', [
                'PendingBorrowApproval',   
                'Borrowed',                
                'PendingReturnApproval',   
                'PartiallyReturned',
                'Returned',                
                'BorrowRejected',   
                'ReturnRejected'
            ])->default('Borrowed');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_borrow_items');
    }
};
