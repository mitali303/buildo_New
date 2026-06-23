<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Backend\Agency;
use App\Models\Backend\SchemeDetail;
use App\Models\Backend\Labour_Work;
use Illuminate\Support\Facades\Auth;
use Session;
use Carbon\Carbon;

class LabourWorkController extends Controller
{
    /**
     * Display listing
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            
            $clientId = Session::get('selected_scheme_id');

            $query = Labour_Work::with('agency', 'scheme')
                ->select('*')
                ->where('schemeID', $clientId)
                ->orderBy('Date', 'DESC');

            return DataTables::of($query)
                ->addIndexColumn()
                ->filter(function ($query) {

                    $search = request('search.value');

                    if ($search) {

                        $query->where(function ($q) use ($search) {

                            $q->where('Date', 'like', "%{$search}%")
                            ->orWhere('gtotal', 'like', "%{$search}%")

                            ->orWhereHas('scheme', function ($sq) use ($search) {
                                $sq->where('Name', 'like', "%{$search}%");
                            })

                            ->orWhereHas('agency', function ($sq) use ($search) {
                                $sq->where('Name', 'like', "%{$search}%");
                            });

                        });
                    }

                }, false)
                ->editColumn('Date', function ($row) {
                    return \Carbon\Carbon::parse($row->Date)->format('d-m-Y');
                })

                ->addColumn('Agency', function ($row) {
                    return $row->agency->Name ?? '';
                })

                ->addColumn('Scheme', function ($row) {
                    return $row->scheme->Name ?? '';
                })

                ->addColumn('actions', function ($row) {

                    $editUrl   = route('Labour_Work.edit', $row->ID);
                    $deleteUrl = route('Labour_Work.delete', $row->ID);
                    $formId    = 'delete-form-' . $row->ID;

                    $actions = '';

                    if (hasPermission('edit_labour_work')) {
                        $actions .= '
                            <a href="'.$editUrl.'" class="me-2 text-primary">
                                <i data-feather="edit-2"></i>
                            </a>
                        ';
                    }

                    if (hasPermission('delete_labour_work')) {
                        $actions .= '
                            <a href="#" class="text-danger delete-confirm" data-id="'.$formId.'">
                                <i data-feather="trash"></i>
                            </a>

                            <form id="'.$formId.'" action="'.$deleteUrl.'" method="POST" class="d-none">
                                '.csrf_field().'
                                '.method_field('DELETE').'
                            </form>
                        ';
                    }

                    return $actions;
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('backend.Labour_Work.index');
    }

    /**
     * Show create form
     */
    public function create()
    {
        // selected scheme from session
        $clientId = Session::get('selected_scheme_id');

        // get only active scheme
        $schemes = SchemeDetail::where('ID', $clientId)
                    ->where('completFalg', 0)
                    ->get();

        // get all agencies
        $agencies = Agency::orderBy('Name')->get();

        return view('backend.Labour_Work.create', compact('schemes','agencies'));
    }

