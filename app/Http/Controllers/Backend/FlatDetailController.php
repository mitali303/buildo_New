<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Backend\Role;
use App\Models\Backend\Permission;
use App\Models\Backend\SchemeDetail;
use App\Models\Backend\Scheme;
use App\Models\Backend\Booking_Customer;
use App\Models\Backend\Booking_Cancel;
use App\Models\Backend\Bank_Acc;
use App\Models\Backend\Flat_details;
use App\Models\Backend\Scheme_Flat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; 
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class FlatDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $selectedSchemeId = session('selected_scheme_id');

            $schemes = SchemeDetail::where('completFalg', 0)
                ->when($selectedSchemeId, function ($query) use ($selectedSchemeId) {
                    $query->where('ID', $selectedSchemeId);
                })
                ->get();

            $data = $schemes->map(function ($scheme, $key) {

                $schemeId = $scheme->ID;

                /*
                |--------------------------------------------------------------------------
                | TOTAL FLATS (ONLY FOR PROJECT)
                |--------------------------------------------------------------------------
                */
                $totalFlats = 0;
                if ($scheme->type === 'project') {
                    $totalFlats = Scheme_Flat::where('ClientID', $schemeId)->sum('NoOfFlat');
                }

                /*
                |--------------------------------------------------------------------------
                | SOLD FLATS
                |--------------------------------------------------------------------------
                */
                $cancelledFlatIds = Booking_Cancel::where('SchemID', $schemeId)
                    ->pluck('flatID')
                    ->toArray();

                $bookings = Booking_Customer::where('Scheme', $schemeId)
                    ->whereNotIn('ID', $cancelledFlatIds)
                    ->get();

                $bookedFlatNosAll = $bookings->pluck('FlatNo')->unique()->toArray();

                $soldFlatsCount = 0;
                if ($scheme->type === 'project') {
                    $soldFlatsCount = Flat_details::where('scheme_ID', $schemeId)
                        ->whereIn('ID', $bookedFlatNosAll)
                        ->count();
                }

                /*
                |--------------------------------------------------------------------------
                | AVERAGE RATE (ONLY FOR PROJECT)
                |--------------------------------------------------------------------------
                */
                $avgRate = 0;

                if ($scheme->type === 'project') {

                    $lumpsumAmount = Booking_Customer::where('Scheme', $schemeId)
                        ->where('Ptype', 0)
                        ->sum('TotalFlatAmt');

                    $bspAmount = Booking_Customer::where('Scheme', $schemeId)
                        ->where('Ptype', 1)
                        ->sum('BspAmount');

                    $totalAmount = $lumpsumAmount + $bspAmount;

                    $totalSqFt = Flat_details::where('scheme_ID', $schemeId)
                        ->sum('TotalSqFt');

                    $avgRate = ($totalSqFt > 0) ? round($totalAmount / $totalSqFt) : 0;
                }

                return [
                    'DT_RowIndex' => $key + 1,
                    'ID' => $scheme->ID,
                    'Name' => $scheme->Name,
                    'Address' => $scheme->Address,
                    'cperson' => $scheme->cperson,
                    'Area' => $scheme->Area,
                    'TotalFlats' => $scheme->type === 'individual' ? '-' : $totalFlats,
                    'SoldFlats' => $scheme->type === 'individual' ? '-' : $soldFlatsCount,
                    'AvgRate' => $scheme->type === 'individual' ? '-' : $avgRate,
                ];

            })->values();

            return DataTables::of($data)
                ->addColumn('actions', function ($row) {

                    $editUrl = route('Flat.edit', $row['ID']);
                    $deleteUrl = route('Flat.delete', $row['ID']);
                    $viewUrl = route('scheme.flatDetails');
                    $formId = 'delete-form-' . $row['ID'];

                    $actions = '<a href="#" class="me-2 text-info view-flat-details" data-scheme-id="' . $row['ID'] . '" data-scheme-name="' . htmlspecialchars($row['Name']) . '">
                        <i data-feather="eye"></i>
                    </a>';

                    if (hasPermission('edit_flat_detail')) {
                        $actions .= '
                        <a href="' . $editUrl . '" class="me-2 text-primary">
                            <i data-feather="edit-2"></i>
                        </a>';
                    }

                    if (hasPermission('delete_flat_detail')) {
                        $actions .= '
                        <a href="#" class="text-danger delete-confirm" data-id="' . $formId . '">
                            <i data-feather="trash"></i>
                        </a>
                        <form id="' . $formId . '" action="' . $deleteUrl . '" method="POST" class="d-none">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                        </form>';
                    }

                    return $actions;
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('backend.FlatDetails.index');
    }

public function getSchemeDetails($id)
{
    $scheme = SchemeDetail::where('ID', $id)
                ->where('CreateFlag', '0')
                ->first(['Address', 'Location', 'Email', 'cperson', 'cnumber']);

    return response()->json($scheme);
}


    /**
     * Show the form for creating a new resource. 
     */
   public function create()
{
    $owners = SchemeDetail::select('cperson')
                ->groupBy('cperson')
                ->get();
    $schemes      = SchemeDetail::select('ID','Name')->where('CreateFlag', '0')->get();

    return view('backend.FlatDetails.create', [
        'scheme' => null,
        'cashBalance' => '',
        'owners' => $owners,
        'schemes' => $schemes
    ]);
}


public function store(Request $request)
{
    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */
    $validator = Validator::make($request->all(), [
        'scheme'              => 'nullable|exists:scheme_step1,ID',
        'category'            => 'required|array|min:1',
        'area'                => 'required|numeric|min:0',
        'gov_rate'            => 'required|numeric|min:0',
        'TotalareaAmount'     => 'required|numeric|min:0',
        'type'                => 'required|in:project,individual',

        // Project only
        'builtuparea'         => 'required_if:type,project|nullable|numeric|min:0',

        // Individual only
        'head_roomarea'       => 'required_if:type,individual|nullable|numeric|min:0',
        'head_rate'           => 'required_if:type,individual|nullable|numeric|min:0',
        'TotalheadAmount'     => 'required_if:type,individual|nullable|numeric|min:0',
        'TotalProjectAmount'  => 'required_if:type,individual|nullable|numeric|min:0',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    DB::beginTransaction();

    try {

        $schemeId = $request->scheme;

        // Safe numeric handling
        $builtupArea      = $request->type === 'project' ? ($request->builtuparea ?: 0) : null;
        $headRoomArea     = $request->type === 'individual' ? ($request->head_roomarea ?: 0) : 0;
        $headRate         = $request->type === 'individual' ? ($request->head_rate ?: 0) : 0;
        $totalHeadAmount  = $request->type === 'individual' ? ($request->TotalheadAmount ?: 0) : 0;
        $totalProjectAmt  = $request->type === 'individual' ? ($request->TotalProjectAmount ?: 0) : 0;

        /*
        |--------------------------------------------------------------------------
        | UPDATE SCHEME DETAILS
        |--------------------------------------------------------------------------
        */
        SchemeDetail::where('ID', $schemeId)->update([
            'LastEdited'         => now(),
            'Category'           => implode(',', $request->category ?? []),
            'Amenities'          => implode(',', $request->amenities ?? []),
            'Ami_img'            => implode(',', $request->uploadfile ?? []),
            'Area'               => $request->area,
            'Detail'             => $request->detail,
            'gov_rate'           => $request->gov_rate,
            'TotalareaAmount'    => $request->TotalareaAmount,
            'type'               => $request->type,
            'BuiltupArea'        => $builtupArea,
            'head_roomarea'      => $headRoomArea,
            'head_rate'          => $headRate,
            'TotalheadAmount'    => $totalHeadAmount,
            'TotalProjectAmount' => $totalProjectAmt,
            'CreateFlag'         => 1,
            'userID'             => auth()->id(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | INSERT FLATS (ONLY FOR PROJECT)
        |--------------------------------------------------------------------------
        */
        if ($request->type === 'project') {

            $rowIds = $request->row_ids ?? [];

            foreach ($rowIds as $rowId) {

                $typeKey  = "ftype{$rowId}";
                $countKey = "flatcnt{$rowId}";

                if ($request->filled($typeKey)) {

                    Scheme_Flat::create([
                        'ID'        => uniqid(),
                        'ClientID'  => $schemeId,
                        'scheme_ID' => $schemeId,
                        'Type'      => $request->$typeKey,
                        'NoOfFlat'  => $request->$countKey ?: 0,
                        'userID'    => auth()->id(),
                    ]);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | INSERT OWNER TITLES (ONLY FOR INDIVIDUAL)
        |--------------------------------------------------------------------------
        */
        if ($request->type === 'individual') {

            for ($i = 1; $i <= $request->cnt2; $i++) {

                $title = $request->input("title{$i}");
                $amt   = $request->input("amt{$i}");

                if (!empty($title)) {

                    DB::table('owner')->insert([
                        'id'         => uniqid(),
                        'ClientID'   => $schemeId,
                        'scheme_ID'  => $schemeId,
                        'title'      => $title,
                        'amt'        => $amt ?: 0,
                        'Created'    => now(),
                        'LastEdited' => now(),
                    ]);
                }
            }
        }

        DB::commit();

        return response()->json([
            'success'   => true,
            'scheme_id' => $schemeId
        ]);

    } catch (\Exception $e) {

        DB::rollback();

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}


public function saveRow(Request $request)
{
    $data = $request->only([
        'scheme_ID', 'ClientID', 'FlatType', 'Wing', 'Floor', 'FlatNo',
        'Area', 'Other1', 'Other2', 'Terrace', 'FlatAttribute',
        'TotalSqFt', 'TotalSqMtr'
    ]);

    $data['ID'] = uniqid();
    $data['userID'] = auth()->id(); // or session('userID') if you’re using custom session
    $data['Created'] = now(); // or session('userID') if you’re using custom session
    $data['LastEdited'] = now(); // or session('userID') if you’re using custom session
    Flat_details::create($data);

    return response()->json(['success' => true]);
}

   
public function edit($id)
{
    $flat = SchemeDetail::findOrFail($id);

    $amiImages = explode(',', $flat->Ami_img);

    $owners = SchemeDetail::select('cperson')->groupBy('cperson')->get();
    $schemes = SchemeDetail::select('ID','Name')->where('ID', $id)->get();

    $flatRows = Scheme_Flat::where('scheme_ID', $id)->get();
    $flatDetails = Flat_details::where('scheme_ID', $id)->get();

    // ✅ ADD THIS
    $titleAmounts = DB::table('owner')
                        ->where('scheme_ID', $id)
                        ->get();

    return view('backend.FlatDetails.create', [
        'scheme'        => $flat->scheme_ID,
        'cashBalance'   => '',
        'owners'        => $owners,
        'schemes'       => $schemes,
        'flat'          => $flat,
        'amiImages'     => $amiImages,
        'flatDetails'   => $flatDetails,
        'titleAmounts'  => $titleAmounts // ✅ PASS THIS
    ]);
}

public function update(Request $request)
{
    $validator = Validator::make($request->all(), [
        'scheme'              => 'required|exists:scheme_step1,ID',
        'category'            => 'required|array|min:1',
        'area'                => 'required|numeric|min:0',
        'gov_rate'            => 'required|numeric|min:0',
        'TotalareaAmount'     => 'required|numeric|min:0',
        'type'                => 'required|in:project,individual',
        'builtuparea'         => 'required_if:type,project|nullable|numeric|min:0',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    DB::beginTransaction();

    try {

        $schemeId = $request->scheme;

        $builtupArea      = $request->type === 'project' ? ($request->builtuparea ?: 0) : null;
        $headRoomArea     = $request->type === 'individual' ? ($request->head_roomarea ?: 0) : 0;
        $headRate         = $request->type === 'individual' ? ($request->head_rate ?: 0) : 0;
        $totalHeadAmount  = $request->type === 'individual' ? ($request->TotalheadAmount ?: 0) : 0;
        $totalProjectAmt  = $request->type === 'individual' ? ($request->TotalProjectAmount ?: 0) : 0;

        /*
        |--------------------------------------------------------------------------
        | UPDATE SCHEME
        |--------------------------------------------------------------------------
        */
        SchemeDetail::where('ID', $schemeId)->update([
            'LastEdited'         => now(),
            'Category'           => implode(',', $request->category ?? []),
            'Amenities'          => implode(',', $request->amenities ?? []),
            'Ami_img'            => implode(',', $request->uploadfile ?? []),
            'Area'               => $request->area,
            'Detail'             => $request->detail,
            'gov_rate'           => $request->gov_rate,
            'TotalareaAmount'    => $request->TotalareaAmount,
            'type'               => $request->type,
            'BuiltupArea'        => $builtupArea,
            'head_roomarea'      => $headRoomArea,
            'head_rate'          => $headRate,
            'TotalheadAmount'    => $totalHeadAmount,
            'TotalProjectAmount' => $totalProjectAmt,
            'userID'             => auth()->id(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | DELETE OLD DATA
        |--------------------------------------------------------------------------
        */
        Scheme_Flat::where('scheme_ID', $schemeId)->delete();
        // Flat_details::where('scheme_ID', $schemeId)->delete();
        DB::table('owner')->where('scheme_ID', $schemeId)->delete();

        /*
        |--------------------------------------------------------------------------
        | REINSERT BASED ON TYPE
        |--------------------------------------------------------------------------
        */
        if ($request->type === 'project') {

            $rowIds = $request->row_ids ?? [];

            foreach ($rowIds as $rowId) {

                $typeKey  = "ftype{$rowId}";
                $countKey = "flatcnt{$rowId}";

                if ($request->filled($typeKey)) {

                    Scheme_Flat::create([
                        'ID'        => uniqid(),
                        'ClientID'  => $schemeId,
                        'scheme_ID' => $schemeId,
                        'Type'      => $request->$typeKey,
                        'NoOfFlat'  => $request->$countKey ?: 0,
                        'userID'    => auth()->id(),
                    ]);
                }
            }

        } else {

            for ($i = 1; $i <= $request->cnt2; $i++) {

                $title = $request->input("title{$i}");
                $amt   = $request->input("amt{$i}");

                if (!empty($title)) {

                    DB::table('owner')->insert([
                        'id'         => uniqid(),
                        'ClientID'   => $schemeId,
                        'scheme_ID'  => $schemeId,
                        'title'      => $title,
                        'amt'        => $amt ?: 0,
                        'Created'    => now(),
                        'LastEdited' => now(),
                    ]);
                }
            }
        }

        DB::commit();

        return response()->json([
            'success'   => true,
            'scheme_id' => $schemeId
        ]);

    } catch (\Exception $e) {

        DB::rollback();

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

public function updateRow(Request $request)
{
    $data = $request->only([
        'FlatType', 'Wing', 'Floor', 'FlatNo', 'Area', 'Other1', 'Other2', 'Terrace',
        'FlatAttribute', 'TotalSqFt', 'TotalSqMtr'
    ]);

    $flat = Flat_details::findOrFail($request->ID);

    $flat->update(array_merge($data, [
        'LastEdited' => now(),
        'userID' => auth()->id(),
    ]));

    return response()->json(['success' => true]);
}


  public function destroy($id)
{
    SchemeDetail::where('ID', $id)->update([
        'LastEdited' => now(),
        'CreateFlag' => '0',
    ]);

    Scheme_Flat::where('scheme_ID', $id)
               ->where('ClientID', $id)
               ->delete();

    Flat_details::where('scheme_ID', $id)
                ->where('ClientID', $id)
                ->delete();

    return redirect()
        ->route('Flat')
        ->with('success', 'Record has been deleted successfully!');
}


}