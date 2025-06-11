<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penalties', function (Blueprint $table) {
            $table->id();

            $table->foreignId('borrow_item_id')->constrained('asset_borrow_items')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('user_department_id')->nullable()->constrained('departments')->onDelete('set null');

            $table->decimal('amount', 10, 2);
            $table->string('reason')->nullable();
            $table->date('due_date')->nullable();
            $table->boolean('is_paid')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penalties');
    }
};
