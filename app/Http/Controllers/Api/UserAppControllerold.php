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
        // $user = $request->user();

        // if (!$user) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Invalid or inactive user'
        //     ], 401);
        // }

        $user = User::findorfail(3);
        
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

    // LIST API
        public function materialConsumptionList()
        {
        $data = Consumption_Details::with([
            'materials.materialDetail'
        ])
        ->orderBy('Created', 'desc')
        ->get();

            return response()->json([
                'status' => true,
                'message' => 'Data fetched successfully',
                'data' => $data
            ]);
        }

        // STORE API
        public function materialConsumptionStore(Request $request)
        {
            
            //     $user = auth()->user();

            // if (!$user) {
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Unauthenticated'
            //     ], 401);
            // }
            
            $request->validate([
                'Date'        => 'required|date_format:d-m-Y',
                'scheme'      => 'required',
                'material_id' => 'required|array',
                'unit'        => 'required|array',
                'quantity'    => 'required|array',
            ]);

            DB::beginTransaction();

            try {

                $parentId = uniqid();

                DB::table('consumption_detail')->insert([
                    'ID'         => $parentId,
                    'ClientID' => $request->ClientID,
                    'Date'       => Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d'),
                    'scheme'     => $request->scheme,
                    'Created'    => now(),
                    'Lastedited' => now(),
                    'appflag'    => 0,
                    'userID'   => $request->userID,
                ]);

                foreach ($request->material_id as $key => $material) {

                    DB::table('material_consumption')->insert([
                        'ID'         => uniqid(),
                        'ClientID' => $request->ClientID,
                        'Cid'        => $parentId,
                        'Date'       => Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d'),
                        'Material'   => $material,
                        'Unit'       => $request->unit[$key],
                        'Qty'        => $request->quantity[$key],
                        'Created'    => now(),
                        'Lastedited' => now(),
                        'userID'   => $request->userID,
                    ]);
                }

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Record added successfully'
                ]);

            } catch (\Exception $e) {

                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
        }

        // SINGLE VIEW API
        public function materialConsumptionShow($id)
        {

            $data = DB::table('consumption_detail')
                ->where('ID', trim($id))
                ->first();

            if (!$data) {

                return response()->json([

                    'status' => false,
                    'message' => 'Record not found'

                ], 404);
            }

            $materials = DB::table('material_consumption')
                ->where('Cid', trim($id))
                ->get();

            return response()->json([

                'status' => true,
                'data' => $data,
                'materials' => $materials

            ]);
        }

        // UPDATE API
        public function materialConsumptionUpdate(Request $request, $id)
        {
            $request->validate([
                'Date'        => 'required|date_format:d-m-Y',
                'scheme'      => 'required',
            ]);

            DB::beginTransaction();

            try {

                DB::table('consumption_detail')
                    ->where('ID', $id)
                    ->update([
                        'Date'       => Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d'),
                        'scheme'     => $request->scheme,
                        'Lastedited' => now(),
                        'ClientID' => $request->ClientID,
                        'userID'   => $request->userID,
                    ]);

                DB::table('material_consumption')
                    ->where('Cid', $id)
                    ->delete();

                foreach ($request->material_id as $key => $material) {

                    DB::table('material_consumption')->insert([
                        'ID'         => uniqid(),
                         'ClientID'   => $request->ClientID,
                        'Cid'        => $id,
                        'Date'       => Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d'),
                        'Material'   => $material,
                        'Unit'       => $request->unit[$key],
                        'Qty'        => $request->quantity[$key],
                        'Created'    => now(),
                        'Lastedited' => now(),
                        'userID'     => $request->userID,
                    ]);
                }

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Record updated successfully'
                ]);

            } catch (\Exception $e) {

                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
        }

        // DELETE API
        public function materialConsumptionDelete($id)
        {
            DB::beginTransaction();

            try {

                DB::table('material_consumption')
                    ->where('Cid', $id)
                    ->delete();

                DB::table('consumption_detail')
                    ->where('ID', $id)
                    ->delete();

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Record deleted successfully'
                ]);

            } catch (\Exception $e) {

                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
        }


        // <---- Transfer_material ---->

        public function transferMaterialStore(Request $request)
    {
        $request->validate([

            'Date'         => 'required|date_format:d-m-Y',
            'from_site'    => 'required',
            'To_site'      => 'required',
            'transport' => 'required',
            'material_id'  => 'required|array',
            'quantity'     => 'required|array',
            'rate'         => 'required|array',
            'total'        => 'required|array',

            'ClientID'     => 'required',
            'userID'       => 'required',

        ]);

        DB::beginTransaction();

        try {

            $parentId = uniqid();

            DB::table('transfer_detail')->insert([

                'ID'         => $parentId,
                'ClientID'   => $request->ClientID,
                'Srno' => 1,
                'Date'       => Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d'),
                'from_site'  => $request->from_site,
                'To_site'    => $request->To_site,
                 'transport'  => $request->transport,
                 'loading' => $request->loading,
                 'round' => 0,
                'gtotal'     => array_sum($request->total),
                'Created'    => now(),
                'Lastedited' => now(),
                'userID'     => $request->userID,

            ]);

            foreach ($request->material_id as $key => $material) {

                DB::table('transfer_material')->insert([

                    'ID'         => uniqid(),
                    'ClientID'   => $request->ClientID,
                    
                    'Created'    => now(),
                    'LastEdited' => now(),

                    'PID'        => $parentId,

                    'matrialID'  => $material,

                    'from_site'  => $request->from_site,

                    'To_site'    => $request->To_site,

                    'qty'        => $request->quantity[$key],

                    'rate'       => $request->rate[$key],

                    'Amount'     => $request->total[$key],

                    'userID'     => $request->userID,

                ]);
            }

            DB::commit();

            return response()->json([

                'status' => true,
                'message' => 'Transfer Material Added Successfully'

            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([

                'status' => false,
                'message' => $e->getMessage()

            ], 500);
        }
    }

    // SHOW API
        public function transferMaterialShow($id)
        {
            $data = DB::table('transfer_detail')
                ->where('ID', $id)
                ->first();

            if (!$data) {

                return response()->json([

                    'status' => false,
                    'message' => 'Record not found'

                ], 404);
            }

            $materials = DB::table('transfer_material')
                ->where('PID', $id)
                ->get();

            return response()->json([

                'status' => true,
                'data' => $data,
                'materials' => $materials

            ]);
        }

        // UPDATE API
        public function transferMaterialUpdate(Request $request, $id)
        {
            $request->validate([

                'Date'         => 'required|date_format:d-m-Y',
                'from_site'    => 'required',
                'To_site'      => 'required',

                'material_id'  => 'required|array',
                'quantity'     => 'required|array',
                'rate'         => 'required|array',
                'total'        => 'required|array',

                'ClientID'     => 'required',
                'userID'       => 'required',

            ]);

            DB::beginTransaction();

            try {

                DB::table('transfer_detail')
                    ->where('ID', $id)
                    ->update([

                        'Date'       => Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d'),

                        'from_site'  => $request->from_site,

                        'To_site'    => $request->To_site,

                        'gtotal'     => array_sum($request->total),

                        'Lastedited' => now(),

                        'userID'     => $request->userID,

                    ]);

                DB::table('transfer_material')
                    ->where('PID', $id)
                    ->delete();

                foreach ($request->material_id as $key => $material) {

                    DB::table('transfer_material')->insert([

                        'ID'         => uniqid(),

                        'ClientID'   => $request->ClientID,

                        'Created'    => now(),

                        'LastEdited' => now(),

                        'PID'        => $id,

                        'matrialID'  => $material,

                        'from_site'  => $request->from_site,

                        'To_site'    => $request->To_site,

                        'qty'        => $request->quantity[$key],

                        'rate'       => $request->rate[$key],

                        'Amount'     => $request->total[$key],

                        'userID'     => $request->userID,

                    ]);
                }

                DB::commit();

                return response()->json([

                    'status' => true,
                    'message' => 'Record updated successfully'

                ]);

            } catch (\Exception $e) {

                DB::rollBack();

                return response()->json([

                    'status' => false,
                    'message' => $e->getMessage()

                ], 500);
            }
        }

        // DELETE API
        public function transferMaterialDelete($id)
        {
            DB::beginTransaction();

            try {

                DB::table('transfer_material')
                    ->where('PID', $id)
                    ->delete();

                DB::table('transfer_detail')
                    ->where('ID', $id)
                    ->delete();

                DB::commit();

                return response()->json([

                    'status' => true,
                    'message' => 'Record deleted successfully'

                ]);

            } catch (\Exception $e) {

                DB::rollBack();

                return response()->json([

                    'status' => false,
                    'message' => $e->getMessage()

                ], 500);
            }
        }

        // LIST API
        public function transferMaterialList()
        {
            $data = DB::table('transfer_detail')
                ->orderBy('Created', 'DESC')
                ->get();

            return response()->json([

                'status' => true,
                'data' => $data

            ]);
        }
}
