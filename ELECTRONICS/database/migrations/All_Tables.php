<?php
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\DeliveryTracking;
use App\Models\ItemRating;
use Illuminate\Http\Request;

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
         // Restaurant Table
    Schema::create('restaurant', function (Blueprint $table) {
        $table->id();
        $table->string('name', 255);
        $table->string('description',255)->nullable();
        $table->string('location', 255)->nullable();
        $table->string('phone_number', 20)->nullable();
        $table->string('email', 255)->nullable();
        $table->enum('status', ['online', 'offline'])->default('online');
    });

    Schema::create('restaurant_images', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('restaurant_id'); // Foreign key to restaurants table
        $table->string('image_path', 255); // Path to the image file
        $table->string('description', 255)->nullable(); // Optional description for the image
       });

          // Create itemcategories table
          Schema::create('itemcategories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // E.g., "Beverages", "Snacks", "Fast Food"
        });
    
        // Create items table
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Name of the item (e.g., "Burger", "Pizza")
            $table->text('description')->nullable(); // General description of the item
            $table->unsignedBigInteger('category_ID')->nullable();
            $table->unsignedBigInteger('restaurant_ID')->nullable();
        });



        Schema::create('itemdetails', function (Blueprint $table) {
            $table->id();
            $table->string('variation_name'); // Name of the variation (e.g., "Small", "Large")
            $table->decimal('cost', 10, 2); // Price of the variation
            $table->string('additional_info',255)->nullable(); // Any additional information about the item
            $table->string('photo')->nullable(); // Image for the variation
            $table->enum('status', ['active', 'inactive'])->default('active'); // Add status
             $table->enum('timesensitive', ['Yes', 'No'])->default('No');
            $table->integer('preparation_time')->default(0);
            $table->unsignedBigInteger('item_ID'); // Links to Items table
        });
    
        // Create stock table
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_detail_ID'); // Links to Item Details table
            $table->integer('stock_qty')->default(0); // Stock quantity
            $table->timestamp('last_updated')->useCurrent()->useCurrentOnUpdate(); // Tracks last update
        });

     

    // Orders Table
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->dateTime('order_date')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->decimal('total_amount', 10, 2);
        $table->enum('status', ['pending', 'ready','delivered','in_transit','handover_confirmed','assigned','picked_up','completed' ,'canceled','processing'])->default('pending');
        $table->string('delivery_address',255)->nullable();
        $table->enum('payment_status', ['confirmed_by_customer','confirmed_by_vendor', 'pending', 'confirmed_by_deliveryboy'])->default('pending');
        $table->enum('payment_method', ['credit_card', 'cash', 'paypal'])->default('cash');
        $table->string('order_type',255)->nullable();
    });

    Schema::create('delivery_tracking', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('order_id')->nullable();
        $table->unsignedBigInteger('delivery_boy_id');
        $table->string('delivery_boy_name')->nullable();
        $table->string('delivery_boy_contact')->nullable();
         $table->string('delivery_boy_image')->nullable();
        $table->timestamps();
    });
    
    // Order Details Table
    Schema::create('orderdetails', function (Blueprint $table) {
        $table->id();
        $table->integer('qty');
        $table->decimal('unit_price', 10, 2);
        $table->decimal('subtotal', 10, 2);
        $table->unsignedBigInteger('order_id');
        $table->unsignedBigInteger('item_detail_id');
    });

    // Ratings Table
    Schema::create('itemrating', function (Blueprint $table) {
        $table->id();
        $table->decimal('stars',10,2);
        $table->string('comments',255)->nullable();
        $table->dateTime('ratingdate')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->unsignedBigInteger('order_ID');
        $table->unsignedBigInteger('item_detail_ID');
    });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant');
        Schema::dropIfExists('restaurant_images');
        Schema::dropIfExists('itemcategories');
        Schema::dropIfExists('items');
        Schema::dropIfExists('itemdetails');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('orderdetails');
        Schema::dropIfExists('itemrating');
        Schema::dropIfExists('stocks');
        Schema::dropIfExists('delivery_tracking');
    }
};
