<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{    
    public function up(): void
    {
        Schema::create('asset_borrow_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('borrow_code')->unique(); // e.g. BR-20250611-00000001

            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Borrower
            $table->foreignId('user_department_id')->constrained('departments')->onDelete('set null')->nullable();

            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('requested_by_department_id')->nullable()->constrained('departments')->onDelete('set null');

            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by_department_id')->nullable()->constrained('departments')->onDelete('set null');

            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');

            $table->timestamp('borrowed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_borrow_transactions');
    }
};
