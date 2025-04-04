<?php

namespace App\Http\Controllers;


use App\Models\Customer;
use App\Models\LMDUser;
use App\Models\Order;
use App\Models\Shop;
use App\Models\ShopCategory;
use App\Models\Branch;
use App\Models\CourierItemCategory;
use App\Models\CourierItem;
use App\Models\SubOrder;
use App\Models\LocationTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use App\Models\CourierImage;
use App\Models\CourierAdditionalinfo;
use App\Models\CourierOrder;
use App\Models\CourierLiveTracking;
use Illuminate\Support\Facades\Http;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class OrganizationController extends Controller {


    public function signup(Request $request) {
        \Log::info('Request data:', $request->all());
    
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:lmd_users,email',
            'phone_no' => 'required|string|max:15|unique:lmd_users,phone_no',
            'password' => 'required|string|min:1',
            'address_type' => 'required|string|max:50',
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'zip_code' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'cnic' => 'required|string|max:15|unique:lmd_users,cnic',
            'profile_picture' => 'nullable|file|mimes:jpeg,png,jpg',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
    
        $validated = $validator->validated();
    
        DB::beginTransaction();
    
        try {
            // Profile picture upload
            $profilePicturePath = null;
            if ($request->hasFile('profile_picture')) {
                $profilePicturePath = $request->file('profile_picture')->store('organizationImages', 'public');
            }
    
            // Insert into `lmd_users` table
            $userId = DB::table('lmd_users')->insertGetId([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone_no' => $validated['phone_no'],
                'password' => $validated['password'],
                'lmd_user_role' => 'organization',
                'cnic' => $validated['cnic'] ?? null,
                'profile_picture' => $profilePicturePath,
            ]);
    
            // Insert into `organizations` table
            DB::table('organizations')->insert([
                'lmd_users_ID' => $userId,
            ]);
    
            // Insert into `addresses` table
            DB::table('addresses')->insert([
                'address_type' => $validated['address_type'],
                'street' => $validated['street'],
                'city' => $validated['city'],
                'zip_code' => $validated['zip_code'],
                'country' => $validated['country'] ?? 'Pakistan',
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'lmd_users_ID' => $userId,
            ]);
    
            DB::commit();
            return response()->json([
                'message' => 'Organization signed up successfully',
                'user_id' => $userId,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Signup failed', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Signup failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    
       

    public function connectVendorToOrganization(Request $request) {
        $validator = Validator::make($request->all(), [
            'vendor_ID' => 'required|exists:vendors,id',
            'organization_ID' => 'required|exists:organizations,id',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
    
        try {
            // Insert with status always set to 'active'
            DB::table('vendororganization')->insert([
                'vendor_ID' => $request->vendor_ID,
                'organization_ID' => $request->organization_ID,
                'status' => 'active',  // Always active
            ]);
    
            return response()->json([
                'message' => 'Vendor connected to organization successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to connect vendor to organization',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    
}