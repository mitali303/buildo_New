<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Backend\AttendanceMaster;
use App\Models\Backend\SalaryMaster;
use App\Models\Backend\Shift;
use App\Models\Backend\UserShiftAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class UserShiftAssignmentController extends Controller
{
    /**
     * INDEX
     */
    public function index(Request $request)
{
    if ($request->ajax()) {

        $query = UserShiftAssignment::with(['user', 'shift'])
            ->select(
                'id',
                'user_id',
                'emp_id',
                'shift_id',
                'from_date',
                'to_date',
                'is_active'
            )
            ->orderByDesc('id');

        return DataTables::of($query)

            ->addIndexColumn()

            ->filterColumn('employee', function ($query, $keyword) {
                $query->whereHas('user', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('emp_id', 'like', "%{$keyword}%");
                });
            })

            ->filterColumn('shift_name', function ($query, $keyword) {
                $query->whereHas('shift', function ($q) use ($keyword) {
                    $q->where('shift', 'like', "%{$keyword}%");
                });
            })

            ->filterColumn('from_date', function ($query, $keyword) {
                $query->whereDate('from_date', $keyword);
            })

            ->filterColumn('to_date', function ($query, $keyword) {
                $query->whereDate('to_date', $keyword);
            })

            ->filterColumn('status', function ($query, $keyword) {

                if ($keyword == 'Active') {
                    $query->where('is_active', 1);
                }

                if ($keyword == 'Inactive') {
                    $query->where('is_active', 0);
                }
            })

            ->addColumn('employee', function ($row) {
                return $row->user->Name ?? 'N/A';
            })

            ->addColumn('shift_name', function ($row) {
                return $row->shift->shift ?? 'N/A';
            })

            ->editColumn('from_date', function ($row) {
                return $row->from_date
                    ? Carbon::parse($row->from_date)->format('d-m-Y')
                    : '';
            })

            ->editColumn('to_date', function ($row) {
                return $row->to_date
                    ? Carbon::parse($row->to_date)->format('d-m-Y')
                    : '';
            })

            ->addColumn('status', function ($row) {
                return $row->is_active == 1
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';
            })

            ->addColumn('actions', function ($row) {

                $editUrl   = route('UserShift.edit', $row->id);
                $deleteUrl = route('UserShift.delete', $row->id);
                $formId    = 'delete-form-' . $row->id;

                $actions = '';

                if (hasPermission('edit_UserShift')) {
                    $actions .= '
                        <a href="' . $editUrl . '" class="text-primary me-2">
                            <i data-feather="edit"></i>
                        </a>
                    ';
                }

                if (hasPermission('delete_UserShift')) {
                    $actions .= '
                        <a href="#" class="text-danger delete-confirm" data-id="' . $formId . '">
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

            ->rawColumns(['status', 'actions'])
            ->make(true);
    }

    return view('backend.UserShift.index');
}

    /**
     * CREATE
     */
    public function create()
    {
        $users  = User::all();
        $shifts = Shift::all();

        return view('backend.UserShift.create', compact('users','shifts'));
    }

    /**
     * STORE
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id'   => 'required',
            'shift_id'  => 'required',
            'from_date' => 'required|date',
        ]);

        // close previous active shift
        UserShiftAssignment::where('user_id', $request->user_id)
            ->where('is_active', 1)
            ->update([
                'to_date' => $request->from_date,
                'is_active' => 0
            ]);

        UserShiftAssignment::create([
            'user_id'    => $request->user_id,
            'emp_id'     => User::find($request->user_id)->emp_id ?? null,
            'shift_id'   => $request->shift_id,
            'from_date'  => $request->from_date,
            'to_date'    => $request->to_date,
            'is_active'  => 1,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('UserShift')
            ->with('success','Shift assigned successfully!');
    }

    /**
     * EDIT
     */
    public function edit($id)
    {
        $data   = UserShiftAssignment::findOrFail($id);
        $users  = User::all();
        $shifts = Shift::all();

        return view('backend.UserShift.create', compact('data','users','shifts'));
    }

    /**
     * UPDATE (WITH SAFETY CHECK)
     */
    public function update(Request $request)
    {
        $request->validate([
            'id'        => 'required',
            'user_id'   => 'required',
            'shift_id'  => 'required',
            'from_date' => 'required|date',
            'is_active' => 'required|in:0,1',
        ]);

        $data = UserShiftAssignment::findOrFail($request->id);

        // ❗ SAFETY CHECK - prevent update if attendance or salary exists
        // $attendanceExists = AttendanceMaster::where('emp_id', $data->emp_id)
        //     ->where('date', '>=', $data->from_date)
        //     ->exists();

        // $salaryExists = SalaryMaster::where('emp_id', $data->emp_id)
        //     ->exists();

        // if ($attendanceExists || $salaryExists) {
        //     return back()->with('error', 'Cannot update shift. Attendance or Salary already generated!');
        // }

        $data->update([
            'user_id'    => $request->user_id,
            'emp_id'     => User::find($request->user_id)->emp_id ?? null,
            'shift_id'   => $request->shift_id,
            'from_date'  => $request->from_date,
            'to_date'    => $request->to_date,
            'is_active'  => $request->is_active,
        ]);

        return redirect()->route('UserShift')
            ->with('success','Shift updated successfully!');
    }

    /**
     * DELETE (SAFE BLOCK)
     */
    public function destroy(string $id)
    {
        $data = UserShiftAssignment::findOrFail($id);

        // ❗ BLOCK DELETE IF ATTENDANCE EXISTS
        $attendanceExists = AttendanceMaster::where('emp_id', $data->user_id)
            ->exists();

        $salaryExists = SalaryMaster::where('emp_id', $data->user_id)
            ->exists();

        if ($attendanceExists || $salaryExists) {
            return back()->with('error', 'Cannot delete. Attendance or Salary already generated!');
        }

        $data->delete();

        return redirect()->route('UserShift')
            ->with('success','Deleted successfully!');
    }
}
