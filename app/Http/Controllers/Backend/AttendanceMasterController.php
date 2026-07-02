<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Backend\Shift;
use App\Models\Backend\AttendanceMaster;

use Illuminate\Support\Facades\Auth;

use Yajra\DataTables\Facades\DataTables;

use App\Imports\AttendanceImport;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceMasterController extends Controller
{
    /**
     * INDEX
     */
    public function index(Request $request)
{
    if ($request->ajax()) {

        $data = AttendanceMaster::with(['Shift', 'user'])
            ->select('attendancemaster.*')
            ->latest();

        /*
        |--------------------------------------------------------------------------
        | Custom Search Filters
        |--------------------------------------------------------------------------
        */

        if ($request->filled('employee_name')) {
            $data->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->employee_name . '%');
            });
        }

        if ($request->filled('date')) {
            $data->whereDate('date', $request->date);
        }

        if ($request->filled('intime')) {
            $data->where('intime', 'like', '%' . $request->intime . '%');
        }

        if ($request->filled('outtime')) {
            $data->where('outtime', 'like', '%' . $request->outtime . '%');
        }

        if ($request->filled('late_mins')) {
            $data->where('late_mins', 'like', '%' . $request->late_mins . '%');
        }

        if ($request->filled('early_dep')) {
            $data->where('early_dep', 'like', '%' . $request->early_dep . '%');
        }

        if ($request->filled('work_hr')) {
            $data->where('work_hr', 'like', '%' . $request->work_hr . '%');
        }

        if ($request->filled('ot_hr')) {
            $data->where('ot_hr', 'like', '%' . $request->ot_hr . '%');
        }

        return DataTables::of($data)

            ->addIndexColumn()

            /*
            |--------------------------------------------------------------------------
            | SHIFT NAME
            |--------------------------------------------------------------------------
            */

            ->addColumn('shift_name', function ($row) {

                return $row->Shift->shift ?? 'N/A';
            })

            /*
            |--------------------------------------------------------------------------
            | EMPLOYEE NAME
            |--------------------------------------------------------------------------
            */

            ->addColumn('employee_name', function ($row) {

                return $row->user->name ?? 'N/A';
            })

            /*
            |--------------------------------------------------------------------------
            | ACTIONS
            |--------------------------------------------------------------------------
            */

            ->addColumn('actions', function ($row) {

                $editUrl   = route('AttendanceMaster.edit', $row->id);
                $deleteUrl = route('AttendanceMaster.destroy', $row->id);

                $formId = 'delete-form-' . $row->id;

                $actions = '';

                // EDIT
                if (hasPermission('edit_AttendanceMaster')) {

                    $actions .= '
                        <a href="' . $editUrl . '" class="me-2 text-primary">
                            <i data-feather="edit"></i>
                        </a>
                    ';
                }

                // DELETE
                if (hasPermission('delete_AttendanceMaster')) {

                    $actions .= '
                        <a href="#"
                           class="text-danger delete-confirm"
                           data-id="' . $formId . '">
                            <i data-feather="trash"></i>
                        </a>

                        <form id="' . $formId . '"
                              action="' . $deleteUrl . '"
                              method="POST"
                              class="d-none">
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

    return view('backend.AttendanceMaster.index');
}
    /**
     * IMPORT ATTENDANCE
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx'
        ]);

        Excel::import(new AttendanceImport, $request->file('file'));

        return redirect()->back();
    }

    /**
     * CREATE PAGE
     */
    public function create()
    {
        $empshift = Shift::select(
                            'id',
                            'shift'
                        )
                        ->orderBy('shift')
                        ->get();

        $users = User::select(
                        'id',
                        'name',
                        'emp_id',
                        'designation'
                    )
                    ->orderBy('name')
                    ->get();

        return view(
            'backend.AttendanceMaster.create',
            compact('empshift','users')
        );
    }

    /**
     * STORE
     */
    public function store(Request $request)
    {
        $validated = $request->validate([

            'date'        => 'required|date',

            'shift'       => 'required',

            'emp_id'      => 'required',

            'designation' => 'required',

            'intime'      => 'nullable',

            'outtime'     => 'nullable',

            'present'     => 'required|numeric',

            'absent'      => 'required|numeric',

            'emp_leave'   => 'required|numeric',

            'late_mins'   => 'nullable|numeric',

            'early_dep'   => 'nullable|numeric',

            'work_hr'     => 'nullable',

            'ot_hr'       => 'nullable',
        ]);

        /*
        |--------------------------------------------------------------------------
        | DUPLICATE CHECK
        |--------------------------------------------------------------------------
        */

        $alreadyExists = AttendanceMaster::where('date', $request->date)

                            ->where('emp_id', $request->emp_id)

                            ->exists();

        if ($alreadyExists)
        {
            return back()->with(
                'error',
                'Attendance already exists for this employee and date.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SAVE
        |--------------------------------------------------------------------------
        */

        $data = new AttendanceMaster();

        $data->date        = $validated['date'];

        $data->shift       = $validated['shift'];

        $data->emp_id      = $validated['emp_id'];

        $data->designation = $validated['designation'];

        $data->present     = $validated['present'];

        $data->absent      = $validated['absent'];

        $data->emp_leave   = $validated['emp_leave'];

        $data->intime      = $validated['intime'] ?? null;

        $data->outtime     = $validated['outtime'] ?? null;

        $data->late_mins   = $validated['late_mins'] ?? 0;

        $data->early_dep   = $validated['early_dep'] ?? 0;

        $data->work_hr     = $validated['work_hr'] ?? null;

        $data->ot_hr       = $validated['ot_hr'] ?? '00:00';

        $data->createdby   = Auth::id();

        $data->save();

        return redirect()
            ->route('AttendanceMaster')
            ->with('success', 'Attendance created successfully!');
    }

    /**
     * EDIT
     */
    public function edit($id)
    {
        $old = AttendanceMaster::findOrFail($id);

        $empshift = Shift::select(
                            'id',
                            'shift'
                        )
                        ->orderBy('shift')
                        ->get();

        $users = User::select(
                        'id',
                        'name',
                        'emp_id',
                        'designation'
                    )
                    ->orderBy('name')
                    ->get();

        return view(
            'backend.AttendanceMaster.create',
            compact('old','empshift','users')
        );
    }

    /**
     * UPDATE
     */
    public function update(Request $request)
    {
        $validated = $request->validate([

            'id'          => 'required',

            'date'        => 'required|date',

            'shift'       => 'required',

            'emp_id'      => 'required',

            'designation' => 'required',

            'intime'      => 'nullable',

            'outtime'     => 'nullable',

            'present'     => 'required|numeric',

            'absent'      => 'required|numeric',

            'emp_leave'   => 'required|numeric',

            'late_mins'   => 'nullable|numeric',

            'early_dep'   => 'nullable|numeric',

            'work_hr'     => 'nullable',

            'ot_hr'       => 'nullable',
        ]);

        $data = AttendanceMaster::findOrFail($request->id);

        $data->date        = $validated['date'];

        $data->shift       = $validated['shift'];

        $data->emp_id      = $validated['emp_id'];

        $data->designation = $validated['designation'];

        $data->present     = $validated['present'];

        $data->absent      = $validated['absent'];

        $data->emp_leave   = $validated['emp_leave'];

        $data->intime      = $validated['intime'] ?? null;

        $data->outtime     = $validated['outtime'] ?? null;

        $data->late_mins   = $validated['late_mins'] ?? 0;

        $data->early_dep   = $validated['early_dep'] ?? 0;

        $data->work_hr     = $validated['work_hr'] ?? null;

        $data->ot_hr       = $validated['ot_hr'] ?? '00:00';

        $data->save();

        return redirect()
            ->route('AttendanceMaster')
            ->with('success', 'Attendance updated successfully!');
    }

    /**
     * DELETE
     */
    public function destroy($id)
    {
        $data = AttendanceMaster::findOrFail($id);

        $data->delete();

        return redirect()
            ->route('AttendanceMaster')
            ->with('success', 'Attendance deleted successfully!');
    }
}
