<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penalty_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('penalty_id')->constrained('penalties')->onDelete('cascade');
            $table->foreignId('paid_by_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('paid_by_department_id')->nullable()->constrained('departments')->onDelete('set null');

            $table->decimal('amount_paid', 10, 2);
            $table->timestamp('paid_at')->nullable();
            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penalty_payments');
    }
};
