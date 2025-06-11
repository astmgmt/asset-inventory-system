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

            $table->foreignId('borrow_item_id')->constrained('asset_borrow_items')->onDelete('cascade');
            $table->foreignId('returned_by_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('returned_by_department_id')->nullable()->constrained('departments')->onDelete('set null');

            $table->timestamp('returned_at')->nullable();
            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_return_items');
    }
};
