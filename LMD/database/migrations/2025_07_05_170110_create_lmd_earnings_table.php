<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLmdEarningsTable extends Migration
{
    public function up()
    {
        Schema::create('lmd_earnings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('suborder_id')->unique(); // Unique per suborder
            $table->decimal('order_total', 10, 2);               // Suborder total
            $table->decimal('lmd_charge_percentage', 5, 2);      // e.g. 1.00 = 1%
            $table->decimal('lmd_earning_amount', 10, 2);        // LMD's share
            $table->decimal('tax_percentage', 5, 2)->default(0); // Optional tax %
            $table->decimal('tax_amount', 10, 2)->default(0);    // Tax on earning
            $table->timestamps(0); // 👈 Automatically managed by Laravel (created_at, updated_at)
        });
    }

    public function down()
    {
        Schema::dropIfExists('lmd_earnings');
    }
}

