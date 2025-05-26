<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
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

    public function down()
    {
        // Optional: Revert back to original enum (without assigned and picked_up)
        DB::statement("ALTER TABLE orders MODIFY status ENUM(
            'pending', 
            'ready', 
            'completed', 
            'canceled', 
            'processing'
        ) DEFAULT 'pending'");
    }
};
