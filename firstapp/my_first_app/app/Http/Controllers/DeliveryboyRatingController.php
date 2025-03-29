<?php

namespace App\Http\Controllers;

use App\Models\DeliveryboyRating;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController; // Import the correct base controller


class DeliveryboyRatingController extends BaseController
{
    public function index()
    {
        return DeliveryboyRating::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'deliveryboy_ID' => 'required|exists:deliveryboys,ID',
            'customer_id' => 'required|exists:customers,ID',
            'suborder_id' => 'required|exists:suborder,ID',
            'rating_stars' => 'required|integer|min:1|max:5',
        ]);

        $rating = DeliveryboyRating::create($validated);
        return response()->json($rating, 201);
    }

    public function show($id)
    {
        return DeliveryboyRating::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $rating = DeliveryboyRating::findOrFail($id);
        $rating->update($request->all());
        return response()->json($rating);
    }

    public function destroy($id)
    {
        DeliveryboyRating::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
