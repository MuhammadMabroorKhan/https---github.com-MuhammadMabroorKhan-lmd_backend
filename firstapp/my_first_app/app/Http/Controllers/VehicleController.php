<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController; // Import the correct base controller
class VehicleController extends BaseController
{
    public function index()
    {
        return Vehicle::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'plate_no' => 'required|unique:vehicle',
            'deliveryboy_ID' => 'required|exists:deliveryboys,ID',
        ]);

        $vehicle = Vehicle::create($validated);
        return response()->json($vehicle, 201);
    }

    public function show($id)
    {
        return Vehicle::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->update($request->all());
        return response()->json($vehicle);
    }

    // public function destroy($id)
    // {
    //     Vehicle::findOrFail($id)->delete();
    //     return response()->json(null, 204);
    // }

    public function destroy($id) {
        $vehicle = Vehicle::findOrFail($id); 
    
        if ($vehicle) {
            $vehicle->delete();
            return response()->json(['message' => 'Vehicle  Deleted'], 200); 
        } else {
            return response()->json(['message' => 'Vehicle not found'], 404);
        }
    }
}
