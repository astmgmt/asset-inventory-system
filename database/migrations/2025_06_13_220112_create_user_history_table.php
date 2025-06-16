<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('borrow_code')->nullable();
            $table->string('return_code')->nullable();
            $table->enum('status', [
                'Borrow Approved', 
                'Borrow Denied', 
                'Return Approved', 
                'Return Denied'
            ]);
            $table->json('borrow_data')->nullable();
            $table->json('return_data')->nullable();
            $table->timestamp('action_date');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_histories');
    }
};
