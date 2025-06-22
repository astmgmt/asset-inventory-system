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
        Schema::create('software_assignment_batches', function (Blueprint $table) {
            $table->id();
            $table->string('assignment_no')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('cascade');            
            $table->string('purpose');
            $table->text('remarks')->nullable();
            $table->enum('status', [
                'Assigned',                
                'Available',                                
                'PendingReturn',  
                'ReturnRejected'    
            ])->default('Assigned');
            $table->timestamp('date_assigned')->useCurrent();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('software_assignment_batches');
    }
};
