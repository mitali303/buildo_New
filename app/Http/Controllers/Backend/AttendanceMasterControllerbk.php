<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\AttendanceMaster;
use App\Models\Backend\Staff;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class AttendanceMasterController extends Controller
{

    // ✅ INDEX (Datatable)
    public function index(Request $request)
{
    if ($request->ajax()) {

        $data = AttendanceMaster::with(['staff'])
            ->select('attendancemaster.*');

        return DataTables::of($data)
            ->addIndexColumn()
       ->editColumn('date', function ($row) {
        return Carbon::parse($row->date)->format('d-m-Y');
    })
            // ✅ Employee Name
            ->addColumn('emp_id', function ($row) {
                return $row->staff->Name ?? 'N/A';
            })

            

            // ❌ You DON'T have intime/outtime/work_hr in DB
            // So we return dummy OR remove from table

            ->addColumn('intime', function ($row) {
                return '-';
            })

            ->addColumn('outtime', function ($row) {
                return '-';
            })

            ->addColumn('work_hr', function ($row) {
                return '-';
            })

            // ✅ Actions
            ->addColumn('actions', function ($row) {

                $editUrl   = route('AttendanceMaster.edit', $row->id);
                $deleteUrl = route('AttendanceMaster.destroy', $row->id);
                $formId    = 'delete-form-' . $row->id;

                $actions = '';

                if (hasPermission('edit_AttendanceMaster')) {
                    $actions .= '
                        <a href="'.$editUrl.'" class="me-2 text-primary">
                            <i data-feather="edit-2"></i>
                        </a>
                    ';
                }

                if (hasPermission('delete_AttendanceMaster')) {
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

    return view('backend.AttendanceMaster.index');
}


    // ✅ CREATE
    public function create()
    {
        $staffs = Staff::select('ID', 'Name')->orderBy('Name')->get();

       $designations = AttendanceMaster::select('designation')
        ->whereNotNull('designation')
        ->where('designation', '!=', '')
        ->distinct()
        ->orderBy('designation')
        ->get();

    return view('backend.AttendanceMaster.create',
        compact('staffs', 'designations'));
    }


    // ✅ STORE
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'month' => 'required|numeric|min:1|max:12',
            'year' => 'required|numeric|min:2000|max:2100',
            'emp_id' => 'required',
            'designation' => 'required',
            'intime'  => 'required|date_format:H:i',
            'outtime' => 'required|date_format:H:i',
            'present' => 'required|numeric',
            'absent' => 'required|numeric',
            'emp_leave' => 'required|numeric',
            'late_mins' => 'required|numeric',
            'early_dep' => 'required|numeric',
            'work_hr' => 'required|numeric',

        ]);



        $data = new AttendanceMaster();
        $data->date = $validated['date'];
        $data->month = $validated['month'];
        $data->year  = $validated['year'];
        $data->emp_id = $validated['emp_id'];
        $data->designation = $validated['designation'];
        $data->present = $validated['present'] ?? null;
        $data->absent = $validated['absent'] ?? null;
        $data->emp_leave = $validated['emp_leave'] ?? null;
        $data->intime =  $validated['intime'];
        $data->outtime =  $validated['outtime'];
        $data->late_mins =  $validated['late_mins'];
        $data->early_dep =  $validated['early_dep'];
        $data->work_hr =  $validated['work_hr'];
        $data->createdby = Auth::id() ?? 0; // Safety fallback
        $data->save();

        return redirect()->route('AttendanceMaster')->with('success', 'Attendance Master created successfully!');
    }


    // ✅ EDIT
    public function edit($id)
    {
        $old = AttendanceMaster::findOrFail($id);
        $staffs = Staff::select('ID', 'Name')->orderBy('Name')->get();

         $designations = AttendanceMaster::select('designation')
        ->whereNotNull('designation')
        ->where('designation', '!=', '')
        ->distinct()
        ->orderBy('designation')
        ->get();

    return view('backend.AttendanceMaster.create',
        compact('old', 'staffs', 'designations'));
    }


    // ✅ UPDATE
    public function update(Request $request)
    {
        $data = AttendanceMaster::findOrFail($request->id);

        $validated = $request->validate([
            'date' => 'required|date',
            'month' => 'required|numeric|min:1|max:12',
            'year' => 'required|numeric|min:2000|max:2100',
            'emp_id' => 'required',
            'designation' => 'required',
            'intime'  => 'required|date_format:H:i',
            'outtime' => 'required|date_format:H:i',
            'present' => 'required|numeric',
            'absent' => 'required|numeric',
            'emp_leave' => 'required|numeric',
            'late_mins' => 'required|numeric',
            'early_dep' => 'required|numeric',
            'work_hr' => 'required|numeric',
        ]);



       $data->date = $validated['date'];
        $data->month = $validated['month'];
        $data->year  = $validated['year'];
        $data->emp_id = $validated['emp_id'];
        $data->designation = $validated['designation'];
        $data->present = $validated['present'] ?? null;
        $data->absent = $validated['absent'] ?? null;
        $data->emp_leave = $validated['emp_leave'] ?? null;
        $data->intime =  $validated['intime'];
        $data->outtime =  $validated['outtime'];
        $data->late_mins =  $validated['late_mins'];
        $data->early_dep =  $validated['early_dep'];
        $data->work_hr =  $validated['work_hr'];
        $data->save();

        return redirect()->route('AttendanceMaster')->with('success', 'Attendance Master updated successfully!');
    }


    // ✅ DELETE
    public function destroy($id)
    {
        $data = AttendanceMaster::findOrFail($id);
        $data->delete();

        return redirect()->route('AttendanceMaster')
            ->with('success', 'Attendance Master deleted successfully!');
    }
}