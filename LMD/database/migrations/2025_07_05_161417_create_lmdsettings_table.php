<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLmdsettingsTable extends Migration
{
    public function up()
    {
        Schema::create('lmdsettings', function (Blueprint $table) {
            $table->id();
            $table->decimal('order_charge', 8, 2)->default(1.00); // 1% default charge
            $table->decimal('tax_percentage', 8, 2)->default(0.00);         // Optional tax
            $table->decimal('pickup_radius_km', 8, 2)->default(0.5);      // 10 km default
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lmdsettings');
    }
}
