<?php

namespace App\Http\Controllers;

use App\Models\Suborder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController; // Import the correct base controller

class SuborderController extends BaseController
{
    public function index()
    {
        return Suborder::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_order_ID' => 'required',
            'total_amount' => 'required|numeric',
            'order_ID' => 'required|exists:orders,ID',
        ]);

        $suborder = Suborder::create($validated);
        return response()->json($suborder, 201);
    }

    public function show($id)
    {
        return Suborder::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $suborder = Suborder::findOrFail($id);
        $suborder->update($request->all());
        return response()->json($suborder);
    }

    public function destroy($id)
    {
        Suborder::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
