<?php

namespace App\Http\Controllers;
use App\Models\XYZ;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use Illuminate\Routing\Controller as BaseController;
class XYZController extends BaseController
{
     // GET /api/xyz
     public function index()
     {
         return XYZ::all();  // Retrieve all records
     }
 
     // POST /api/xyz
//      public function sstore(Request $request)
// {
//     $validated = $request->validate([
//         'name' => 'required|string|max:255',
//         'age' => 'required|integer',
//         'gender' => 'required|string|max:20',
//         'city' => 'required|string|max:255',
//     ]);
//     \Log::info('Validated Data:', $validated);
//     // Create the new record with error handling
//     try {
//         $xyz = XYZ::create($validated);  // Create new record

//         // Log the created record to check if it's inserted correctly
//         \Log::info('Created XYZ:', ['xyz' => $xyz]);

//         return response()->json($xyz, 201);  // Return success response
//     } catch (\Exception $e) {
//         \Log::error('Error creating XYZ:', ['error' => $e->getMessage()]);
//         return response()->json(['message' => 'Error creating record: ' . $e->getMessage()], 500);
//     }
// }

     
    //  public function store(Request $request){
    //     // $validator = Validator::make($request->all(),[
    //     //     'name' => 'required|string|max:255',
    //     //     'age' => 'required|integer',
    //     //     'gender' => 'required|string|max:20',
    //     //     'city' => 'required|string|max:255',
    //     // ]);

    //     $validated = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'age' => 'required|integer',
    //         'gender' => 'required|string|max:20',
    //         'city' => 'required|string|max:255',
    //     ]);

    
    //     // if($validator->fails()){
    //     //     return response()->json([
    //     //         'message' => 'All fields are mandatory',
    //     //         'error' => $validator->messages(),
    //     //     ],422);
    //     // }

    //     try {
    //         $xyz = XYZ::create($validated);
    //         // $xyz=XYZ::create([
    //         //     'name' => $request->name,
    //         //     'age' => $request->age,
    //         //     'gender' => $request->gender,
    //         //     'city' => $request->city,
    //         // ]);

            
    //     }
    //     catch(\Exception $e) {
    //         echo $e;
    //     }


       
    //     return response()->json([
    //         'message' => 'Record Inserted',
    //         'data' => $xyz
    //     ],200);
    //  }

     
 
     // GET /api/xyz/{id}
     public function show($id)
     {
         $xyz = XYZ::findOrFail($id);  // Retrieve specific record
         return response()->json($xyz);
     }
 
     // PUT /api/xyz/{id}
    //  public function update(Request $request, $id)
    //  {
    //      $xyz = XYZ::findOrFail($id);  // Find record to update
 
    //      $validated = $request->validate([
    //          'name' => 'string|max:255',
    //          'age' => 'integer',
    //          'gender' => 'string|max:20',
    //          'city' => 'string|max:255',
    //      ]);
 
    //      $xyz->update($validated);  // Update record
    //      return response()->json($xyz);
    //  }
 

    public function destroy($id)
{
    DB::beginTransaction();

    try {
        $xyz = XYZ::find($id);

        if (!$xyz) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        $deleted = $xyz->delete();

        DB::commit();

        if ($deleted) {
            return response()->json(['message' => 'Record deleted successfully'], 200);
        } else {
            return response()->json(['message' => 'Failed to delete record'], 500);
        }

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json(['message' => 'Error deleting record: ' . $e->getMessage()], 500);
    }
}

    





public function store(Request $request)
{
    // Validate incoming request data
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'age' => 'required|integer',
        'gender' => 'required|string|max:20',
        'city' => 'required|string|max:255',
    ]);

    
    // Start a database transaction
    DB::beginTransaction();

    try {
        // Create new record in the XYZ table

        \DB::enableQueryLog();
$xyz = XYZ::create($validated);
\Log::info(\DB::getQueryLog());


        // Commit the transaction
        DB::commit();

        // Return success response
        return response()->json([
            'message' => 'Record Inserted',
            'data' => $xyz
        ], 201);

    } catch (\Exception $e) {
        // Rollback the transaction in case of error
        DB::rollBack();

        // Log the error (optional)
        \Log::error('Error in Store: ' . $e->getMessage());

        // Return error response
        return response()->json([
            'message' => 'Error inserting record: ' . $e->getMessage()
        ], 500);
    }
}

public function update(Request $request, $id)
{
    // Validate incoming request data
    $validated = $request->validate([
        'name' => 'string|max:255',
        'age' => 'integer',
        'gender' => 'string|max:20',
        'city' => 'string|max:255',
    ]);

    // Start a database transaction
    DB::beginTransaction();

    try {
        // Find the specific record by ID
        $xyz = XYZ::findOrFail($id);

        // Update the record with validated data
        $xyz->update($validated);

        // Commit the transaction
        DB::commit();

        // Return success response
        return response()->json([
            'message' => 'Record Updated',
            'data' => $xyz
        ], 200);

    } catch (\Exception $e) {
        // Rollback the transaction in case of error
        DB::rollBack();

        // Log the error (optional)
        \Log::error('Error in Update: ' . $e->getMessage());

        // Return error response
        return response()->json([
            'message' => 'Error updating record: ' . $e->getMessage()
        ], 500);
    }
}






}
