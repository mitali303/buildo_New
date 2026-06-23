<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Backend\Scheme;
use App\Models\Backend\Consumption_Details;
use App\Models\Backend\Material_Consumption;


class UserAppController extends Controller
{
    // ✅ Login API
    public function loginapp(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required|string'
        ]);

       $user = User::where('UserID', $request->username)
                    ->whereIn('access_type', ['mobile', 'both'])
                    ->first();

        // Check user exists
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found or inactive'
            ], 404);
        }

        // Verify password manually
        if (!Hash::check($request->password, $user->Password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid password'
            ], 401);
        }

        // Create token
        $user->tokens()->delete();

$token = $user->createToken('deliveryboy-token')->plainTextToken;

        activity_log('App Login', '', $user);

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->ID,
                'name' => $user->Name,
            ]
        ]);
    }

    // Update device token (authenticated user)
    public function updateDeviceToken(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or inactive user'
            ], 401);
        }

        Log::info('Device API - User ID: ' . $user->id);

        $user = $request->user();
        $user->device_token = $request->device_token;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Device token updated successfully'
        ]);
    }

    public function getschemes(Request $request)
    {
        // For POST request with form data
        $ID = $request->input('user_id');
        $user = User::find($ID);
        
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }
        
        if ($user->Role == 1) {
            $schemes = Scheme::select('ID','Name')->get();
        } else {
            $schemeIds = [];
            if (!empty($user->scheme)) {
                $schemeIds = json_decode($user->scheme, true);
                if (!is_array($schemeIds)) {
                    $schemeIds = [];
                }
            }
            $schemes = Scheme::whereIn('ID', $schemeIds)
                            ->select('ID','Name')
                            ->get();
        }
        
        return response()->json([
            'status' => true,
            'message' => 'Schemes fetched successfully',
            'data' => $schemes
        ]);
    }


    public function getDashboardData(Request $request)
    {
        $request->validate([
            'scheme_id' => 'required|exists:scheme_step1,ID'
        ]);
        
        $schemeId = $request->scheme_id;
        
        // Fetch all dashboard statistics for the scheme
        $dashboardData = [
            'total_material_consumption' => (string) (\DB::table('material_consumption')
                ->where('ClientID', $schemeId)
                ->whereMonth('Created', now()->month)
                ->sum('Qty') ?? 0),
                
            'daily_work' => (string) (\DB::table('daily_work_entry')
                ->where('ClientID', $schemeId)
                ->whereDate('date', today())
                ->count() ?? 0),
                
            'labor_work' => (string) (\DB::table('labour_work')
                ->where('schemeID', $schemeId)
                ->whereMonth('Date', now()->month)
                ->sum('gtotal') ?? 0),
                
            'material_inward' => (string) (\DB::table('inv_product')
                ->where('ClientID', $schemeId)
                ->sum('Qty') ?? 0),
                
            'material_transfer' => (string) (\DB::table('transfer_material')
                ->where('ClientID', $schemeId)
                ->whereMonth('Created', now()->month)
                ->sum('Qty') ?? 0),
                
            'material_consumption' => (string) (\DB::table('material_consumption')
                ->where('ClientID', $schemeId)
                ->sum('Qty') ?? 0),
        ];
        
        return response()->json([
            'status' => true,
            'message' => 'Dashboard data fetched successfully',
            'data' => $dashboardData
        ]);
    }


        
    public function saveDailyWork(Request $request)
    {   
        $request->validate([
            'date' => 'required|string',
            'scheme_id' => 'required|string',
            'scheme_name' => 'required|string',
            'work_report' => 'required|string',
            'user_id' => 'required|string',
            'user_name' => 'required|string',
            'site' => 'required|string',
        ]);

        $ID = $request->input('user_id');
            $user = User::find($ID);
            
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }
        
        try {
            // Don't include ID - let database auto-increment
            $dailyWork = DB::table('daily_work_entry')->insert([
            
                    'date' => $request->date,
                    'ClientID' => $request->scheme_id,
                    'sitename' => $request->scheme_name,
                    'workdone' => $request->work_report,
                    'UserID' => $request->user_id,
                    'Created' => now(),
                    'LastEdited' => now(),
            ]);
            
            return response()->json([
                'status' => true,
                'message' => 'Daily work report saved successfully',
                'data' => $request->all()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to save: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getDailyWorkReports(Request $request)
    {
        $request->validate([
            'scheme_id' => 'required|string',
            'from_date' => 'required|date',
            'to_date' => 'required|date',
        ]);
        
        try {
            $reports = DB::table('daily_work_entry')
                ->leftJoin('user', 'daily_work_entry.UserID', '=', 'user.ID')
                ->where('daily_work_entry.ClientID', $request->scheme_id)
                ->whereBetween('daily_work_entry.date', [$request->from_date, $request->to_date])
                ->select(
                    'daily_work_entry.*',
                    'user.Name as user_name',  // Get user name from users table
                )
                ->orderBy('daily_work_entry.date', 'desc')
                ->orderBy('daily_work_entry.Created', 'desc')
                ->get();
            
            return response()->json([
                'status' => true,
                'message' => 'Daily work reports fetched successfully',
                'data' => $reports
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch reports: ' . $e->getMessage()
            ], 500);
        }
    }


    // Get Labor Work Reports
    public function getLaborWorkReports(Request $request)
    {
        $request->validate([
            'scheme_id' => 'required|string',
        ]);
        
        try {
            $reports = DB::table('labour_work')
                ->leftJoin('user', 'labour_work.userID', '=', 'user.ID')
                ->leftJoin('scheme_step1', 'labour_work.schemeID', '=', 'scheme_step1.ID')
                ->leftJoin('agency', 'labour_work.Agency_ID', '=', 'agency.ID')  // Join agency table
                ->where('labour_work.schemeID', $request->scheme_id)
                ->select(
                    'labour_work.*',
                    'user.Name as user_name',
                    'scheme_step1.Name as scheme_name',
                    'agency.Name as agency_name',  // Get agency name
                    'agency.ContactNo as agency_contact'  // Optional: get agency contact
                )
                ->orderBy('labour_work.Created', 'desc')
                ->get();
            
            return response()->json([
                'status' => true,
                'message' => 'Labor work reports fetched successfully',
                'data' => $reports
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch reports: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAgencies(Request $request)
    {
        try {
        
            // Get all agencies - adjust based on your requirement
            $agencies = DB::table('agency')
                ->select('ID', 'Name', 'ContactNo', 'Address') // Select relevant fields
                ->orderBy('Name', 'asc')
                ->get();
            
            return response()->json([
                'status' => true,
                'message' => 'Agencies fetched successfully',
                'data' => $agencies
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch agencies: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAgencyRates(Request $request)
    {
        $request->validate([
            'agency_id' => 'required|string',
        ]);
        
        try {
            $agency = DB::table('agency')
                ->where('ID', $request->agency_id)
                ->select(
                    'Mistri_m_rate',
                    'Mistri_f_rate',
                    'Labour_m_rate',
                    'Labour_f_rate',
                    'Thekedar_m_rate',
                    'Thekedar_f_rate'
                )
                ->first();
            
            
            return response()->json([
                'status' => true,
                'data' => $agency
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch rates: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function saveLaborWork(Request $request)
    {
        try {
            $validator = validator($request->all(), [
                'scheme_id' => 'required|string',
                'date' => 'required|date',
                'agency_id' => 'required|string',
                'num_mistri_male' => 'required|integer|min:0',
                'num_mistri_female' => 'required|integer|min:0',
                'num_labour_male' => 'required|integer|min:0',
                'num_labour_female' => 'required|integer|min:0',
                'num_thekedar_male' => 'required|integer|min:0',
                'num_thekedar_female' => 'required|integer|min:0',
                'gtotal' => 'required|numeric|min:0',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $ID = $request->input('user_id');
            $user = User::find($ID);
            
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }
            
            $id = $request->id ?? null;
            
            // Prepare data array based on your actual column names
            $data = [
                'ClientID' => $request->scheme_id,
                'schemeID' => $request->scheme_id,
                'Date' => $request->date,
                'Agency_ID' => $request->agency_id,
                'num_mistri_male' => $request->num_mistri_male,
                'num_mistri_female' => $request->num_mistri_female,
                'num_labour_male' => $request->num_labour_male,
                'num_labour_female' => $request->num_labour_female,
                'num_thekedar_male' => $request->num_thekedar_male,
                'num_thekedar_female' => $request->num_thekedar_female,
                'gtotal' => $request->gtotal,
                'userID' => $ID,
                'LastEdited' => now(),
            ];
            
            if ($id) {
                // Update
                $data['LastEdited'] = now(); 
                
                $updated = DB::table('labour_work')
                    ->where('ID', $id)
                    ->update($data);
                
                if ($updated) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Labor work updated successfully'
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Failed to update labor work'
                    ], 500);
                }
            } else {
                // Insert
                $data['ID'] = uniqid();
                $data['Created'] = now();
                
                $inserted = DB::table('labour_work')->insert($data);
                
                if ($inserted) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Labor work saved successfully'
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Failed to save labor work'
                    ], 500);
                }
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }



    

    // <---- Transfer_material ---->

    public function saveMaterialTransfer(Request $request)
    {
        try {
            // Get materials as JSON string and decode
            $materialsJson = $request->input('materials');
            $materials = json_decode($materialsJson, true);
            
            $validator = validator($request->all(), [
                'scheme_id' => 'required|string',
                'date' => 'required|date',
                'from_site' => 'required|string',
                'to_site' => 'required|string',
                'transport' => 'nullable|numeric|min:0',
                'loading' => 'nullable|numeric|min:0',
                'round' => 'nullable|numeric|min:0',
                'gtotal' => 'required|numeric|min:0',
                'user_id' => 'required|string',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            if (empty($materials)) {
                return response()->json([
                    'status' => false,
                    'message' => 'At least one material is required'
                ], 422);
            }
            
            $user = User::find($request->user_id);
            
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found'
                ], 401);
            }
            
            $id = $request->id ?? null;
            $srno = DB::table('transfer_detail')->max('Srno') + 1;
            
            if ($id) {
                // Update existing transfer_detail
                DB::table('transfer_detail')
                    ->where('ID', $id)
                    ->update([
                        'Date' => $request->date,
                        'from_site' => $request->from_site,
                        'To_site' => $request->to_site,
                        'transport' => $request->transport ?? 0,
                        'loading' => $request->loading ?? 0,
                        'round' => $request->round ?? 0,
                        'gtotal' => $request->gtotal,
                        'LastEdited' => now(),
                    ]);
                
                // Delete existing materials
                DB::table('transfer_material')->where('PID', $id)->delete();
                
                // Insert new materials
                foreach ($materials as $material) {
                    DB::table('transfer_material')->insert([
                        'ID' => (string) Str::uuid(),
                        'PID' => $id,
                        'matrialID' => $material['material_type_id'] ?? '',
                        'qty' => $material['quantity'] ?? 0,
                        'rate' => $material['rate'] ?? 0,
                        'Amount' => $material['amount'] ?? 0,
                        //'Unit' => $material['unit'] ?? '',
                        'from_site' => $request->from_site,
                        'To_site' => $request->to_site,
                        'ClientID' => $request->scheme_id,
                        'userID' => $request->user_id,
                        'Created' => now(),
                        'LastEdited' => now(),
                    ]);
                }
                
                $message = 'Material transfer updated successfully';
            } else {
                // Create new transfer_detail
                $newId = uniqid();
                
                DB::table('transfer_detail')->insert([
                    'ID' => $newId,
                    'Date' => $request->date,
                    'from_site' => $request->from_site,
                    'To_site' => $request->to_site,
                    'Srno' => $srno,
                    'ClientID' => $request->scheme_id,
                    'transport' => $request->transport ?? 0,
                    'loading' => $request->loading ?? 0,
                    'round' => $request->round ?? 0,
                    'gtotal' => $request->gtotal,
                    'userID' => $request->user_id,
                    'Created' => now(),
                    'LastEdited' => now(),
                ]);
                
                // Insert materials
                foreach ($materials as $material) {
                    DB::table('transfer_material')->insert([
                        'ID' => uniqid(),
                        'PID' => $newId,
                        'matrialID' => $material['material_type_id'] ?? '',
                        'qty' => $material['quantity'] ?? 0,
                        'rate' => $material['rate'] ?? 0,
                        'Amount' => $material['amount'] ?? 0,
                        //'Unit' => $material['unit'] ?? '',
                        'from_site' => $request->from_site,
                        'To_site' => $request->to_site,
                        'ClientID' => $request->scheme_id,
                        'userID' => $request->user_id,
                        'Created' => now(),
                        'LastEdited' => now(),
                    ]);
                }
                
                $message = 'Material transfer saved successfully';
            }
            
            return response()->json([
                'status' => true,
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in saveMaterialTransfer: ' . $e->getMessage());
            
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // SHOW API
    public function getMaterialTransfers(Request $request)
    {
        $request->validate([
            'scheme_id' => 'required|string',
        ]);
        
        try {
            // Get all transfer details with from_site and to_site names
            $transfers = DB::table('transfer_detail')
                ->leftJoin('scheme_step1 as from_scheme', 'transfer_detail.from_site', '=', 'from_scheme.ID')
                ->leftJoin('scheme_step1 as to_scheme', 'transfer_detail.To_site', '=', 'to_scheme.ID')
                ->where('transfer_detail.ClientID', $request->scheme_id)
                ->select(
                    'transfer_detail.*',
                    'from_scheme.Name as from_site_name',
                    'to_scheme.Name as to_site_name'
                )
                ->orderBy('transfer_detail.Created', 'desc')
                ->get();
            
            // For each transfer, get its materials with material names
            foreach ($transfers as $transfer) {
                $transfer->materials = DB::table('transfer_material')
                    ->leftJoin('material', 'transfer_material.matrialID', '=', 'material.ID')
                    ->where('transfer_material.PID', $transfer->ID)
                    ->select(
                        'transfer_material.*',
                        'material.Name as material_name',
                        'material.Type as material_type'
                    )
                    ->get();
            }
            
            return response()->json([
                'status' => true,
                'message' => 'Material transfers fetched successfully',
                'data' => $transfers
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch transfers: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get Sites (no scheme_id needed)
    public function getSites(Request $request)
    {
        try {
            $sites = DB::table('scheme_step1')
                ->select('ID', 'Name')
                ->get();
            
            return response()->json([
                'status' => true,
                'data' => $sites
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Get All Materials
   // Get Unique Material Names (grouped by Name)
    public function getMaterials(Request $request)
    {
        try {
            $materials = DB::table('material')
                ->select('Name')
                ->groupBy('Name')
                ->get();
            
            return response()->json([
                'status' => true,
                'data' => $materials
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


   // Get Material Types and details by Material Name
    public function getMaterialTypesByName(Request $request)
    {
        $request->validate([
            'material_name' => 'required|string',
        ]);
        
        try {
            // Get all entries of this material with their types and quantities
            $materialEntries = DB::table('material')
                ->where('Name', $request->material_name)
                ->select('ID', 'Type', 'Unit')
                ->get();
            
            // Extract unique types
            $types = $materialEntries->map(function($item) {
                return [
                    'id' => $item->ID,
                    'name' => $item->Type,
                    'unit' => $item->Unit,
                ];
            });
            
            return response()->json([
                'status' => true,
                'data' => [
                    'types' => $types,
                    //'total_quantity' => $materialEntries->sum('Quantity'),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }



    public function getMaterialStock(Request $request)
    {
        $materialId = $request->matid;
        $excludeId  = $request->edit_id;
        $clientId   = $request->scheme_id;
        
        if (!$materialId || !$clientId) {
            return response()->json([
                'qty' => 0,
                'error' => 'Missing material_id or scheme_id'
            ]);
        }

        // Get all calculations
        $invoiceQty = DB::table('Inv_Product')
            ->where('Material', $materialId)
            ->where('ClientID', $clientId)
            ->sum('Qty');

        $outwardQty = DB::table('material_outward')
            ->where('material', $materialId)
            ->where('ClientID', $clientId)
            ->sum('quantity');

        $transferOutQuery = DB::table('transfer_material')
            ->where('matrialID', $materialId)
            ->where('from_site', $clientId);
        if (!empty($excludeId)) {
            $transferOutQuery->where('PID', '!=', $excludeId);
        }
        $transferOutQty = $transferOutQuery->sum('qty');

        $transferInQuery = DB::table('transfer_material')
            ->where('matrialID', $materialId)
            ->where('To_site', $clientId);
        if (!empty($excludeId)) {
            $transferInQuery->where('PID', '!=', $excludeId);
        }
        $transferInQty = $transferInQuery->sum('qty');

        $consumptionQuery = DB::table('material_consumption')
            ->where('material', $materialId)
            ->where('ClientID', $clientId);
        if (!empty($excludeId)) {
            $consumptionQuery->where('Cid', '!=', $excludeId);
        }
        $consumptionQty = $consumptionQuery->sum('qty');

        $availableQty = ($invoiceQty + $transferInQty) - ($transferOutQty + $consumptionQty + $outwardQty);

        // Return detailed response for debugging
        return response()->json([
            'qty' => max(0, $availableQty),
            'details' => [
                'invoice_qty' => (float)$invoiceQty,
                'outward_qty' => (float)$outwardQty,
                'transfer_out_qty' => (float)$transferOutQty,
                'transfer_in_qty' => (float)$transferInQty,
                'consumption_qty' => (float)$consumptionQty,
            ]
        ]);
    }


    // <---- Material Consumption ---->
    // LIST API
    public function getMaterialConsumptions(Request $request)
    {
        try {
            $clientId = $request->scheme_id;
            
            if (!$clientId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Scheme not selected'
                ], 400);
            }
            
            // Get all consumption records
            $consumptions = DB::table('consumption_detail')
                ->where('ClientID', $clientId)
                ->orderBy('Created', 'desc')
                ->get();
            
            $formattedData = [];
            
            foreach ($consumptions as $consumption) {
                // Get materials for this consumption
                $materials = DB::table('material_consumption')
                    ->where('Cid', $consumption->ID)
                    ->get();
                
                $materialList = [];
                $breakdownList = [];
                
                foreach ($materials as $material) {
                    // Get material details
                    $materialDetail = DB::table('material')
                        ->where('ID', $material->material)
                        ->first();
                    
                    $materialList[] = [
                        'material_name' => $materialDetail->Name ?? '',
                        'unit' => $materialDetail->Unit ?? '',
                        'quantity' => $material->Qty ?? 0,
                        'type' => $materialDetail->Type ?? '',
                    ];
                    
                    $breakdownList[] = [
                        'unit' => $materialDetail->Unit ?? '',
                        'quantity' => $material->Qty ?? 0,
                    ];
                }
                
                $formattedData[] = [
                    'ID' => $consumption->ID,
                    'date' => $consumption->Date,
                    'siteName' => $consumption->site_name ?? '',
                    'materials' => $materialList,
                    'breakdown' => $breakdownList,
                    'notes' => $consumption->notes ?? '',
                    'Created' => $consumption->Created,
                ];
            }
            
            return response()->json([
                'status' => true,
                'message' => 'Data fetched successfully',
                'data' => $formattedData
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function saveMaterialConsumption(Request $request)
    {
        try {
            // Get materials as JSON string and decode
            $materialsJson = $request->input('materials');
            $materials = json_decode($materialsJson, true);
            
            $validator = validator($request->all(), [
                'scheme_id' => 'required|string',
                'date' => 'required|date',
                'site_name' => 'required|string',
                'user_id' => 'required|string',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            if (empty($materials)) {
                return response()->json([
                    'status' => false,
                    'message' => 'At least one material is required'
                ], 422);
            }
            
            $user = User::find($request->user_id);
            
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found'
                ], 401);
            }
            
            $date =$request->date;
            
            $id = $request->id ?? null;
            
            if ($id) {
                // Update existing consumption_detail
                DB::table('consumption_detail')
                    ->where('ID', $id)
                    ->update([
                        'Date' => $date,
                        'scheme' => $request->site_name,
                        'Lastedited' => now(),
                    ]);
                
                // Delete existing materials
                DB::table('material_consumption')->where('Cid', $id)->delete();
                
                // Insert new materials
                foreach ($materials as $material) {
                    DB::table('material_consumption')->insert([
                        'ID' => uniqid(),
                        'Cid' => $id,
                        'ClientID' => $request->scheme_id,
                        'Date' => $date,
                        'Material' => $material['material_type_id'] ?? '',
                        'Unit' => $material['unit'] ?? '',
                        'Qty' => $material['quantity'] ?? 0,
                        'userID' => $request->user_id,
                        'Created' => now(),
                        'Lastedited' => now(),
                    ]);
                }
                
                $message = 'Material consumption updated successfully';
            } else {
                // Create new consumption_detail
                $newId = uniqid();
                
                DB::table('consumption_detail')->insert([
                    'ID' => $newId,
                    'ClientID' => $request->scheme_id,
                    'Date' => $date,
                    'scheme' => $request->site_name,
                    'userID' => $request->user_id,
                    'Created' => now(),
                    'Lastedited' => now(),
                    'appflag' => 0,
                ]);
                
                // Insert materials
                foreach ($materials as $material) {
                    DB::table('material_consumption')->insert([
                        'ID' => uniqid(),
                        'Cid' => $newId,
                        'ClientID' => $request->scheme_id,
                        'Date' => $date,
                        'Material' => $material['material_type_id'] ?? '',
                        'Unit' => $material['unit'] ?? '',
                        'Qty' => $material['quantity'] ?? 0,
                        'userID' => $request->user_id,
                        'Created' => now(),
                        'Lastedited' => now(),
                    ]);
                }
                
                $message = 'Material consumption saved successfully';
            }
            
            return response()->json([
                'status' => true,
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in saveMaterialConsumption: ' . $e->getMessage());
            
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteMaterialConsumption(Request $request)
    {
        try {
            $validator = validator($request->all(), [
                'id' => 'required|string',
                'scheme_id' => 'required|string',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Delete materials first
            DB::table('material_consumption')
                ->where('Cid', $request->id)
                ->delete();
            
            // Delete main record
            DB::table('consumption_detail')
                ->where('ID', $request->id)
                ->where('ClientID', $request->scheme_id)
                ->delete();
            
            return response()->json([
                'status' => true,
                'message' => 'Material consumption deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete: ' . $e->getMessage()
            ], 500);
        }
    }


    // <---- Material Consumption ---->
    public function getMaterialInward(Request $request)
    {
        try {
            $clientId = $request->scheme_id;
            
            if (!$clientId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Scheme not selected'
                ], 400);
            }
            
            // Get all invoices with vendor and scheme details
            $invoices = DB::table('inv_detail')
                ->leftJoin('vendor', 'inv_detail.purchasefrom', '=', 'vendor.ID')
                ->leftJoin('scheme_step1', 'inv_detail.destination', '=', 'scheme_step1.ID')
                ->where('inv_detail.ClientID', $clientId)
                ->select(
                    'inv_detail.ID',
                    'inv_detail.Date',
                    'inv_detail.Invno as order_no',
                    'inv_detail.gtotal as grand_total',
                    'inv_detail.purchasefrom',
                    'inv_detail.destination',
                    'vendor.Name as vendor_name',
                    'scheme_step1.Name as site_name'
                )
                ->orderBy('inv_detail.Invno', 'DESC')
                ->get();
            
            $formattedData = [];
            foreach ($invoices as $invoice) {
                $formattedData[] = [
                    'ID' => $invoice->ID,
                    'Date' => Carbon::parse($invoice->Date)->format('d-m-Y'),
                    'order_no' => $invoice->order_no,
                    'vendor' => $invoice->vendor_name ?? '-',
                    'site_location' => $invoice->site_name ?? '-',
                    'grand_total' => $invoice->grand_total ?? 0,
                ];
            }
            
            return response()->json([
                'status' => true,
                'message' => 'Data fetched successfully',
                'data' => $formattedData
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getMaterialInwardDetails(Request $request)
    {
        try {
            $clientId = $request->scheme_id;
            
            if (!$clientId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Scheme not selected'
                ], 400);
            }
            
            // Get invoice details with all fields
            $invoice = DB::table('inv_detail')
                ->leftJoin('vendor', 'inv_detail.purchasefrom', '=', 'vendor.ID')
                ->leftJoin('scheme_step1', 'inv_detail.destination', '=', 'scheme_step1.ID')
                ->where('inv_detail.ID', $request->id)
                ->where('inv_detail.ClientID', $clientId)
                ->select(
                    'inv_detail.ID',
                    'inv_detail.Date',
                    'inv_detail.Invno as order_no',
                    'inv_detail.gtotal as grand_total',
                    'inv_detail.amt_b_tax as amount_before_tax',
                    'inv_detail.totcgst_amt as total_cgst',
                    'inv_detail.totsgst_amt as total_sgst',
                    'inv_detail.totigst_amt as total_igst',
                    'inv_detail.narration',
                    'inv_detail.loading',
                    'inv_detail.unloading',
                    'inv_detail.other',
                    'inv_detail.transport',
                    'inv_detail.round',
                    'inv_detail.attachment',
                    'inv_detail.purchasefrom as vendor_id',
                    'inv_detail.destination as scheme_id',
                    'vendor.Name as vendor_name',
                    'scheme_step1.Name as site_name'
                )
                ->first();
            
            if (!$invoice) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invoice not found'
                ], 404);
            }
            
            // Get invoice items with all details
            $items = DB::table('inv_product')
                ->leftJoin('material', 'inv_product.Material', '=', 'material.ID')
                ->where('inv_product.Invno', $request->id)
                ->select(
                    'inv_product.Material as material_id',
                    'inv_product.Qty as quantity',
                    'inv_product.Unit as unit',
                    'inv_product.Rate as rate',
                    'inv_product.Disc as discount',
                    'inv_product.Amount as taxable_amount',
                    'inv_product.CGST as cgst',
                    'inv_product.CGST_amt as cgst_amount',
                    'inv_product.SGST as sgst',
                    'inv_product.SGST_amt as sgst_amount',
                    'inv_product.IGST as igst',
                    'inv_product.IGST_amt as igst_amount',
                    'inv_product.Total as total',
                    'material.Name as material_name',
                    'material.Type as type'
                )
                ->get();
            
            $formattedItems = [];
            foreach ($items as $item) {
                $formattedItems[] = [
                    'material_id' => $item->material_id,
                    'material_name' => $item->material_name ?? '-',
                    'type_id' => $item->material_id, // Using material_id as type_id
                    'type' => $item->type ?? '-',
                    'unit' => $item->unit ?? '',
                    'quantity' => (float)($item->quantity ?? 0),
                    'rate' => (float)($item->rate ?? 0),
                    'discount' => (float)($item->discount ?? 0),
                    'taxable_amount' => (float)($item->taxable_amount ?? 0),
                    'cgst' => (float)($item->cgst ?? 0),
                    'cgst_amount' => (float)($item->cgst_amount ?? 0),
                    'sgst' => (float)($item->sgst ?? 0),
                    'sgst_amount' => (float)($item->sgst_amount ?? 0),
                    'igst' => (float)($item->igst ?? 0),
                    'igst_amount' => (float)($item->igst_amount ?? 0),
                    'total' => (float)($item->total ?? 0),
                ];
            }
            
            return response()->json([
                'status' => true,
                'message' => 'Data fetched successfully',
                'data' => [
                    'id' => $invoice->ID,
                    'date' => Carbon::parse($invoice->Date)->format('d-m-Y'),
                    'order_no' => $invoice->order_no,
                    'vendor' => $invoice->vendor_name ?? '-',
                    'vendor_id' => $invoice->vendor_id,
                    'site_location' => $invoice->site_name ?? '-',
                    'scheme_id' => $invoice->scheme_id,
                    'grand_total' => (float)($invoice->grand_total ?? 0),
                    'amount_before_tax' => (float)($invoice->amount_before_tax ?? 0),
                    'total_cgst' => (float)($invoice->total_cgst ?? 0),
                    'total_sgst' => (float)($invoice->total_sgst ?? 0),
                    'total_igst' => (float)($invoice->total_igst ?? 0),
                    'narration' => $invoice->narration ?? '',
                    'loading' => (float)($invoice->loading ?? 0),
                    'unloading' => (float)($invoice->unloading ?? 0),
                    'other' => (float)($invoice->other ?? 0),
                    'transport' => (float)($invoice->transport ?? 0),
                    'round' => (float)($invoice->round ?? 0),
                    'attachment' => $invoice->attachment ?? '',
                    'items' => $formattedItems
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch details: ' . $e->getMessage()
            ], 500);
        }
    }


    public function getNextInvoiceNo(Request $request)
    {
        try {
            $clientId = $request->scheme_id;
            
            if (!$clientId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Scheme not selected'
                ], 400);
            }
            
            $maxInvno = DB::table('inv_detail')
                ->where('ClientID', $clientId)
                ->max('Invno');
            
            $nextInvno = $maxInvno ? $maxInvno + 1 : 1;
            
            return response()->json([
                'status' => true,
                'message' => 'Next invoice number fetched successfully',
                'data' => [
                    'inv_no' => $nextInvno
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch next invoice number: ' . $e->getMessage()
            ], 500);
        }
    }


    public function getVendors(Request $request)
    {
        try {
            $clientId = $request->scheme_id;
            
            if (!$clientId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Scheme not selected'
                ], 400);
            }
            
            $vendors = DB::table('vendor')
                ->where('ClientID', $clientId)
                ->select('ID', 'Name')
                ->orderBy('Name', 'asc')
                ->get();
            
            return response()->json([
                'status' => true,
                'message' => 'Vendors fetched successfully',
                'data' => $vendors
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch vendors: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadFile(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|max:5120', // Max 5MB
            ]);
            
            $file = $request->file('file');
            
            // Generate unique filename
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
            
            // Store file in storage/app/public/attachments
            $path = $file->storeAs('uploads', $filename, 'public');
            
            return response()->json([
                'status' => true,
                'message' => 'File uploaded successfully',
                'filename' => $filename,
                'path' => $path
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function saveMaterialInward(Request $request)
    {
        try {
            $validated = $request->validate([
                'date' => ['required', 'date_format:d-m-Y'],
                'inv_no' => ['required', 'string'],
                'vendor_id' => ['required', 'string'],
                'scheme_id' => ['required', 'string'],
                'materials' => ['required', 'string'],
                'amount_before_tax' => ['required', 'numeric', 'min:0'],
                'total_cgst' => ['required', 'numeric', 'min:0'],
                'total_sgst' => ['required', 'numeric', 'min:0'],
                'total_igst' => ['required', 'numeric', 'min:0'],
                'grand_total' => ['required', 'numeric', 'min:0'],
                'user_id' => ['required', 'string'],
            ]);

            $clientId = $request->scheme_id;
            $materials = json_decode($request->materials, true);
            
            if (empty($materials)) {
                return response()->json([
                    'status' => false,
                    'message' => 'At least one material is required'
                ], 422);
            }

            $invId = $request->id ?? uniqid();
            $isUpdate = $request->has('id');

            // Handle nullable fields with default values
            $narration = $request->narration ?? '';
            $loading = $request->loading ?? 0;
            $unloading = $request->unloading ?? 0;
            $other = $request->other ?? 0;
            $transport = $request->transport ?? 0;
            $round = $request->round ?? 0;

            if ($isUpdate) {
                // Update existing invoice
                $invoice = DB::table('inv_detail')
                    ->where('ID', $invId)
                    ->where('ClientID', $clientId)
                    ->first();

                if (!$invoice) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Invoice not found'
                    ], 404);
                }

                // Update header
                DB::table('inv_detail')
                    ->where('ID', $invId)
                    ->update([
                        'Date' => Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d'),
                        'Invno' => $request->inv_no,
                        'purchasefrom' => $request->vendor_id,
                        'destination' => $request->scheme_id,
                        'total' => $request->amount_before_tax,
                        'amt_b_tax' => $request->amount_before_tax,
                        'totcgst_amt' => $request->total_cgst,
                        'totsgst_amt' => $request->total_sgst,
                        'totigst_amt' => $request->total_igst,
                        'narration' => $narration,
                        'loading' => $loading,
                        'unloading' => $unloading,
                        'other' => $other,
                        'transport' => $transport,
                        'round' => $round,
                        'gtotal' => $request->grand_total,
                        'userID' => $request->user_id,
                        'attachment' => $request->attachment ?? '',
                        'otrnar'=> '',
                        'LastEdited' => now(),
                    ]);

                // Delete old items
                DB::table('inv_product')->where('Invno', $invId)->delete();
            } else {
                // Create new invoice
                DB::table('inv_detail')->insert([
                    'ID' => $invId,
                    'ClientID' => $clientId,
                    'Date' => Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d'),
                    'Invno' => $request->inv_no,
                    'purchasefrom' => $request->vendor_id,
                    'destination' => $request->scheme_id,
                    'total' => $request->amount_before_tax,
                    'amt_b_tax' => $request->amount_before_tax,
                    'totcgst_amt' => $request->total_cgst,
                    'totsgst_amt' => $request->total_sgst,
                    'totigst_amt' => $request->total_igst,
                    'narration' => $narration,
                    'loading' => $loading,
                    'unloading' => $unloading,
                    'other' => $other,
                    'transport' => $transport,
                    'round' => $round,
                    'gtotal' => $request->grand_total,
                    'userID' => $request->user_id,
                    'attachment' => $request->attachment ?? '',
                    'otrnar'=> '',
                    'Created' => now(),
                    'LastEdited' => now(),
                ]);
            }

            // Insert new items
            foreach ($materials as $material) {
                // Get material ID
                $materialId = DB::table('material')
                    ->where('Name', $material['material_name'])
                    ->where('Type', $material['type_name'])
                    ->value('ID');

                $taxable = floatval($material['taxable_amount']);
                $cgst = floatval($material['cgst']);
                $sgst = floatval($material['sgst']);
                $igst = floatval($material['igst']);

                $cgst_amt = ($taxable * $cgst) / 100;
                $sgst_amt = ($taxable * $sgst) / 100;
                $igst_amt = ($taxable * $igst) / 100;

                DB::table('inv_product')->insert([
                    'ID' => uniqid(),
                    'ClientID' => $clientId,
                    'Invno' => $invId,
                    'Created' => now(),
                    'LastEdited' => now(),
                    'Material' => $materialId,
                    'Vat' => 0,
                    'Qty' => floatval($material['quantity']),
                    'Rate' => floatval($material['rate']),
                    'Disc' => floatval($material['discount']),
                    'Amount' => $taxable,
                    'Unit' => $material['unit'] ?? '',
                    'CGST' => $cgst,
                    'SGST' => $sgst,
                    'IGST' => $igst,
                    'CGST_amt' => $cgst_amt,
                    'SGST_amt' => $sgst_amt,
                    'IGST_amt' => $igst_amt,
                    'userID' => $request->user_id,
                    'Total' => floatval($material['total']),
                ]);
            }

            $message = $isUpdate ? 'Purchase invoice updated successfully' : 'Purchase invoice saved successfully';

            return response()->json([
                'status' => true,
                'message' => $message,
                'data' => ['id' => $invId]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in saveMaterialInward: ' . $e->getMessage());
            
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAttachment(Request $request)
    {
        $request->validate([
            'filename' => 'required|string'
        ]); 
        
        $filename = $request->filename;
        $path = storage_path('app/public/uploads/' . $filename);
        
        if (!file_exists($path)) {
            return response()->json([
                'status' => false,
                'message' => 'File not found'
            ], 404);
        }
        
        // Read the file and return as base64 or file content
        $fileContent = file_get_contents($path);
        $base64 = base64_encode($fileContent);
        
        return response()->json([
            'status' => true,
            'filename' => $filename,
            'content' => $base64,
            'mime_type' => mime_content_type($path)
        ]);
    }

    public function getSchemesForEnquiry(Request $request)
    {
        try {
            $schemes = DB::table('scheme_step1')
                ->select('ID', 'Name')
                ->orderBy('Name', 'asc')
                ->get();
            
            return response()->json([
                'status' => true,
                'message' => 'Schemes fetched successfully',
                'data' => $schemes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch schemes: ' . $e->getMessage()
            ], 500);
        }
    }

    public function submitEnquiry(Request $request)
    {
        try {
            $request->validate([
                'customer_name' => 'required|string|max:50',
                'email' => 'nullable|email|max:50',
                'phone_no' => 'required|string|max:10',
                'address' => 'required|string',
                'scheme_id' => 'required|string|max:30',
                'bill_no' => 'nullable|string|max:30',
                'queries' => 'required|string',
                'user_id' => 'required|string',
            ]);
            
            $enquiryId = uniqid();
            
            DB::table('enquiries')->insert([
                'ID' => $enquiryId,
                'customer_name' => $request->customer_name,
                'email' => $request->email,
                'phone_no' => $request->phone_no,
                'address' => $request->address,
                'scheme_id' => $request->scheme_id,
                'bill_no' => $request->bill_no,
                'queries' => $request->queries,
                'status' => 'pending',
                'Created' => now(),
                'LastEdited' => now(),
            ]);
            
            return response()->json([
                'status' => true,
                'message' => 'Enquiry submitted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to submit enquiry: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getEnquiries(Request $request)
    {
        try {
            $request->validate([
                'scheme_id' => 'required|string',
            ]);
            
            
            $enquiries = DB::table('enquiries')
                ->where('scheme_id', $request->scheme_id)
                ->orderBy('Created', 'desc')
                ->get();
            
            return response()->json([
                'status' => true,
                'message' => 'Enquiries fetched successfully',
                'data' => $enquiries
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch enquiries: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateEnquiryStatus(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|string',
                'status' => 'required|string|in:pending,processing,completed',
                'scheme_id' => 'required|string',
            ]);
            
            DB::table('enquiries')
                ->where('ID', $request->id)
                ->where('scheme_id', $request->scheme_id)
                ->update([
                    'status' => $request->status,
                    'LastEdited' => now(),
                ]);
            
            return response()->json([
                'status' => true,
                'message' => 'Enquiry status updated successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }
}   
