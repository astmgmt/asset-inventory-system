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
        Schema::create('software_assignment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_batch_id')->constrained('software_assignment_batches')->onDelete('cascade');
            $table->foreignId('software_id')->constrained('software')->onDelete('cascade');
            $table->unsignedInteger('quantity');
            $table->enum('status', [
                'Assigned',                
                'Available',                                
                'PendingReturn',  
                'ReturnRejected'    
            ])->default('Assigned');
            $table->date('installation_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('software_assignment_items');
    }
};
