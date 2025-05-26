<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
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



    public function down()
    {
        // You can revert to the previous state if needed
        DB::statement("ALTER TABLE orders MODIFY status ENUM(
            'pending', 
            'ready', 
            'completed', 
            'canceled', 
            'processing', 
            'assigned', 
            'picked_up'
        ) DEFAULT 'pending'");
    }
};

