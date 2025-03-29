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
        // Schema::create('xyz', function (Blueprint $table) {
        //     $table->id();  // Creates an auto-incrementing primary key
        //     $table->string('name', 255);
        //     $table->integer('age');
        //     $table->string('gender', 20);
        //     $table->string('city', 255);
        //     $table->timestamps();  // Adds created_at and updated_at columns
        // });

        Schema::create('XYZ', function (Blueprint $table) {
            $table->id(); // This automatically creates an auto-incrementing 'ID' column
            $table->string('name');
            $table->integer('age');
            $table->string('gender');
            $table->string('city');
            $table->timestamps();
        });
        


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xyz');
    }
};

// use Illuminate\Database\Migrations\Migration;
// use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Support\Facades\Schema;

// return new class extends Migration
// {
//     /**
//      * Run the migrations.
//      */
//     public function up(): void
//     {
//         Schema::create('xyz', function (Blueprint $table) {
//             $table->id();
//             $table->timestamps();
//         });
//     }

//     /**
//      * Reverse the migrations.
//      */
//     public function down(): void
//     {
//         Schema::dropIfExists('xyz');
//     }
// };
