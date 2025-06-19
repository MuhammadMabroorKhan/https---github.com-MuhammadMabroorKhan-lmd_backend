<?php

namespace App\Http\Controllers;

use App\Models\Resturant;
use App\Models\RestaurantImages;
use App\Models\Item;
use App\Models\ItemRating;
use App\Models\DeliveryTracking;
use App\Models\Category;
use App\Models\Order; // Import the Order model
use App\Models\OrderDetail; // Import the OrderDetail model
use App\Http\ResturantController\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
// use Illuminate\Support\Facades\Storage;
class ResturantController extends Controller
{
    
  // Receive an Order
  public function receiveOrder(Request $request)
  {

    \Log::info('Incoming Order Request:', $request->all());
      // Validate the incoming request data
      $request->validate([
          'total_amount' => 'nullable|numeric',
          'delivery_address' => 'sometimes|string',
          'order_type' => 'required|in:dine_in,takeaway,delivery,lmd,lmd/test',
          'order_details' => 'required|array',
          'order_details.*.item_detail_id' => 'required|',//exists:item_details,id',
          'order_details.*.qty' => 'required|numeric',
          'order_details.*.unit_price' => 'sometimes|numeric',
          'order_details.*.subtotal' => 'sometimes|numeric',
      ]);
  
      // Create the order
      $order = new Order();
      $order->total_amount = $request->input('total_amount');
      $order->status = 'pending'; // Initially pending
      $order->delivery_address = $request->input('delivery_address');
      $order->payment_status = 'pending'; // Payment status is always pending by default
      $order->payment_method = 'cash'; // Payment method is cash by default
      $order->order_type = $request->input('order_type');
      $order->save();
  
      // Add order details
      foreach ($request->input('order_details') as $item) {

          $itemDetailId = $item['item_detail_id'];
        $qty = $item['qty'];


          $orderDetail = new OrderDetail();
          $orderDetail->order_id = $order->id;
          $orderDetail->item_detail_id = $item['item_detail_id'];
          $orderDetail->qty = $item['qty'];
          $orderDetail->unit_price = $item['unit_price'];
          $orderDetail->subtotal = $item['subtotal'];
          $orderDetail->save();


           // Update stock based on order type
        if ($order->order_type === 'lmd') {
            DB::table('stocks')
                ->where('item_detail_ID', $itemDetailId)
                ->decrement('stock_qty', $qty);
        } elseif ($order->order_type === 'lmd/test') {
            DB::table('test_stocks')
                ->where('item_detail_ID', $itemDetailId)
                ->decrement('stock_qty', $qty);
        }
      }
  
      // Return a shortened response
    return response()->json([
        'id' => $order->id,
        'total_amount' => $order->total_amount,
        'status' => $order->status,
        'payment_status' => $order->payment_status,
    ], 201);
      //return response()->json(['message' => 'Order received successfully!', 'order' => $order], 201);
  }
  public function getKfcRatingAndReviews()
{
    // Calculate total reviews and average rating for all items in KFC
    $kfcRatings = DB::table('itemrating')
        ->join('itemdetails', 'itemrating.item_detail_ID', '=', 'itemdetails.id') // Join item details
        ->join('items', 'itemdetails.item_ID', '=', 'items.id') // Join items
        ->selectRaw('COUNT(itemrating.id) as total_reviews, COALESCE(AVG(itemrating.stars), 0) as average_rating')
        ->first();

    return response()->json([
        'total_reviews' => $kfcRatings->total_reviews, // Total number of reviews
        'average_rating' => round($kfcRatings->average_rating, 2) // Rounded average rating (out of 5)
    ]);
}




public function getItemRatingForOrder($orderId)
{
    $ratings = DB::table('itemrating')
        ->where('order_ID', $orderId)
        ->get();

    if ($ratings->isEmpty()) {
        return response()->json([
            'has_rated' => false,
            'message' => 'No ratings found for this order.'
        ]);
    }

    $baseUrl = url('storage'); // assuming images stored with public disk

    // Collect item_detail_IDs
    $itemDetailIds = $ratings->pluck('item_detail_ID')->toArray();

    // Fetch all related images for this order and those items
    $images = DB::table('rateditemimages')
        ->where('order_ID', $orderId)
        ->whereIn('item_detail_ID', $itemDetailIds)
        ->get()
        ->groupBy('item_detail_ID');

    // Format ratings
    $formattedRatings = $ratings->map(function ($rating) use ($images, $baseUrl) {
        $itemImages = $images->get($rating->item_detail_ID, collect());

        return [
            'item_detail_id' => $rating->item_detail_ID,
            'stars' => $rating->stars,
            'comments' => $rating->comments,
            'ratings_date' => $rating->ratingdate,
            'rated_images' => $itemImages->map(function ($img) use ($baseUrl) {
                return $baseUrl . '/' . $img->image_path;
            })->values()
        ];
    });

    return response()->json([
        'has_rated' => true,
        'ratings' => $formattedRatings
    ]);
}



public function addItemRating(Request $request): JsonResponse
{
    \Log::info('Incoming Request Data:', $request->all());

    $request->validate([
        'id' => 'required|exists:orders,id',
        'item_detail_id' => 'required|exists:itemdetails,id',
        'stars' => 'required|numeric|min:1|max:5',
        'comments' => 'nullable|string|max:255',
        'rated_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    $existingRating = DB::table('itemrating')
        ->where('order_ID', $request->id)
        ->where('item_detail_ID', $request->item_detail_id)
        ->first();

    if ($existingRating) {
        return response()->json([
            'success' => false,
            'status' => 409,
            'message' => 'This item is already rated for this order.',
            'error' => [
                'type' => 'DuplicateRating',
                'details' => [
                    'item_detail_id' => $request->item_detail_id,
                    'order_id' => $request->id,
                ]
            ]
        ], 409);
    }

    DB::table('itemrating')->insert([
        'order_ID' => $request->id,
        'item_detail_ID' => $request->item_detail_id,
        'stars' => $request->stars,
        'comments' => $request->comments,
        'ratingdate' => now(),
    ]);

    if ($request->hasFile('rated_images')) {
        foreach ($request->file('rated_images') as $image) {
            $path = $image->store('rated_item_images', 'public');
            DB::table('rateditemimages')->insert([
                'image_path' => $path,
                'order_ID' => $request->id,
                'item_detail_ID' => $request->item_detail_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    return response()->json([
        'success' => true,
        'status' => 200,
        'message' => 'Rating and images added successfully.',
        'data' => null
    ], 200);
}


  // Update Order Status
  public function updateOrderStatus(Request $request, $orderId)
  {
      // Validate the incoming status change
      $request->validate([
          'status' => 'required|in:processing,ready,completed,canceled',
      ]);

      // Find the order and update its status
      $order = Order::findOrFail($orderId);
      $order->status = $request->input('status');
      $order->save();

      return response()->json(['message' => 'Order status updated successfully!', 'order' => $order]);
  }

  public function getOrderStatus()
  {
      try {
          // Fetch all orders where order_type is 'lmd'
          $orders = Order::where('order_type', 'lmd')->get(['id', 'status', 'payment_status']);
  
          if ($orders->isEmpty()) {
              return response()->json([
                  'status' => 'error',
                  'message' => 'No orders found for LMD.',
              ], 404);
          }
  
          // Return the order statuses
          return response()->json([
              'status' => 'success',
              'orders' => $orders,
          ]);
      } catch (\Exception $e) {
          return response()->json([
              'status' => 'error',
              'message' => 'An error occurred while fetching the order statuses.',
          ], 500);
      }
  }
  


  // Mark Order as Ready for Delivery
  public function markOrderReady(Request $request, $orderId)
  {
      // Find the order and mark it as ready
      $order = Order::findOrFail($orderId);
      $order->status = 'ready';
      $order->save();

      return response()->json(['message' => 'Order marked as ready for delivery!', 'order' => $order]);
  }

  // Track Order Delivery Status
  public function trackDelivery(Request $request, $orderId)
  {
      // Get the delivery tracking information
      $deliveryTracking = DeliveryTracking::where('order_id', $orderId)->first();

      if ($deliveryTracking) {
          return response()->json([
              'message' => 'Delivery status retrieved successfully!',
              'status' => $deliveryTracking->status,
              'delivery_boy' => $deliveryTracking->delivery_boy_name,
          ]);
      } else {
          return response()->json(['message' => 'No delivery tracking found for this order.'], 404);
      }
  }
  



  // Get All Orders
public function getAllOrders()
{
    // Retrieve all orders
    $orders = Order::all();

    if ($orders->isEmpty()) {
        return response()->json(['message' => 'No orders found.'], 404);
    }

    return response()->json(['message' => 'Orders retrieved successfully!', 'orders' => $orders]);
}

    

public function updateDeliveryTracking(Request $request, $orderId)
{
    // Validate the incoming request data
    $request->validate([
        'delivery_boy_id' => 'required|',
        'delivery_boy_name' => 'required|string',
        'delivery_boy_contact' => 'nullable|string',
    ]);

    // Find the order
    $order = Order::findOrFail($orderId);

    // Check if the order status is 'ready' before proceeding
    if ($order->status !== 'ready') {
        return response()->json(['message' => 'Order is not ready for delivery.'], 400);
    }

    // Create a new delivery tracking entry
    $deliveryTracking = new DeliveryTracking();
    $deliveryTracking->order_id = $orderId;
    $deliveryTracking->status = 'picked_up'; // Initially, the status will be 'picked_up'
    $deliveryTracking->delivery_boy_id = $request->input('delivery_boy_id');
    $deliveryTracking->delivery_boy_name = $request->input('delivery_boy_name');
    $deliveryTracking->delivery_boy_contact = $request->input('delivery_boy_contact');
    $deliveryTracking->save();

    // Optionally, update the order status to 'processing' or another appropriate status
    $order->status = 'completed'; // Or another status like 'on_delivery'
    $order->save();

    return response()->json(['message' => 'Delivery tracking updated successfully!', 'delivery_tracking' => $deliveryTracking]);
}



// public function getItemsWithDetails()
// {
//     // Retrieve all items
//     $items = DB::table('items')
//         ->leftJoin('itemcategories', 'items.category_ID', '=', 'itemcategories.id')
//         ->select('items.id as item_id', 'items.name as item_name', 'items.description', 'itemcategories.name as category_name', 'items.restaurant_ID')
//         ->get();

//     // Add item details to each item
//     foreach ($items as $item) {
//         $item->item_details = DB::table('itemdetails')
//             ->where('item_ID', $item->item_id)
//             ->select('id as detail_id', 'variation_name', 'cost', 'additional_info', 'photo', 'status')
//             ->get();
//     }

//     if ($items->isEmpty()) {
//         return response()->json(['message' => 'No items found.'], 404);
//     }

//     return response()->json(['message' => 'Items retrieved successfully!', 'items' => $items]);
// }




// public function getItemsWithDetails()
// {
//     // Retrieve all items with their categories and details
//     $items = DB::table('items')
//         ->leftJoin('itemcategories', 'items.category_ID', '=', 'itemcategories.id')
//         ->leftJoin('itemdetails', 'items.id', '=', 'itemdetails.item_ID')
//         ->leftJoin('itemattributes', 'itemdetails.id', '=', 'itemattributes.itemdetail_id') // Join with attributes
//         ->select(
//             'items.id as item_id',
//             'items.name as item_name',
//             'items.description as item_description',
//             'itemcategories.name as item_category',
//             'itemdetails.id as item_detail_id',
//             'itemdetails.variation_name',
//             'itemdetails.cost as item_detail_price',
//             'itemdetails.additional_info',
//             'itemdetails.photo',
//             'itemdetails.status',
//             'itemdetails.preparation_time',
//             'itemdetails.timesensitive',
//             'itemattributes.id as attribute_id',
//             'itemattributes.key as attribute_key',
//             'itemattributes.value as attribute_value'
//         )
//         ->get();

//     if ($items->isEmpty()) {
//         return response()->json(['message' => 'No items found.'], 404);
//     }

//     // Base URL for images
//     $baseURL = asset('storage/');
  


//     // Process data to match the required JSON structure
//     $formattedItems = [];
//     foreach ($items as $item) {
//         $itemId = $item->item_id;
//         $detailId = $item->item_detail_id;

//         // Skip items that have no details
//         if (is_null($detailId)) {
//             continue;
//         }

//         if (!isset($formattedItems[$itemId])) {
//             $formattedItems[$itemId] = [
//                 'item_id' => $item->item_id,
//                 'item_name' => $item->item_name,
//                 'item_description' => $item->item_description,
//                 'item_category' => $item->item_category,
//                 'item_details' => [],
//             ];
//         }

//         if (!isset($formattedItems[$itemId]['item_details'][$detailId])) {
//             $formattedItems[$itemId]['item_details'][$detailId] = [
//                 'item_detail_id' => $item->item_detail_id,
//                 'variation_name' => $item->variation_name,
//                 'item_detail_price' => $item->item_detail_price,
//                 'additional_info' => $item->additional_info,
//                 'photo' => $item->photo ? $baseURL . '/' . $item->photo : null,
//                 'status' => $item->status,
//                 'preparation_time' => $item->preparation_time,
//                 'timesensitive' => $item->timesensitive,
//                 'item_attributes' => [],
//             ];
//         }

//         // Add attributes only if they exist
//         if ($item->attribute_id) {
//             $formattedItems[$itemId]['item_details'][$detailId]['item_attributes'][] = [
//                 'attribute_id' => $item->attribute_id,
//                 'attribute_key' => $item->attribute_key,
//                 'attribute_value' => $item->attribute_value,
//             ];
//         }
//     }

//     // Flatten the array and remove numeric keys
//     $finalResponse = [];
//     foreach ($formattedItems as $item) {
//         $item['item_details'] = array_values($item['item_details']); // Reset numeric keys
//         $finalResponse[] = $item;
//     }

//     return response()->json([
//         'message' => 'Items retrieved successfully!',
//         'items' => $finalResponse,
//     ]);
// }


public function getItemsWithDetails()
{
    $baseUrl = url('storage'); // Adjust this if needed

    $items = DB::table('items')
        ->leftJoin('itemcategories', 'items.category_ID', '=', 'itemcategories.id')
        ->leftJoin('itemdetails', 'items.id', '=', 'itemdetails.item_ID')
        ->leftJoin('itemattributes', 'itemdetails.id', '=', 'itemattributes.itemdetail_id')
        ->select(
            'items.id as item_id',
            'items.name as item_name',
            'items.description as item_description',
            'itemcategories.id as item_category_id',
            'itemcategories.name as item_category',
            'itemdetails.id as item_detail_id',
            'itemdetails.variation_name',
            'itemdetails.cost as item_detail_price',
            'itemdetails.additional_info',
            'itemdetails.photo',
            'itemdetails.status',
            'itemdetails.preparation_time',
            'itemdetails.timesensitive',
            'itemattributes.id as attribute_id',
            'itemattributes.key as attribute_key',
            'itemattributes.value as attribute_value'
        )
        ->get();

    if ($items->isEmpty()) {
        return response()->json(['message' => 'No items found.'], 404);
    }

    $formattedItems = $items->groupBy('item_detail_id')->map(function ($itemDetails) use ($baseUrl) {
        $firstItem = $itemDetails->first();

        return [
            'item_id' => $firstItem->item_id,
            'item_name' => $firstItem->item_name,
            'item_description' => $firstItem->item_description,
            'timesensitive' => $firstItem->timesensitive,
            'preparation_time' => $firstItem->preparation_time,
            'picture' => $firstItem->photo ? $baseUrl . '/' . $firstItem->photo : null, // Full URL
            'itemdetail_id' => $firstItem->item_detail_id,
            'variation_name' => $firstItem->variation_name,
            'unit_price' => $firstItem->item_detail_price,
            'additional_info' => $firstItem->additional_info,
            'item_category_id' => $firstItem->item_category_id,
            'item_category' => $firstItem->item_category,
            'item_attributes' => $itemDetails->filter(function ($detail) {
                return !is_null($detail->attribute_id);
            })->map(function ($attribute) {
                return [
                    'key' => $attribute->attribute_key,
                    'value' => $attribute->attribute_value,
                ];
            })->values(),
        ];
    })->values();

    return response()->json($formattedItems);
}




///New Ststuses update
 // Mark order as 'processing'
//  public function markAsProcessing($id)
//  {
//      $order = Order::find($id);
 
//      if (!$order) {
//          return response()->json([
//              'success' => false,
//              'message' => 'Order not found',
//          ], 404);
//      }
 
//      if ($order->status !== 'pending') {
//          return response()->json([
//              'success' => false,
//              'message' => "Cannot mark as processing. Order must be in 'pending' status.",
//              'current_status' => $order->status,
//          ], 400);
//      }
 
//      $order->status = 'processing';
//      $order->save();
 
//      return response()->json([
//          'success' => true,
//          'message' => 'Order marked as processing',
//          'data' => [
//              'order_id' => $order->id,
//              'status' => $order->status,
//          ]
//      ]);
//  }
 


 public function markAsProcessing($id)
 {
     $order = Order::find($id);
 
     if (!$order) {
         return response()->json([
             'success' => false,
             'message' => 'Order not found',
         ], 404);
     }
 
     if ($order->status !== 'pending') {
         return response()->json([
             'success' => false,
             'message' => "Cannot mark as processing. Order must be in 'pending' status.",
             'current_status' => $order->status,
         ], 400);
     }
 
     // First update KFC side
     $order->status = 'processing';
     $order->save();
 
     // Then call main server to mark as in_progress
    //  $response = Http::patch("http://192.168.43.63:8000/vendor/order/{$order->suborder_id}/in-progress");
 
     return response()->json([
         'success' => true,
         'message' => 'Order marked as processing (KFC) and in-progress (Main)',
         'kfc_status' => $order->status,
        //  'main_server_response' => $response->json(),
     ]);
 }
 


 public function markAsReady($id)
 {
     $order = Order::find($id);
 
     if (!$order) {
         return response()->json([
             'success' => false,
             'message' => 'Order not found',
         ], 404);
     }
 
     if (!in_array($order->status, ['processing'])) {
         return response()->json([
             'success' => false,
             'message' => "Cannot mark as ready. Order must be in 'processing' status.",
             'current_status' => $order->status,
         ], 400);
     }
 
     $order->status = 'ready';
     $order->save();
 
     return response()->json([
         'success' => true,
         'message' => 'Order marked as ready',
         'data' => [
             'order_id' => $order->id,
             'status' => $order->status,
         ]
     ]);
 }






public function markAssigned(Request $request, $id)
{
    \Log::info('Request data:', $request->all());
   
    $order = Order::find($id);

    if (!$order) {
        return response()->json([
            'success' => false,
            'message' => 'Order not found',
        ], 404);
    }

    // Update order status to assigned
    $order->status = 'assigned';
    $order->save();

    // Validate request
    $request->validate([
        'delivery_boy_id' => 'required|integer',
        'delivery_boy_name' => 'nullable|string',
        'delivery_boy_contact' => 'nullable|string',
        'delivery_boy_image' => 'nullable|image|mimes:jpeg,png,jpg',
    ]);

    // Handle image upload
    $imagePath = null;
    if ($request->hasFile('delivery_boy_image')) {
        $imagePath = $request->file('delivery_boy_image')->store('delivery_boy_images', 'public');
    }

    // Save to delivery_tracking table
    DeliveryTracking::create([
        'order_id' => $order->id,
        'delivery_boy_id' => $request->delivery_boy_id,
        'delivery_boy_name' => $request->delivery_boy_name,
        'delivery_boy_contact' => $request->delivery_boy_contact,
        'delivery_boy_image' => $imagePath, // saved as relative path in /storage/app/public/
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Order marked as assigned and delivery tracking saved',
        'data' => [
            'order_id' => $order->id,
            'status' => $order->status,
            'delivery_boy_image_url' => $imagePath ? asset('storage/' . $imagePath) : null,
        ]
    ]);
}





public function pickupOrder($id)
{
    $order = Order::find($id);

    if (!$order) {
        return response()->json([
            'success' => false,
            'message' => 'Order not found',
        ], 404);
    }

    if (!in_array($order->status, ['assigned'])) {
        return response()->json([
            'success' => false,
            'message' => "Cannot mark as picked up. Order must be in 'assigned' status.",
            'current_status' => $order->status,
        ], 400);
    }

    $order->status = 'picked_up';
    $order->save();

    return response()->json([
        'success' => true,
        'message' => 'Order marked as picked up',
        'data' => [
            'order_id' => $order->id,
            'status' => $order->status,
        ]
    ]);
}

public function handoverConfirmed($id)
{
    $order = Order::find($id);

    if (!$order) {
        return response()->json([
            'success' => false,
            'message' => 'Order not found',
        ], 404);
    }

    if (!in_array($order->status, ['picked_up'])) {
        return response()->json([
            'success' => false,
            'message' => "Cannot mark as handover confirmed. Order must be in 'picked_up' status.",
            'current_status' => $order->status,
        ], 400);
    }

    $order->status = 'handover_confirmed';
    $order->save();

    return response()->json([
        'success' => true,
        'message' => 'Order marked as handover confirmed',
        'data' => [
            'order_id' => $order->id,
            'status' => $order->status,
        ]
    ]);
}


public function inTransit($id)
{
    $order = Order::find($id);

    if (!$order) {
        return response()->json([
            'success' => false,
            'message' => 'Order not found',
        ], 404);
    }

    if ($order->status === 'in_transit') {
        return response()->json([
            'success' => true,
            'message' => 'Order already in transit',
            'data' => [
                'order_id' => $order->id,
                'status' => $order->status,
            ]
        ]);
    }

    if ($order->status !== 'handover_confirmed') {
        return response()->json([
            'success' => false,
            'message' => "Cannot mark as in transit. Order must be in 'handover_confirmed' or already 'in_transit' status.",
            'current_status' => $order->status,
        ], 400);
    }

    $order->status = 'in_transit';
    $order->save();

    return response()->json([
        'success' => true,
        'message' => 'Order marked as in transit',
        'data' => [
            'order_id' => $order->id,
            'status' => $order->status,
        ]
    ]);
}

public function markDelivered($id)
{
    $order = Order::find($id);

    if (!$order) {
        return response()->json([
            'success' => false,
            'message' => 'Order not found',
        ], 404);
    }

    if ($order->status !== 'in_transit') {
        return response()->json([
            'success' => false,
            'message' => "Cannot mark as delivered. Order must be in 'in_transit' status.",
            'current_status' => $order->status,
        ], 400);
    }

    $order->status = 'delivered';
    $order->save();

    return response()->json([
        'success' => true,
        'message' => 'Order marked as delivered',
        'data' => [
            'order_id' => $order->id,
            'status' => $order->status,
        ]
    ]);
}


public function cancelOrder($id)
{
    $order = Order::find($id);

    if (!$order) {
        return response()->json([
            'success' => false,
            'message' => 'Order not found',
        ], 404);
    }

    if ($order->status !== 'pending') {
        return response()->json([
            'success' => false,
            'message' => "Only 'pending' orders can be cancelled.",
            'current_status' => $order->status,
        ], 400);
    }

    $order->status = 'canceled';
    $order->save();

    return response()->json([
        'success' => true,
        'message' => 'Order has been cancelled',
        'data' => [
            'order_id' => $order->id,
            'status' => $order->status,
        ]
    ]);
}




public function confirmPaymentByCustomer($id)
{
    $order = Order::find($id);

    if (!$order) {
        return response()->json(['success' => false, 'message' => 'Order not found'], 404);
    }

    if ($order->payment_status !== 'pending') {
        return response()->json([
            'success' => false,
            'message' => "Only 'pending' payment status can be confirmed by customer.",
            'current_status' => $order->payment_status
        ], 400);
    }

    $order->payment_status = 'confirmed_by_customer';
    $order->save();

    return response()->json([
        'success' => true,
        'message' => 'Payment confirmed by customer',
        'data' => [
            'order_id' => $order->id,
            'payment_status' => $order->payment_status,
        ]
    ]);
}
public function confirmPaymentByDeliveryBoy($id)
{
    $order = Order::find($id);

    if (!$order) {
        return response()->json(['success' => false, 'message' => 'Order not found'], 404);
    }

    if ($order->payment_status !== 'confirmed_by_customer') {
        return response()->json([
            'success' => false,
            'message' => "Payment must be 'confirmed_by_customer' before delivery boy can confirm.",
            'current_status' => $order->payment_status
        ], 400);
    }

    $order->payment_status = 'confirmed_by_deliveryboy';
    $order->save();

    return response()->json([
        'success' => true,
        'message' => 'Payment confirmed by delivery boy',
        'data' => [
            'order_id' => $order->id,
            'payment_status' => $order->payment_status,
        ]
    ]);
}

public function confirmPaymentByVendor($id)
{
    $order = Order::find($id);

    if (!$order) {
        return response()->json(['success' => false, 'message' => 'Order not found'], 404);
    }

    if ($order->payment_status !== 'confirmed_by_deliveryboy') {
        return response()->json([
            'success' => false,
            'message' => "Payment must be 'confirmed_by_deliveryboy' before vendor can confirm.",
            'current_status' => $order->payment_status
        ], 400);
    }

    $order->payment_status = 'confirmed_by_vendor';
    $order->save();

    return response()->json([
        'success' => true,
        'message' => 'Payment confirmed by vendor',
        'data' => [
            'order_id' => $order->id,
            'payment_status' => $order->payment_status,
        ]
    ]);
}









public function getStocksByItemDetails(Request $request)
{
    $itemDetailIds = $request->input('item_detail_id'); // Expecting an array

    if (!is_array($itemDetailIds) || empty($itemDetailIds)) {
        return response()->json([
            'message' => 'item_detail_id must be a non-empty array.'
        ], 422);
    }

    $results = [];

    foreach ($itemDetailIds as $id) {
        $stock = DB::table('stocks')
            ->where('item_detail_ID', $id)
            ->first();

        $testStock = DB::table('test_stocks')
            ->where('item_detail_ID', $id)
            ->first();
        $results[] = [
    'item_detail_ID' => $id,
    'stock' => $stock ? ($stock->stock_qty ?? 0) : null,
    'test_stock' => $testStock ? ($testStock->stock_qty ?? 0) : null,
];

    }

    return response()->json($results);
}







///////WEBSITES

public function getAllOrdersWithDetailsForKFC()
{
    
    $baseUrl = url('/');

    $orders = DB::table('orders')
        ->join('orderdetails', 'orders.id', '=', 'orderdetails.order_id')
        ->join('itemdetails', 'orderdetails.item_detail_id', '=', 'itemdetails.id')
        ->join('items', 'itemdetails.item_ID', '=', 'items.id')
        ->select(
            'orders.id as order_id',
            'orders.order_date',
            'orders.total_amount',
            'orders.status as order_status',
            'orders.payment_status',
            'orders.payment_method',
            'orders.delivery_address',
            'orders.order_type',

            'orderdetails.qty as item_quantity',
            'orderdetails.unit_price',
            'orderdetails.subtotal',

            'itemdetails.id as item_detail_id',
            'itemdetails.variation_name',
            'itemdetails.cost',
            'itemdetails.status as item_status',

            DB::raw("CASE 
                WHEN itemdetails.photo IS NULL OR itemdetails.photo = '' 
                THEN NULL 
                ELSE CONCAT('$baseUrl/storage/', itemdetails.photo) 
            END as item_photo"),

            'items.id as item_id',
            'items.name as item_name',
            'items.description',
            'items.category_ID',
            'items.restaurant_ID'
        )
        ->orderBy('orders.order_date', 'desc')
        ->get();

    if ($orders->isEmpty()) {
        return response()->json(['message' => 'No orders found'], 404);
    }

    $grouped = [];
    foreach ($orders as $row) {
        $orderId = $row->order_id;

        if (!isset($grouped[$orderId])) {
            $grouped[$orderId] = [
                'order_id' => $row->order_id,
                'order_date' => $row->order_date,
                'total_amount' => $row->total_amount,
                'order_status' => $row->order_status,
                'payment_status' => $row->payment_status,
                'payment_method' => $row->payment_method,
                'delivery_address' => $row->delivery_address,
                'order_type' => $row->order_type,
                'items' => [],
            ];
        }

        $grouped[$orderId]['items'][] = [
            'item_detail_id' => $row->item_detail_id,
            'variation_name' => $row->variation_name,
            'cost' => $row->cost,
            'photo' => $row->item_photo, // Using generated URL
            'status' => $row->item_status,
            'item_quantity' => $row->item_quantity,
            'unit_price' => $row->unit_price,
            'subtotal' => $row->subtotal,
            'item_name' => $row->item_name,
            'item_description' => $row->description,
            'category_ID' => $row->category_ID,
            'restaurant_ID' => $row->restaurant_ID,
        ];
    }

    return response()->json(array_values($grouped));
}


// public function updateSuborderStatusFromKFC(Request $request)
// {
//     // Step 1: Validate input
//     $validated = $request->validate([
//         'vendor_order_id' => 'required|integer',
//         'status_type' => 'required|in:order,payment',
//         'status' => 'required|string',
//     ]);

//     $orderId = $validated['vendor_order_id'];
//     $statusType = $validated['status_type'];
//     $newStatus = strtolower($validated['status']);

//     // Step 2: Fetch the order
//     $order = Order::find($orderId);
//     if (!$order) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Order not found in KFC system.',
//         ], 404);
//     }

//     try {
//     $lmdResponse = Http::post('http://192.168.43.63:8000/api/vendor/update-suborder-status', [
//         'vendor_order_id' => (string)$orderId,
//         'status_type' => $statusType,
//         'status' => $mappedStatus,
//     ]);

//     $lmdData = $lmdResponse->json();

//     return response()->json([
//         'success' => true,
//         'message' => 'KFC status updated and sent to LMD.',
//         'kfc_order' => [
//             'order_id' => $order->id,
//             'status' => $order->status,
//             'payment_status' => $order->payment_status,
//         ],
//         'lmd_response' => $lmdData
//     ]);
// } catch (\Exception $e) {
//     return response()->json([
//         'success' => false,
//         'message' => 'LMD update failed. KFC status not updated.',
//         'error' => $e->getMessage(),
//         'lmd_response' => null
//     ]);
// }

// }

public function updateSuborderStatusFromKFC(Request $request)
{
    // Step 1: Validate input
    $validated = $request->validate([
        'vendor_order_id' => 'required|integer',
        'status_type' => 'required|in:order,payment',
        'status' => 'required|string',
    ]);

    $orderId = $validated['vendor_order_id'];
    $statusType = $validated['status_type'];
    $incomingStatus = strtolower($validated['status']);

    // Step 2: Fetch the order
    $order = Order::find($orderId);
    if (!$order) {
        return response()->json([
            'success' => false,
            'message' => 'Order not found in KFC system.',
        ], 404);
    }

    // Step 3: Update status locally in KFC
    if ($statusType === 'payment') {
        if ($incomingStatus === 'confirmed_by_vendor') {
            if ($order->payment_status !== 'confirmed_by_deliveryboy') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment can only be confirmed by vendor if delivery boy has confirmed it.',
                ], 400);
            }
        }

        $validPaymentStatuses = [
            'pending',
            'confirmed_by_customer',
            'confirmed_by_deliveryboy',
            'confirmed_by_vendor'
        ];

        if (!in_array($incomingStatus, $validPaymentStatuses)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid payment status.',
            ], 400);
        }

        $order->payment_status = $incomingStatus;
        $order->save();

    } elseif ($statusType === 'order') {
        $validTransitions = [
            'pending' => 'processing',
            'processing' => 'ready',
            'picked_up' => 'handover_confirmed',
            'handover_confirmed' => 'in_transit',
            'in_transit' => 'delivered',
            'delivered' => 'completed',
        ];

        $currentStatus = strtolower($order->status);
        $nextAllowed = $validTransitions[$currentStatus] ?? null;

        if ($nextAllowed !== $incomingStatus) {
            return response()->json([
                'success' => false,
                'message' => "Invalid order status transition from '$currentStatus' to '$incomingStatus'.",
            ], 400);
        }

        $order->status = $incomingStatus;
        $order->save();
    }

    // Step 4: Map to LMD-side status values (if needed)
    $statusMap = [
        'processing' => 'in_progress',
        'canceled' => 'cancelled',
    ];

    $mappedStatus = $statusMap[$incomingStatus] ?? $incomingStatus;

    // Step 5: Send status to LMD
    
    try {
        $lmdResponse = Http::timeout(60)->post('http://192.168.43.63:8000/api/vendor/update-suborder-status', [
            'vendor_order_id' => (string)$orderId,
            'status_type' => $statusType,
            'status' => $mappedStatus,
        ]);

        $lmdData = $lmdResponse->json();

        return response()->json([
            'success' => true,
            'message' => 'KFC status updated and sent to LMD.',
            'kfc_order' => [
                'order_id' => $order->id,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
            ],
            'lmd_response' => $lmdData
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'LMD update failed. KFC status not updated.',
            'error' => $e->getMessage(),
            'lmd_response' => null
        ]);
    }
}

}





