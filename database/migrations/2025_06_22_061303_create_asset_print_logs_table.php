<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up(): void
    {
        Schema::create('asset_print_logs', function (Blueprint $table) {
            $table->id();
            $table->string('print_code', 30)->unique();
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->json('asset_snapshot_data'); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_print_logs');
    }
};
