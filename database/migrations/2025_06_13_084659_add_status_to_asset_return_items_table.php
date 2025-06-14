<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('asset_return_items', function (Blueprint $table) {
            $table->enum('status', ['Pending', 'Approved', 'Rejected','Returned','Borrowed'])->default('Pending');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('asset_return_items', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('approved_by_user_id');
            $table->dropColumn('approved_at');
        });
    }
};
