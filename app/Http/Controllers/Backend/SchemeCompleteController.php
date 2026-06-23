<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Backend\Role;
use App\Models\Backend\Permission;
use App\Models\Backend\SchemeDetail;
use App\Models\Backend\Bank_Acc;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; 
use Illuminate\Validation\Rule;

class SchemeCompleteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
  public function index(Request $request)
{
    if ($request->ajax()) {
        $query = SchemeDetail::select(['ID','Name','contactperson','Location','cnumber','Created','completFalg']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('completFalg', function ($row) { // ✅ fixed: removed trailing space
                $checked = $row->completFalg ? 'checked' : '';
                return "<input type='checkbox' class='flag-toggle' data-id='$row->ID' $checked>";
            })
            ->rawColumns(['completFalg']) // ✅ fixed
            ->make(true);
    }

    return view('backend.SchemeComplete.index');
}


// PartnerLoanInvestorController.php
public function toggleStatus(Request $request)
{
    $request->validate([
        'id'   => 'required|exists:scheme_step1,ID',
        'flag' => 'required|boolean'
    ]);

    $scheme        = SchemeDetail::findOrFail($request->id);
    $scheme->completFalg = $request->flag;
    $scheme->save();

    return response()->json(['success' => true]);
}

}
