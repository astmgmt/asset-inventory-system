<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->index('expiry_status');
        });
        
        Schema::table('software', function (Blueprint $table) {
            $table->index('expiry_status');
        });
    }

    public function down(): void
    {
        Schema::table('expiry_status', function (Blueprint $table) {
            //
        });
    }
};
