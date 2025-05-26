<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('delivery_tracking', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
    
    public function down()
    {
        Schema::table('delivery_tracking', function (Blueprint $table) {
            $table->enum('status', ['picked_up', 'delivered'])->default('picked_up');
        });
    }
    
};
