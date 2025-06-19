<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_return_items', function (Blueprint $table) {
            $table->id();

            $table->string('return_code'); // e.g. RT-20250611-00000001

            $table->foreignId('borrow_item_id')->constrained('asset_borrow_items')->onDelete('cascade');

            $table->foreignId('returned_by_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('returned_by_department_id')->nullable()->constrained('departments')->nullOnDelete();

            $table->timestamp('returned_at')->nullable(); // When the user submitted the return request
            $table->text('remarks')->nullable();

            // Admin approval-related fields
            $table->enum('approval_status', ['Pending', 'Approved', 'Rejected','Returned','Borrowed'])->default('Pending');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_return_items');
    }
};
