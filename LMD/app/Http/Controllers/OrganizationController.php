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

    public function signup( Request $request ) {
        \Log::info( 'Request data:', $request->all() );

        $validator = Validator::make( $request->all(), [
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
        ] );

        if ( $validator->fails() ) {
            return response()->json( [
                'success' => false,
                'errors' => $validator->errors()
            ], 422 );
        }

        $validated = $validator->validated();

        DB::beginTransaction();

        try {
            // Profile picture upload
            $profilePicturePath = null;
            if ( $request->hasFile( 'profile_picture' ) ) {
                $profilePicturePath = $request->file( 'profile_picture' )->store( 'organizationImages', 'public' );
            }

            // Insert into `lmd_users` table
            $userId = DB::table( 'lmd_users' )->insertGetId( [
                'name' => $validated[ 'name' ],
                'email' => $validated[ 'email' ],
                'phone_no' => $validated[ 'phone_no' ],
                'password' => $validated[ 'password' ],
                'lmd_user_role' => 'organization',
                'cnic' => $validated[ 'cnic' ] ?? null,
                'profile_picture' => $profilePicturePath,
            ] );

            // Insert into `organizations` table
            DB::table( 'organizations' )->insert( [
                'lmd_users_ID' => $userId,
            ] );

            // Insert into `addresses` table
            DB::table( 'addresses' )->insert( [
                'address_type' => $validated[ 'address_type' ],
                'street' => $validated[ 'street' ],
                'city' => $validated[ 'city' ],
                'zip_code' => $validated[ 'zip_code' ],
                'country' => $validated[ 'country' ] ?? 'Pakistan',
                'latitude' => $validated[ 'latitude' ],
                'longitude' => $validated[ 'longitude' ],
                'lmd_users_ID' => $userId,
            ] );

            DB::commit();
            return response()->json( [
                'message' => 'Organization signed up successfully',
                'user_id' => $userId,
            ], 201 );
        } catch ( \Exception $e ) {
            DB::rollBack();
            Log::error( 'Signup failed', [ 'error' => $e->getMessage() ] );
            return response()->json( [
                'message' => 'Signup failed',
                'error' => $e->getMessage(),
            ], 500 );
        }
    }

    public function getDeliveryBoysByOrganization( $organization_id )
 {
        $baseUrl = url( '/' );
        // e.g., http://yourdomain.com

        $deliveryBoys = DB::table( 'deliveryboys' )
        ->join( 'lmd_users', 'deliveryboys.lmd_users_ID', '=', 'lmd_users.id' )
        ->leftJoin( 'addresses', 'addresses.lmd_users_ID', '=', 'lmd_users.id' )
        ->where( 'deliveryboys.organization_id', $organization_id )
        ->select(
            'deliveryboys.id as delivery_boy_id',
            'lmd_users.name',
            'lmd_users.email',
            'lmd_users.phone_no',
            'lmd_users.cnic',
            DB::raw( "CASE 
                            WHEN lmd_users.profile_picture IS NULL OR lmd_users.profile_picture = '' 
                            THEN NULL 
                            ELSE CONCAT('$baseUrl/storage/', lmd_users.profile_picture) 
                         END as profile_picture" ),
            'deliveryboys.license_no',
            'deliveryboys.license_expiration_date',
            DB::raw( "CASE 
                            WHEN deliveryboys.license_front IS NULL OR deliveryboys.license_front = '' 
                            THEN NULL 
                            ELSE CONCAT('$baseUrl/storage/', deliveryboys.license_front) 
                         END as license_front" ),
            DB::raw( "CASE 
                            WHEN deliveryboys.license_back IS NULL OR deliveryboys.license_back = '' 
                            THEN NULL 
                            ELSE CONCAT('$baseUrl/storage/', deliveryboys.license_back) 
                         END as license_back" ),
            'deliveryboys.status',
            'deliveryboys.approval_status',
            'addresses.address_type',
            'addresses.street',
            'addresses.city',
            'addresses.zip_code',
            'addresses.country',
            'addresses.latitude',
            'addresses.longitude'
        )
        ->get();

        return response()->json( [
            'delivery_boys' => $deliveryBoys,
        ], 200 );
    }

    public function getPendingVendorRequests( $organizationId )
 {
        $baseUrl = url( '/' );

        $baseQuery = DB::table( 'vendororganization' )
        ->join( 'vendors', 'vendororganization.vendor_ID', '=', 'vendors.id' )
        ->join( 'organizations', 'vendororganization.organization_ID', '=', 'organizations.id' )
        ->join( 'lmd_users as vendor_user', 'vendors.lmd_users_ID', '=', 'vendor_user.id' )
        ->join( 'lmd_users as org_user', 'organizations.lmd_users_ID', '=', 'org_user.id' )
        ->where( 'vendororganization.organization_ID', $organizationId )
        ->select(
            'vendororganization.id as request_id',
            'vendororganization.approval_status',
            'vendororganization.status',

            'vendors.id as vendor_id',
            'vendor_user.name as vendor_name',
            'vendor_user.email as vendor_email',
            'vendor_user.phone_no as vendor_phone',
            DB::raw( "CASE 
                            WHEN vendor_user.profile_picture IS NULL OR vendor_user.profile_picture = '' 
                            THEN NULL 
                            ELSE CONCAT('$baseUrl/storage/', vendor_user.profile_picture) 
                        END as vendor_profile_picture" ),

            'organizations.id as organization_id',
            'org_user.name as org_user_name',
            'org_user.email as org_user_email'
        );
        // Separate queries for each status
        $pending = ( clone $baseQuery )->where( 'vendororganization.approval_status', 'pending' )->get();
        $approved = ( clone $baseQuery )->where( 'vendororganization.approval_status', 'approved' )->get();
        $rejected = ( clone $baseQuery )->where( 'vendororganization.approval_status', 'rejected' )->get();

        return response()->json( [
            'pending_requests' => $pending,
            'approved_requests' => $approved,
            'rejected_requests' => $rejected,
        ], 200 );
    }

    // public function getPendingVendorRequests( $organizationId )
    // {
    //     $baseQuery = DB::table( 'vendororganization' )
    //         ->join( 'vendors', 'vendororganization.vendor_ID', '=', 'vendors.id' )
    //         ->join( 'organizations', 'vendororganization.organization_ID', '=', 'organizations.id' )
    //         ->join( 'lmd_users as vendor_user', 'vendors.lmd_users_ID', '=', 'vendor_user.id' )
    //         ->join( 'lmd_users as org_user', 'organizations.lmd_users_ID', '=', 'org_user.id' )
    //         ->where( 'vendororganization.organization_ID', $organizationId )
    //         ->select(
    //             'vendororganization.id as request_id',
    //             'vendororganization.approval_status',
    //             'vendororganization.status',

    //             'vendors.id as vendor_id',
    //             'vendor_user.name as vendor_name',
    //             'vendor_user.email as vendor_email',
    //             'vendor_user.phone_no as vendor_phone',

    //             'organizations.id as organization_id',
    //             'org_user.name as org_user_name',
    //             'org_user.email as org_user_email'
    // );

    //     // Separate queries for each status
    //     $pending = ( clone $baseQuery )->where( 'vendororganization.approval_status', 'pending' )->get();
    //     $approved = ( clone $baseQuery )->where( 'vendororganization.approval_status', 'approved' )->get();
    //     $rejected = ( clone $baseQuery )->where( 'vendororganization.approval_status', 'rejected' )->get();

    //     return response()->json( [
    //         'pending_requests' => $pending,
    //         'approved_requests' => $approved,
    //         'rejected_requests' => $rejected,
    // ], 200 );
    // }

    public function acceptVendorRequest( $requestId )
 {
        // Check for pending rejection reasons before approval
        $hasPendingReasons = DB::table( 'vendororganizationrejectionreasons' )
        ->where( 'vendororganization_ID', $requestId )
        ->where( 'status', 'Pending' )
        ->exists();

        if ( $hasPendingReasons ) {
            return response()->json( [
                'message' => 'Cannot approve request with pending rejection reasons.'
            ], 400 );
        }

        DB::table( 'vendororganization' )
        ->where( 'id', $requestId )
        ->update( [ 'approval_status' => 'approved' ] );

        return response()->json( [ 'message' => 'Vendor request accepted' ], 200 );
    }

    // public function rejectVendorRequest( Request $request, $requestId )
    // {
    //     $request->validate( [
    //         'reason' => 'required|string'
    // ] );

    //     // Update status to rejected
    //     DB::table( 'vendororganization' )
    //         ->where( 'id', $requestId )
    //         ->update( [ 'status' => 'rejected' ] );

    //     // Save rejection reason
    //     DB::table( 'vendorrejectionreasons' )->insert( [
    //         'vendororganization_ID' => $requestId,
    //         'reason' => $request->reason,
    //         'status' => 'pending'
    // ] );

    //     return response()->json( [ 'message' => 'Vendor request rejected with reason saved' ], 200 );
    // }

    public function rejectVendorRequest( Request $request, $requestId )
 {
        $request->validate( [
            'rejection_reasons' => 'required|array',
            'rejection_reasons.*' => 'string|max:255',
        ] );

        $vendorOrg = DB::table( 'vendororganization' )->where( 'id', $requestId )->first();

        if ( !$vendorOrg || $vendorOrg->approval_status !== 'pending' ) {
            return response()->json( [ 'message' => 'Request not found or already processed.' ], 404 );
        }

        DB::beginTransaction();

        try {
            foreach ( $request->rejection_reasons as $reason ) {
                DB::table( 'vendororganizationrejectionreasons' )->insert( [
                    'vendororganization_ID' => $requestId,
                    'reason' => $reason,
                    'status' => 'pending',
                ] );
            }

            DB::table( 'vendororganization' )->where( 'id', $requestId )->update( [
                'approval_status' => 'rejected'
            ] );

            DB::commit();

            return response()->json( [ 'message' => 'Vendor request rejected with reasons saved' ], 200 );

        } catch ( \Exception $e ) {
            DB::rollBack();
            return response()->json( [ 'message' => 'Failed to reject request.', 'error' => $e->getMessage() ], 500 );
        }
    }

    // public function getRejectionReasons( $organizationId )
    // {
    //     $reasons = DB::table( 'vendorrejectionreasons' )
    //         ->join( 'vendororganization', 'vendorrejectionreasons.vendororganization_ID', '=', 'vendororganization.id' )
    //         ->where( 'vendororganization.organization_ID', $organizationId )
    //         ->select(
    //             'vendorrejectionreasons.id',
    //             'vendorrejectionreasons.reason',
    //             'vendorrejectionreasons.status',
    //             'vendororganization.vendor_ID',
    //             'vendororganization.organization_ID'
    // )
    //         ->get();

    //     return response()->json( [ 'rejection_reasons' => $reasons ], 200 );
    // }

    public function getRejectionReasons( $organizationId )
 {
        $reasons = DB::table( 'vendororganizationrejectionreasons' )
        ->join( 'vendororganization', 'vendororganizationrejectionreasons.vendororganization_ID', '=', 'vendororganization.id' )
        ->where( 'vendororganization.organization_ID', $organizationId )
        ->select(
            'vendororganizationrejectionreasons.id',
            'vendororganizationrejectionreasons.reason',
            'vendororganizationrejectionreasons.status',
            'vendororganization.vendor_ID',
            'vendororganization.organization_ID'
        )
        ->get();

        if ( $reasons->isEmpty() ) {
            return response()->json( [ 'message' => 'No rejection reasons found.' ], 404 );
        }

        return response()->json( [ 'rejection_reasons' => $reasons ], 200 );
    }

    // public function correctRejectionReason( $reasonId )
    // {
    //     DB::table( 'vendorrejectionreasons' )
    //         ->where( 'id', $reasonId )
    //         ->update( [ 'status' => 'corrected' ] );

    //     return response()->json( [ 'message' => 'Rejection reason marked as corrected' ], 200 );
    // }

    public function correctRejectionReason( $reasonId )
 {
        $rejection = DB::table( 'vendororganizationrejectionreasons' )->where( 'id', $reasonId )->first();

        if ( !$rejection || $rejection->status !== 'Pending' ) {
            return response()->json( [ 'message' => 'Rejection reason not found or already corrected.' ], 404 );
        }

        DB::table( 'vendororganizationrejectionreasons' )
        ->where( 'id', $reasonId )
        ->update( [
            'status' => 'Corrected',
        ] );

        return response()->json( [ 'message' => 'Rejection reason marked as corrected' ], 200 );
    }

    public function getOrganizationData( $id )
 {
        $baseUrl = url( '/' );
        // Base URL like http://your-domain.com

        // Join organizations with lmd_users where lmd_user_role is 'organization'
        $organizationData = DB::table( 'organizations' )
        ->join( 'lmd_users', 'organizations.lmd_users_ID', '=', 'lmd_users.id' )
        ->select(
            'organizations.id as organization_id',  // organization table ID
            'lmd_users.name',
            'lmd_users.email',
            'lmd_users.phone_no',
            'lmd_users.cnic',
            'lmd_users.password',                  // Only include if necessary
            'lmd_users.lmd_user_role',
            DB::raw( "CASE 
                            WHEN lmd_users.profile_picture IS NULL OR lmd_users.profile_picture = '' 
                            THEN NULL 
                            ELSE CONCAT('$baseUrl/storage/', lmd_users.profile_picture) 
                        END as profile_picture" )
        )
        ->where( 'lmd_users.id', $id )
        ->where( 'lmd_users.lmd_user_role', 'organization' )
        ->first();

        if ( $organizationData ) {
            return response()->json( $organizationData, 200 );
        }

        return response()->json( [ 'error' => 'Organization not found' ], 404 );
    }







    public function getOrganizationStats($organizationId)
{
    // 1. Validate organization
    $organization = DB::table('organizations')
        ->where('id', $organizationId)
        ->first();

    // if (!$organization) {
    //     return response()->json(['error' => 'Organization not found'], 404);
    // }

    // 2. Total delivery boys linked directly via organization_ID
    $deliveryBoyIds = DB::table('deliveryboys')
        ->where('organization_ID', $organizationId)
        ->pluck('id');

    $totalDeliveryBoys = $deliveryBoyIds->count();

    // 3. Vendor IDs linked via vendororganization table
    $vendorIds = DB::table('vendororganization')
        ->where('organization_ID', $organizationId)
        ->pluck('vendor_ID');

    $totalVendors = $vendorIds->count();

    // 4. Count vendors by approval status
    $vendorApprovalCounts = DB::table('vendors')
        ->whereIn('id', $vendorIds)
        ->select('approval_status', DB::raw('count(*) as count'))
        ->groupBy('approval_status')
        ->pluck('count', 'approval_status');

    $vendorStatusCounts = [
        'pending' => $vendorApprovalCounts['pending'] ?? 0,
        'approved' => $vendorApprovalCounts['approved'] ?? 0,
        'rejected' => $vendorApprovalCounts['rejected'] ?? 0,
    ];

    // 5. Total delivered suborders by those delivery boys
    $totalDeliveredSuborders = DB::table('suborders')
        ->whereIn('deliveryboys_ID', $deliveryBoyIds)
        ->where('status', 'delivered')
        ->count();

    // 6. Return summary
    return response()->json([
        'total_delivery_boys' => $totalDeliveryBoys,
        'total_vendors' => $totalVendors,
        'vendor_approval_status' => $vendorStatusCounts,
        'total_delivered_orders' => $totalDeliveredSuborders
    ]);
}



// public function getOrganizationDeliveryEarnings(Request $request)
// {
//     $request->validate([
//         'organization_id' => 'required|integer',
//         'from_date' => 'nullable|date',
//         'to_date' => 'nullable|date',
//     ]);

//     $organizationId = $request->organization_id;
//     $fromDate = $request->from_date;
//     $toDate = $request->to_date;

//     $deliveryBoyIds = DB::table('deliveryboys')
//         ->where('organization_id', $organizationId)
//         ->pluck('id');

//     $query = DB::table('delivery_earnings')
//         ->join('suborders', 'delivery_earnings.suborder_id', '=', 'suborders.id')
//         ->whereIn('delivery_earnings.deliveryboy_id', $deliveryBoyIds)
//         ->where('suborders.payment_status', 'confirmed_by_deliveryboy');

//     if ($fromDate) {
//         $query->whereDate('delivery_earnings.created_at', '>=', $fromDate);
//     }
//     if ($toDate) {
//         $query->whereDate('delivery_earnings.created_at', '<=', $toDate);
//     }

//     $totalEarnings = $query->sum('delivery_earnings.total_earning');

//     return response()->json([
//         'organization_id' => $organizationId,
//         'total_earnings' => $totalEarnings,
//         'from_date' => $fromDate,
//         'to_date' => $toDate,
//     ]);
// }


// public function getDeliveryBoyEarnings(Request $request)
// {
//     $request->validate([
//         'organization_id' => 'required|integer',
//         'deliveryboy_id' => 'required|integer',
//         'from_date' => 'nullable|date',
//         'to_date' => 'nullable|date',
//     ]);

//     $organizationId = $request->organization_id;
//     $deliveryBoyId = $request->deliveryboy_id;
//     $fromDate = $request->from_date;
//     $toDate = $request->to_date;

//     $isValid = DB::table('deliveryboys')
//         ->where('organization_id', $organizationId)
//         ->where('id', $deliveryBoyId)
//         ->exists();

//     if (!$isValid) {
//         return response()->json([
//             'error' => 'Delivery boy does not belong to the organization',
//         ], 403);
//     }

//     $query = DB::table('delivery_earnings')
//         ->join('suborders', 'delivery_earnings.suborder_id', '=', 'suborders.id')
//         ->where('delivery_earnings.deliveryboy_id', $deliveryBoyId)
//         ->where('suborders.payment_status', 'confirmed_by_deliveryboy');

//     if ($fromDate) {
//         $query->whereDate('delivery_earnings.created_at', '>=', $fromDate);
//     }
//     if ($toDate) {
//         $query->whereDate('delivery_earnings.created_at', '<=', $toDate);
//     }

//     $earnings = $query
//         ->select(
//             'delivery_earnings.suborder_id',
//             'delivery_earnings.distance_km',
//             'delivery_earnings.rate_per_km',
//             'delivery_earnings.total_earning',
//             'delivery_earnings.created_at'
//         )
//         ->orderByDesc('delivery_earnings.created_at')
//         ->get();

//     $totalEarnings = $earnings->sum('total_earning');

//     return response()->json([
//         'deliveryboy_id' => $deliveryBoyId,
//         'organization_id' => $organizationId,
//         'from_date' => $fromDate,
//         'to_date' => $toDate,
//         'total_earnings' => $totalEarnings,
//         'earnings' => $earnings,
//     ]);
// }


public function getOrganizationDeliveryEarnings(Request $request)
{
    \Log::info('🔥 Raw Input', ['raw' => $request->getContent()]);
\Log::info('🔥 Parsed Input', $request->all());


    $request->validate([
        'organization_id' => 'required|integer',
    ]);

    $organizationId = $request->organization_id;

    // Step 1: Get delivery boys under the organization
    $deliveryBoyIds = DB::table('deliveryboys')
        ->where('organization_id', $organizationId)
        ->pluck('id');

    \Log::info('🟨 DeliveryBoy IDs for Org ' . $organizationId, $deliveryBoyIds->toArray());

    // Step 2: Get earnings from confirmed suborders
  $earnings = DB::table('delivery_earnings')
    ->join('suborders', 'delivery_earnings.suborder_id', '=', 'suborders.id')
    ->whereIn('delivery_earnings.deliveryboy_id', $deliveryBoyIds)
    ->whereIn('suborders.payment_status', ['confirmed_by_deliveryboy', 'confirmed_by_vendor'])
    ->select(
        'delivery_earnings.deliveryboy_id',
        'delivery_earnings.suborder_id',
        'delivery_earnings.distance_km',
        'delivery_earnings.rate_per_km',
        'delivery_earnings.total_earning',
        'delivery_earnings.created_at'
    )
    ->orderByDesc('delivery_earnings.created_at')
    ->get();

    \Log::info("🟩 Earnings Fetched for Org {$organizationId}:", $earnings->toArray());

    if ($earnings->isEmpty()) {
        \Log::warning("🟥 No earnings found for Organization ID: $organizationId");
        return response()->json([
            'organization_id' => $organizationId,
            'total_earnings' => 0,
            'message' => 'No earnings found for this organization.'
        ]);
    }

    $totalEarnings = $earnings->sum('total_earning');

    return response()->json([
        'organization_id' => $organizationId,
        'total_earnings' => $totalEarnings,
        'earnings' => $earnings,
    ]);
}




public function getDeliveryBoyEarnings(Request $request)
{
    \Log::info('🔥 Raw Input', ['raw' => $request->getContent()]);
\Log::info('🔥 Parsed Input', $request->all());


    $request->validate([
        'organization_id' => 'required|integer',
        'deliveryboy_id' => 'required|integer',
    ]);

    $organizationId = $request->organization_id;
    $deliveryBoyId = $request->deliveryboy_id;

    $isValid = DB::table('deliveryboys')
        ->where('organization_id', $organizationId)
        ->where('id', $deliveryBoyId)
        ->exists();

    \Log::info("🟨 Validation of DeliveryBoy $deliveryBoyId in Org $organizationId: " . ($isValid ? 'YES' : 'NO'));

    if (!$isValid) {
        \Log::error("🟥 Delivery boy $deliveryBoyId does not belong to organization $organizationId");
        return response()->json([
            'error' => 'Delivery boy does not belong to the organization',
        ], 403);
    }

   $earnings = DB::table('delivery_earnings')
    ->join('suborders', 'delivery_earnings.suborder_id', '=', 'suborders.id')
    ->where('delivery_earnings.deliveryboy_id', $deliveryBoyId)
    ->whereIn('suborders.payment_status', ['confirmed_by_deliveryboy', 'confirmed_by_vendor'])
    ->select(
        'delivery_earnings.suborder_id',
        'delivery_earnings.distance_km',
        'delivery_earnings.rate_per_km',
        'delivery_earnings.total_earning',
        'delivery_earnings.created_at'
    )
    ->orderByDesc('delivery_earnings.created_at')
    ->get();


    \Log::info("🟩 Earnings fetched for DeliveryBoy $deliveryBoyId:", $earnings->toArray());

    if ($earnings->isEmpty()) {
        \Log::warning("🟥 No earnings found for DeliveryBoy ID: $deliveryBoyId");
        return response()->json([
            'organization_id' => $organizationId,
            'deliveryboy_id' => $deliveryBoyId,
            'total_earnings' => 0,
            'message' => 'No earnings found for this delivery boy.'
        ]);
    }

    $totalEarnings = $earnings->sum('total_earning');

    return response()->json([
        'deliveryboy_id' => $deliveryBoyId,
        'organization_id' => $organizationId,
        'total_earnings' => $totalEarnings,
        'earnings' => $earnings,
    ]);
}

}