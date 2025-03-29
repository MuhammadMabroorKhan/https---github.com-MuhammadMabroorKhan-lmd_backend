<?php
namespace App\Http\Controllers;


use App\Models\DeliveryBoy;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController; // Import the correct base controller

class DeliveryBoyController extends BaseController
{
    public function index()
    {
        return DeliveryBoy::all(); // Retrieve all delivery boys
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:delivery_boys,email',
            'phone_no' => 'required|unique:delivery_boys,phone_no',
            'vehicle_no' => 'required',
        ]);

        $deliveryBoy = DeliveryBoy::create($validated); // Create new delivery boy
        return response()->json($deliveryBoy, 201);
    }

    public function show($id)
    {
        $deliveryBoy = DeliveryBoy::findOrFail($id); // Retrieve delivery boy by ID
        return response()->json($deliveryBoy);
    }

    public function update(Request $request, $id)
    {
        $deliveryBoy = DeliveryBoy::findOrFail($id); // Find delivery boy to update
        $deliveryBoy->update($request->all()); // Update delivery boy
        return response()->json($deliveryBoy);
    }

    public function destroy($id)
    {
        $deliveryBoy = DeliveryBoy::findOrFail($id); // Find delivery boy to delete
        $deliveryBoy->delete(); // Delete delivery boy
        return response()->json(null, 204); // Return no content response
    }
}
