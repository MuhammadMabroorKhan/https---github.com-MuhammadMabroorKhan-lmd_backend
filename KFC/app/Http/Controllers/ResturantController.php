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
          'order_type' => 'required|in:dine_in,takeaway,delivery,lmd',
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
          $orderDetail = new OrderDetail();
          $orderDetail->order_id = $order->id;
          $orderDetail->item_detail_id = $item['item_detail_id'];
          $orderDetail->qty = $item['qty'];
          $orderDetail->unit_price = $item['unit_price'];
          $orderDetail->subtotal = $item['subtotal'];
          $orderDetail->save();
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
  

  // Add Item Rating
  public function addItemRating(Request $request)
  {
      // Validate the rating input
      $request->validate([
          'stars' => 'required|numeric|min:1|max:5',
          'comments' => 'nullable|string',
          'order_ID' => 'required|exists:orders,id',
          'item_detail_ID' => 'required|', //exists:item_details,id',
      ]);

      // Create and save the rating
      $rating = new ItemRating();
      $rating->stars = $request->input('stars');
      $rating->comments = $request->input('comments');
      $rating->order_ID = $request->input('order_ID');
      $rating->item_detail_ID = $request->input('item_detail_ID');
      $rating->save();

      return response()->json(['message' => 'Rating added successfully!', 'rating' => $rating]);
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






}