    /**
     * Store record
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'Date' => 'required|date',
            'Destination' => 'required',
            'agency' => 'required',

            'num_mistri_male' => 'required|integer|min:0',
            'num_mistri_female' => 'required|integer|min:0',

            'num_labour_male' => 'required|integer|min:0',
            'num_labour_female' => 'required|integer|min:0',

            'num_thekedar_male' => 'required|integer|min:0',
            'num_thekedar_female' => 'required|integer|min:0',
        ]);

        // fetch agency rates
        $agency = Agency::findOrFail($validated['agency']);

        // calculate grand total on server
        $grandTotal =
            ($validated['num_mistri_male']    * $agency->Mistri_m_rate) +
            ($validated['num_mistri_female']  * $agency->Mistri_f_rate) +
            ($validated['num_labour_male']    * $agency->Labour_m_rate) +
            ($validated['num_labour_female']  * $agency->Labour_f_rate) +
            ($validated['num_thekedar_male']  * $agency->Thekedar_m_rate) +
            ($validated['num_thekedar_female']* $agency->Thekedar_f_rate);

        $workoflbr = new Labour_Work();

        $workoflbr->ID = uniqid();
        $workoflbr->ClientID = session('selected_scheme_id');

        $workoflbr->Date = Carbon::createFromFormat('d-m-Y', $validated['Date'])->format('Y-m-d');
        $workoflbr->schemeID = $validated['Destination'];
        $workoflbr->Agency_ID = $validated['agency'];

        $workoflbr->num_mistri_male = $validated['num_mistri_male'];
        $workoflbr->num_mistri_female = $validated['num_mistri_female'];

        $workoflbr->num_labour_male = $validated['num_labour_male'];
        $workoflbr->num_labour_female = $validated['num_labour_female'];

        $workoflbr->num_thekedar_male = $validated['num_thekedar_male'];
        $workoflbr->num_thekedar_female = $validated['num_thekedar_female'];

        $workoflbr->gtotal = $grandTotal;

        $workoflbr->userID = Auth::id();
        $workoflbr->Created = now();
        $workoflbr->LastEdited = now();

        $workoflbr->save();

        return redirect()
            ->route('Labour_Work')
            ->with('success', 'Labour work saved successfully!');
    }

    /**
     * Edit form
     */
    public function edit($id)
    {
        $workoflbrs = Labour_Work::findOrFail($id);

        // selected scheme from session
        $clientId = Session::get('selected_scheme_id');

        // active scheme
        $schemes = SchemeDetail::where('ID', $clientId)
                    ->where('completFalg', 0)
                    ->get();

        // agencies
        $agencies = Agency::orderBy('Name')->get();

        return view('backend.Labour_Work.create',
            compact('workoflbrs','schemes','agencies'));
    }

    /**
     * Update record
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:labour_work,ID',

            'Date' => 'required|date',
            'Destination' => 'required',
            'agency' => 'required',

            'num_mistri_male' => 'required|integer|min:0',
            'num_mistri_female' => 'required|integer|min:0',

            'num_labour_male' => 'required|integer|min:0',
            'num_labour_female' => 'required|integer|min:0',

            'num_thekedar_male' => 'required|integer|min:0',
            'num_thekedar_female' => 'required|integer|min:0',
        ]);

        $work = Labour_Work::findOrFail($validated['id']);

        // fetch agency rates
        $agency = Agency::findOrFail($validated['agency']);

        // recalc total
        $grandTotal =
            ($validated['num_mistri_male']    * $agency->Mistri_m_rate) +
            ($validated['num_mistri_female']  * $agency->Mistri_f_rate) +
            ($validated['num_labour_male']    * $agency->Labour_m_rate) +
            ($validated['num_labour_female']  * $agency->Labour_f_rate) +
            ($validated['num_thekedar_male']  * $agency->Thekedar_m_rate) +
            ($validated['num_thekedar_female']* $agency->Thekedar_f_rate);

        $work->Date = Carbon::createFromFormat('d-m-Y', $validated['Date'])->format('Y-m-d');
        $work->schemeID = $validated['Destination'];
        $work->Agency_ID = $validated['agency'];

        $work->num_mistri_male = $validated['num_mistri_male'];
        $work->num_mistri_female = $validated['num_mistri_female'];

        $work->num_labour_male = $validated['num_labour_male'];
        $work->num_labour_female = $validated['num_labour_female'];

        $work->num_thekedar_male = $validated['num_thekedar_male'];
        $work->num_thekedar_female = $validated['num_thekedar_female'];

        $work->gtotal = $grandTotal;

        $work->userID = Auth::id();
        $work->LastEdited = now();

        $work->save();

        return redirect()
            ->route('Labour_Work')
            ->with('success','Labour work updated successfully!');
    }

    /**
     * Delete record
     */
    public function destroy($id)
    {
        if (!canDeleteRecord('labour_payment_detail', 'labourworkID', $id)) {
            return redirect()
                ->route('Labour_Work')
                ->with('error', 'Cannot delete its used.');
        }
        
        Labour_Work::findOrFail($id)->delete();

        return redirect()
            ->route('Labour_Work')
            ->with('success', 'Labour_Work deleted successfully!');
    }
}
