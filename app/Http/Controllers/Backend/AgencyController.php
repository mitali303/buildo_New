<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Backend\Agency;
use Illuminate\Support\Facades\Auth;

class AgencyController extends Controller
{
    /**
     * Display listing
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $query = Agency::select([
                'ID',
                'Name',
                'ContactNo',
                'Address',
                'Mistri_m_rate',
                'Mistri_f_rate',
                'Labour_m_rate',
                'Labour_f_rate',
                'Thekedar_m_rate',
                'Thekedar_f_rate'
            ]);

            return DataTables::of($query)
                ->addIndexColumn()

                ->addColumn('actions', function ($row) {

                    $editUrl = route('Agency.edit', $row->ID);
                    $deleteUrl = route('Agency.delete', $row->ID);
                    $formId = 'delete-form-' . $row->ID;

                    $actions = '';

                    // ➤ Edit Permission
                    if (hasPermission('edit_agency')) {
                        $actions .= '
                            <a href="' . $editUrl . '" class="me-2 text-primary">
                                <i data-feather="edit-2"></i>
                            </a>
                        ';
                    }

                    // ➤ Delete Permission
                    if (hasPermission('delete_agency')) {
                        $actions .= '
                            <a href="#" class="text-danger delete-confirm" data-id="' . $formId . '">
                                <i data-feather="trash"></i>
                            </a>

                            <form id="' . $formId . '" action="' . $deleteUrl . '" method="POST" class="d-none">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                            </form>
                        ';
                    }

                    return $actions;
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('backend.Agency.index');
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('backend.Agency.create');
    }

    /**
     * Store record
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'Name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9\s\.\&\-\(\)]+$/',
                Rule::unique('agency', 'Name')->ignore($request->id, 'ID')
            ],
            'ContactNo' => 'required|digits:10',
            'Address' => 'required|string|max:255',

            'MistriMaleRate' => 'required|numeric',
            'MistriFemaleRate' => 'required|numeric',
            'LabourMaleRate' => 'required|numeric',
            'LabourFemaleRate' => 'required|numeric',
            'ThekedarMaleRate' => 'required|numeric',
            'ThekedarFemaleRate' => 'required|numeric',
        ]);

        $agency = new Agency();

        $agency->ID = uniqid();
        $agency->ClientID = session('selected_scheme_id');
        $agency->Name = $validated['Name'];
        $agency->ContactNo = $validated['ContactNo'];
        $agency->Address = $validated['Address'] ?? null;

        $agency->Mistri_m_rate = $validated['MistriMaleRate'];
        $agency->Mistri_f_rate = $validated['MistriFemaleRate'];
        $agency->Labour_m_rate = $validated['LabourMaleRate'];
        $agency->Labour_f_rate = $validated['LabourFemaleRate'];
        $agency->Thekedar_m_rate = $validated['ThekedarMaleRate'];
        $agency->Thekedar_f_rate = $validated['ThekedarFemaleRate'];

        $agency->userID = Auth::id();
        $agency->Created = now();
        $agency->LastEdited = now();

        $agency->save();

        if ($request->has('from_labour_work')) {

            return redirect()
                ->back()
                ->with('success', 'Agency created successfully!');

        }
        return redirect()
            ->route('Agency')
            ->with('success', 'Agency created successfully!');
    }

    /**
     * Edit form
     */
    public function edit($id)
    {
        $agencys = Agency::findOrFail($id);
        return view('backend.Agency.create', compact('agencys'));
    }

    /**
     * Update record
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:agency,ID',

            'Name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9\s\.\&\-\(\)]+$/',
                Rule::unique('agency', 'Name')->ignore($request->id, 'ID')
            ],
            'ContactNo' => 'required|digits:10',
            'Address' => 'nullable|string|max:255',

            'MistriMaleRate' => 'required|numeric',
            'MistriFemaleRate' => 'required|numeric',
            'LabourMaleRate' => 'required|numeric',
            'LabourFemaleRate' => 'required|numeric',
            'ThekedarMaleRate' => 'required|numeric',
            'ThekedarFemaleRate' => 'required|numeric',
        ]);

        $agency = Agency::findOrFail($validated['id']);

        $agency->Name = $validated['Name'];
        $agency->ContactNo = $validated['ContactNo'];
        $agency->Address = $validated['Address'] ?? null;

        $agency->Mistri_m_rate = $validated['MistriMaleRate'];
        $agency->Mistri_f_rate = $validated['MistriFemaleRate'];
        $agency->Labour_m_rate = $validated['LabourMaleRate'];
        $agency->Labour_f_rate = $validated['LabourFemaleRate'];
        $agency->Thekedar_m_rate = $validated['ThekedarMaleRate'];
        $agency->Thekedar_f_rate = $validated['ThekedarFemaleRate'];

        $agency->ClientID = session('selected_scheme_id');
        $agency->userID = Auth::id();
        $agency->LastEdited = now();

        $agency->save();

        return redirect()
            ->route('Agency')
            ->with('success', 'Agency updated successfully!');
    }

    /**
     * Delete record
     */
    public function destroy($id)
    {
        $agency = Agency::findOrFail($id);

        // ❌ check if used
        if (!canDeleteRecord('labour_work', 'Agency_ID', $id)) {
            return redirect()
                ->route('Agency')
                ->with('error', 'Cannot delete Agency. It is used in Labour Work.');
        }

        // 🔹 Log before delete
        activity_log(
            'delete',
            'Agency deleted: ' . $agency->Name,
            $agency
        );

        $agency->delete();

        return redirect()
            ->route('Agency')
            ->with('success', 'Agency deleted successfully!');
    }

    /**
     * Get agency rates (AJAX)
     */
    public function getRates($id)
    {
        $agency = Agency::select(
            'Mistri_m_rate',
            'Mistri_f_rate',
            'Labour_m_rate',
            'Labour_f_rate',
            'Thekedar_m_rate',
            'Thekedar_f_rate'
        )->findOrFail($id);

        return response()->json($agency);
    }

}