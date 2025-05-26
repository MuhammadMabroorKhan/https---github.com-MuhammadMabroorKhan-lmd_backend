<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  
    public function up()
    {
        // Step 1: Convert to string temporarily
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_status')->default('pending')->change();
        });

        // Step 2: Convert back to enum with updated values
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_status', [
                'pending',
                'confirmed_by_customer',
                'confirmed_by_deliveryboy',
                'confirmed_by_vendor'
            ])->default('pending')->change();
        });
    }

    public function down()
    {
        // Revert to original enum
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_status', [
                'paid',
                'pending',
                'failed'
            ])->default('pending')->change();
        });
    }
};

