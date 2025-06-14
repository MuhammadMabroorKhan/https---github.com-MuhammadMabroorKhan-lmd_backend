<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
      
    Schema::create('test_stocks', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('item_detail_ID'); // Reference to real items
    $table->integer('stock_qty')->default(0);
    $table->timestamp('last_updated')->useCurrent()->useCurrentOnUpdate();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_stocks');
    }
};
