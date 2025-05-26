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
        $table->string('delivery_boy_image')->nullable()->after('delivery_boy_contact');
    });
}

public function down()
{
    Schema::table('delivery_tracking', function (Blueprint $table) {
        $table->dropColumn('delivery_boy_image');
    });
}
};
