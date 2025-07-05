<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up() {
        Schema::create('itemattributes', function (Blueprint $table) {
            $table->id();
            $table->string('key'); 
            $table->string('value'); 
            $table->unsignedBigInteger('itemdetail_id'); 
        });
    }

    public function down() {
        Schema::dropIfExists('itemattributes');
    }
};
