<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Fix the enum: remove 'in_progress', add 'processing'
        DB::statement("ALTER TABLE orders MODIFY status ENUM(
            'pending', 
            'processing',
            'ready', 
            'assigned', 
            'picked_up',
            'handover_confirmed',
            'in_transit',
            'delivered',
            'completed', 
            'canceled'
        ) DEFAULT 'pending'");
    }

    public function down()
    {
        // Revert if needed: put back 'in_progress'
        DB::statement("ALTER TABLE orders MODIFY status ENUM(
            'pending', 
            'in_progress',
            'ready', 
            'assigned', 
            'picked_up',
            'handover_confirmed',
            'in_transit',
            'delivered',
            'completed', 
            'canceled'
        ) DEFAULT 'pending'");
    }
};

