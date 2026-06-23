<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Backend\Role;
use App\Models\Backend\Permission;
use App\Models\Backend\Scheme;
use App\Models\Backend\Supplier_contractor;
use App\Models\Backend\Scheme_Flat;
use App\Models\Backend\Flat_details;
use Illuminate\Support\Str;
use App\Models\User;

class SchemeController extends Controller
{
//   public function getSchemenames()
// {
    
//     $schemes = Scheme::select('ID', 'Name')->orderBy('Name')->get();
//     return response()->json($schemes);
// }

    // public function getSchemenames()
    // {
        
    //     $user = auth()->user();

    //     if ($user->Role == 1) {
    //         // Admin: return all schemes
    //         $schemes = Scheme::select('ID', 'Name')->orderBy('Name')->get();
    //     } else {
    //         // Other users: return only assigned schemes
    //         // Decode JSON stored in 'scheme' column
    //         $assignedSchemeIds = json_decode($user->scheme, true) ?? [];

    //         $schemes = Scheme::select('ID', 'Name')
    //             ->whereIn('ID', $assignedSchemeIds)
    //             ->orderBy('Name')
    //             ->get();
    //     }

    //     return response()->json($schemes);
    // }

    public function getSchemenames()
{
    $user = auth()->user();

    if ($user->Role == 1) {
        $schemes = Scheme::select('ID', 'Name')
            ->orderBy('Name')
            ->get();
    } else {

        $assignedSchemeIds = json_decode($user->scheme, true);

        // Single scheme support
        if (!is_array($assignedSchemeIds)) {
            $assignedSchemeIds = [$user->scheme];
        }

        $schemes = Scheme::select('ID', 'Name')
            ->whereIn('ID', $assignedSchemeIds)
            ->orderBy('Name')
            ->get();
    }

    return response()->json($schemes);
}

public function setSession(Request $request)
{ 
    $request->validate([
        'scheme_id' => 'required|exists:scheme_step1,ID', // ✅ expects 'scheme_id'
    ]);

    session(['selected_scheme_id' => $request->scheme_id]); // ✅ sets to session

    return response()->json(['success' => true, 'scheme_id' => $request->scheme_id]);
}

public function addRow(Request $request)
{
    $uid = $request->input('Count'); // or use uniqid()
    
    $types = Scheme_Flat::select('Type')
        ->where('ClientID', session('selected_scheme_id'))
        ->groupBy('Type')
        ->pluck('Type')
        ->toArray();

    $defaultTypes = ['bhk', '1bhk', '2bhk', '3bhk', '4bhk'];
    $otherTypes = array_diff($types, $defaultTypes);

    return view('backend.FlatDetails.scheme_row', ['uid' => $uid, 'otherTypes' => $otherTypes]);
}

public function flatDetails(Request $request)
{
    $flatCount = $request->input('FlatRow');
    $flatsRaw = $request->input('FlatsTp');
    $schemeID = $request->input('SchmID');
    $task = $request->input('Task');

    // You can parse $flatsRaw and render HTML for details dynamically here.
    // For now, a simple response:
    $html = "<div class='alert alert-success'>Showing $flatCount flat rows for Scheme: $schemeID</div>";

    return response()->json(['html' => $html]);
}

public function getFlatDetails(Request $request)
    {
        $flatRow = $request->input('FlatRow');
        $flatsTp = $request->input('FlatsTp');
        $schemeId = $request->input('SchmID');
        $task = $request->input('Task');

        $flatsTpArray = array_filter(explode('@@', trim($flatsTp, '@@')));
        $allDetails = [];

        $i = 0;

        ob_start(); // capture HTML output like legacy PHP

        echo '<hr><h4>Flats Details</h4>';
        echo '<table class="table table-striped table-bordered"><thead><tr>
                <th>Flat Type</th><th>Wing</th><th>Floor</th><th style="min-width: 79px;">Flat No</th>
                <th>C.Area</th><th>Balcony</th><th>En.Balcony</th><th>Terrace</th>
                <th>P.C.Area</th><th>Total Sq Ft</th><th>Total Sq Mtr</th><th>Action</th>
              </tr></thead><tbody>';

        foreach ($flatsTpArray as $pair) {
            [$flatType, $flatCount] = explode('##', $pair);
            $flatCount = (int)$flatCount;

            $existingFlats = ($task == 'update' || $task == 'view') ?
                Flat_details::where('scheme_ID', $schemeId)
                          ->where('FlatType', $flatType)
                          ->where('ClientID', $schemeId)
                          ->orderBy('Wing')
                          ->orderBy('FlatNo')
                          ->get() : collect();

            $totalRows = $flatCount - $existingFlats->count();
            foreach ($existingFlats as $flat) {
                $i++;
                echo view('backend.FlatDetails.flat_row', compact('i', 'flat', 'flatType'))->render();
            }

            for ($j = 1; $j <= $totalRows; $j++) {
                $i++;
                $flat = null;
                echo view('backend.FlatDetails.flat_row', compact('i', 'flat', 'flatType'))->render();
            }
        }

        echo '</tbody><input type="hidden" name="Rowcnt" id="Rowcnt" value="' . $i . '"></table>';
        $html = ob_get_clean();

        return response($html);
    }
    public function getFlatTypes(Request $request)
    {
        $schemeId = $request->input('scheme_id');

        $flatTypes = Scheme_Flat::where('scheme_ID', $schemeId)
            ->select('Type', 'NoOfFlat')
            ->get();

        return response()->json($flatTypes);
    }
}