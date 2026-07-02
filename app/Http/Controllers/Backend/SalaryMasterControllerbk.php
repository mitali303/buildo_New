<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Backend\Material;
use App\Models\Backend\Staff;
use App\Models\Backend\AttendanceMaster;


use App\Models\Backend\SalaryMaster;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class SalaryMasterController extends Controller
{
public function index(Request $request)
{
    if ($request->ajax()) {

        $data = SalaryMaster::with('staff')->select([
            'id',
            'salary_no',
            'amount',
            'payment_method',
            'emp_id',
            'month' // ✅ include month
        ]);

        return DataTables::of($data)
            ->addIndexColumn()

            ->addColumn('staff_name', function ($row) {
                return $row->staff->Name ?? 'N/A';
            })

            ->addColumn('month_name', function ($row) {

                $months = [
                    1 => 'January', 2 => 'February', 3 => 'March',
                    4 => 'April',   5 => 'May',      6 => 'June',
                    7 => 'July',    8 => 'August',   9 => 'September',
                    10 => 'October',11 => 'November',12 => 'December'
                ];

                return $months[(int)$row->month] ?? 'N/A';
            })

            ->addColumn('actions', function ($row) {
                $editUrl   = route('SalaryMaster.edit', $row->id);
                $deleteUrl = route('SalaryMaster.destroy', $row->id);
                $printUrl  = route('SalaryMaster.print', $row->id);
                $formId    = 'delete-form-' . $row->id;

                return '
                    <a href="'.$editUrl.'" class="me-2 text-primary">
                        <i data-feather="edit-2"></i>
                    </a>

                    <a href="'.$printUrl.'" target="_blank" class="me-2 text-success">
                        <i data-feather="printer"></i>
                    </a>

                    <a href="#" class="text-danger delete-confirm" data-id="'.$formId.'">
                        <i data-feather="trash"></i>
                    </a>

                    <form id="'.$formId.'" action="'.$deleteUrl.'" method="POST" class="d-none">
                        '.csrf_field().'
                        '.method_field('DELETE').'
                    </form>
                ';
            })

            ->rawColumns(['actions'])
            ->make(true);
    }

    return view('backend.SalaryMaster.index');
}

 public function create()
    {
       //$empshift = Shift::select('id', 'Shift')->orderBy('Shift')->get();
        $users = User::select('id', 'name')->orderBy('name')->get();
        $staffs = Staff::select('ID', 'Name')->orderBy('Name')->get();

        return view('backend.SalaryMaster.create', compact('users','staffs'));
    }

    public function store(Request $request)
    {
        $clientId = session()->get('selected_scheme_id');

        $validated = $request->validate([
            'date' => 'required|date',
            'salary_no' => 'required|numeric',
            'emp_id' => 'required',
            'month' => 'required|numeric|min:1|max:12',
            'year' => 'required|numeric|min:2000|max:2100',
            'gross' => 'required|numeric',
            'basic_salary' => 'required|numeric',
            'pf' => 'required|numeric',
            'esi' => 'required|numeric',
            'advance_emi' => 'required|numeric',
            'net_salary' => 'required|numeric',
            'payment_method' => 'required',
            'account_no' => 'required',
            'cheque_no' => 'numeric',
            'amount' => 'required|numeric',
            'narration' => 'required',

        ]);



        $data = new SalaryMaster();
        $data->Date = $validated['date'];
        $data->ClientID = $clientId;
        $data->month = $validated['month'];
        $data->year  = $validated['year'];
        $data->account_no = $validated['account_no'];
        $data->salary_no = $validated['salary_no'];
        $data->emp_id = $validated['emp_id'];
        $data->gross = $validated['gross'];
        $data->basic_salary = $validated['basic_salary'] ?? null;
        $data->pf = $validated['pf'] ?? null;
        $data->esi = $validated['esi'] ?? null;
        $data->advance_emi =  $validated['advance_emi'];
        $data->net_salary =  $validated['net_salary'];
        $data->amount =  $validated['amount'];
        $data->narration =  $validated['narration'];
        $data->payment_method =  $validated['payment_method'];
        $data->cheque_no =  $validated['cheque_no'];
        $data->createdby = Auth::id() ?? 0; // Safety fallback
        $data->save();

        return redirect()->route('SalaryMaster')->with('success', 'Salary Master created successfully!');
    }

    public function edit($id)
    {
        $old = SalaryMaster::findOrFail($id);
       // $empshift = Shift::select('id', 'Shift')->orderBy('Shift')->get();
        $users = User::select('id', 'name')->orderBy('name')->get();

        $staffs = Staff::select('ID', 'Name')->orderBy('ID')->get();
        return view('backend.SalaryMaster.create', compact('old','users','staffs'));
    }

    public function update(Request $request)
    {
        $clientId = session()->get('selected_scheme_id');

        $data = SalaryMaster::findOrFail($request->id);

        $validated = $request->validate([
             'date' => 'required|date',
            'salary_no' => 'required|numeric',
            'emp_id' => 'required',
            'month' => 'required|numeric|min:1|max:12',
            'year' => 'required|numeric|min:2000|max:2100',
            'gross' => 'required|numeric',
            'basic_salary' => 'required|numeric',
            'pf' => 'required|numeric',
            'esi' => 'required|numeric',
            'advance_emi' => 'required|numeric',
            'net_salary' => 'required|numeric',
            'payment_method' => 'required',
            'account_no' => 'required',
            'cheque_no' => 'numeric',
            'amount' => 'required|numeric',
            'narration' => 'required',
        ]);



      $data->Date = $validated['date'];
      $data->ClientID = $clientId;
      $data->month = $validated['month'];
        $data->year  = $validated['year'];
        $data->account_no = $validated['account_no'];
        $data->salary_no = $validated['salary_no'];
        $data->emp_id = $validated['emp_id'];
        $data->gross = $validated['gross'];
        $data->basic_salary = $validated['basic_salary'] ?? null;
        $data->pf = $validated['pf'] ?? null;
        $data->esi = $validated['esi'] ?? null;
        $data->advance_emi =  $validated['advance_emi'];
        $data->net_salary =  $validated['net_salary'];
        $data->amount =  $validated['amount'];
        $data->narration =  $validated['narration'];
        $data->payment_method =  $validated['payment_method'];
        $data->cheque_no =  $validated['cheque_no'];
        $data->save();

        return redirect()->route('SalaryMaster')->with('success', 'Salary Master updated successfully!');
    }

    public function destroy($id)
    {
        $data = SalaryMaster::findOrFail($id);
        if ($data->image && file_exists(public_path('images/' . $data->image))) {
            unlink(public_path('images/' . $data->image));
        }
        $data->delete();
        return redirect()->route('SalaryMaster')->with('success', 'Salary Master deleted successfully!');
    }

    public function getEmployeeSalary($id, Request $request)
{
    $staff = Staff::find($id);

    if (!$staff) {
        return response()->json(['status' => false]);
    }

    $month = $request->month;
    $year  = $request->year;

    $attendance = AttendanceMaster::where('emp_id', $id)
        ->where('month', $month)
        ->where('year', $year)
        ->first();

    return response()->json([
        'status' => true,
        'daily_wage' => $staff->DailyWage,
        'present' => $attendance->present ?? 0
    ]);
}

public function printSlip($id)
{
    $salary = SalaryMaster::with('staff')->findOrFail($id);

    return view('backend.SalaryMaster.print', compact('salary'));
}

}
