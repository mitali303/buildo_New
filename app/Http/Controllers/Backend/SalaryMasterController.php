<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Backend\CompanySetting;
use App\Models\Backend\LateMarkCalculation;
use App\Models\Backend\SalaryMaster;
use App\Models\Backend\AttendanceMaster;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Backend\EmployeeAdvance;
use App\Models\Backend\EmployeeAdvancePayment;

class SalaryMasterController extends Controller
{
    /* =========================================================
        INDEX
    ========================================================= */

   public function index(Request $request)
{
    if ($request->ajax()) {

        $data = SalaryMaster::with('user')
            ->select('salarymaster.*')
            ->latest();

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        if ($request->filled('employee_name')) {

            $data->whereHas('user', function ($q) use ($request) {

                $q->where(
                    'name',
                    'like',
                    '%' . trim($request->employee_name) . '%'
                );
            });
        }

        if ($request->filled('gross')) {

            $data->where(
                'gross',
                'like',
                '%' . trim($request->gross) . '%'
            );
        }

        if ($request->filled('advance_emi')) {

            $data->where(
                'advance_emi',
                'like',
                '%' . trim($request->advance_emi) . '%'
            );
        }

        if ($request->filled('net_salary')) {

            $data->where(
                'net_salary',
                'like',
                '%' . trim($request->net_salary) . '%'
            );
        }

        return DataTables::of($data)

            ->addIndexColumn()

            ->addColumn('employee_name', function ($row) {

                return $row->user->name ?? 'N/A';
            })

            ->addColumn('salary_month', function ($row) {

                return $row->month . ' - ' . $row->year;
            })

            ->editColumn('gross', function ($row) {

                return number_format($row->gross, 2);
            })

            ->editColumn('advance_emi', function ($row) {

                return number_format($row->advance_emi, 2);
            })

            ->editColumn('net_salary', function ($row) {

                return number_format($row->net_salary, 2);
            })

            ->addColumn('actions', function ($row) {

                $editUrl   = route('SalaryMaster.edit', $row->id);
                $deleteUrl = route('SalaryMaster.destroy', $row->id);
                $slipUrl   = route('SalaryMaster.slip', $row->id);

                $formId = 'delete-form-' . $row->id;

                $btn = '';

                if (hasPermission('edit_SalaryMaster')) {

                    $btn .= '
                        <a href="' . $editUrl . '" class="me-2 text-primary">
                            <i data-feather="edit"></i>
                        </a>
                    ';
                }

                $btn .= '
                    <a href="' . $slipUrl . '"
                       class="me-2 text-success"
                       target="_blank">

                        <i data-feather="file-text"></i>

                    </a>
                ';

                if (hasPermission('delete_SalaryMaster')) {

                    $btn .= '
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

                return $btn;
            })

            ->rawColumns(['actions'])

            ->make(true);
    }

    return view('backend.SalaryMaster.index');
}


    /* =========================================================
        CREATE
    ========================================================= */

    public function create()
    {
        $users = User::orderBy('name')
                    ->select('id', 'name')
                    ->get();

        $prefix = 'SAL-' . date('Ym') . '-';

        $lastSalary = SalaryMaster::where(
                            'salary_no',
                            'LIKE',
                            $prefix . '%'
                        )
                        ->latest('id')
                        ->first();

        if ($lastSalary) {

            $lastNumber = (int) substr($lastSalary->salary_no, -3);

            $newNumber = $lastNumber + 1;

        } else {

            $newNumber = 1;
        }

        $salaryNo = $prefix .
                    str_pad($newNumber, 3, '0', STR_PAD_LEFT);

        return view(
            'backend.SalaryMaster.create',
            compact('users', 'salaryNo')
        );
    }


    /* =========================================================
        STORE
    ========================================================= */

    public function store(Request $request)
    {
        $validated = $request->validate([

            'date'                 => 'required|date',
            'month'                => 'required',
            'year'                 => 'required',

            'salary_no'            => 'required',

            'emp_id'               => 'required|exists:users,id',

            'basic_salary'         => 'required|numeric',
            'gross'                => 'required|numeric',

            'pf'                   => 'nullable|numeric',
            'esi'                  => 'nullable|numeric',

            'advance_emi'          => 'nullable|numeric',
            'late_deduction'       => 'nullable|numeric',

            'overtime_hours'       => 'nullable|numeric',
            'overtime_amount'      => 'nullable|numeric',

            'total_present_days'   => 'nullable|numeric',
            'total_absent_days'    => 'nullable|numeric',
            'paid_leaves'          => 'nullable|numeric',
            'working_days'         => 'nullable|numeric',

            'per_day_salary'       => 'nullable|numeric',
            'absent_deduction'     => 'nullable|numeric',

            'per_hour_ot_rate'     => 'nullable|numeric',

            'net_salary'           => 'required|numeric',

            'payment_method'       => 'required',

            'cheque_no'            => 'nullable',

            'narration'            => 'nullable',
        ]);


        /* =========================================================
            DUPLICATE CHECK
        ========================================================= */

        $exists = SalaryMaster::where('emp_id', $request->emp_id)
                    ->where('month', $request->month)
                    ->where('year', $request->year)
                    ->exists();

        if ($exists) {

            return back()
                    ->withInput()
                    ->withErrors([

                        'emp_id' =>
                        'Salary already generated for selected month and year.'
                    ]);
        }


        /* =========================================================
            STORE
        ========================================================= */

        SalaryMaster::create([

            'date'                 => $validated['date'],
            'month'                => $validated['month'],
            'year'                 => $validated['year'],

            'salary_no'            => $validated['salary_no'],

            'emp_id'               => $validated['emp_id'],

            'basic_salary'         => $validated['basic_salary'],
            'gross'                => $validated['gross'],

            'pf'                   => $validated['pf'] ?? 0,
            'esi'                  => $validated['esi'] ?? 0,

            'advance_emi'          => $validated['advance_emi'] ?? 0,
            'late_deduction'       => $validated['late_deduction'] ?? 0,

            'overtime_hours'       => $validated['overtime_hours'] ?? 0,
            'overtime_amount'      => $validated['overtime_amount'] ?? 0,

            'total_present_days'   => $validated['total_present_days'] ?? 0,
            'total_absent_days'    => $validated['total_absent_days'] ?? 0,
            'paid_leaves'          => $validated['paid_leaves'] ?? 0,
            'working_days'         => $validated['working_days'] ?? 0,

            'per_day_salary'       => $validated['per_day_salary'] ?? 0,
            'absent_deduction'     => $validated['absent_deduction'] ?? 0,

            'per_hour_ot_rate'     => $validated['per_hour_ot_rate'] ?? 0,

            'net_salary'           => $validated['net_salary'],

            'payment_method'       => $validated['payment_method'],
            'cheque_no'            => $validated['cheque_no'],

            'narration'            => $validated['narration'],

            'createdby'            => Auth::id(),
        ]);

        /* =========================================================
    ADVANCE EMI PAYMENT ENTRY
========================================================= */

if (($validated['advance_emi'] ?? 0) > 0) {

    // Latest active advance
    $advance = EmployeeAdvance::where('emp_id', $validated['emp_id'])
                    ->where('remaining_amount', '>', 0)
                    ->latest()
                    ->first();

    if ($advance) {

        // Remaining amount calculate
        $newRemaining =
            $advance->remaining_amount - $validated['advance_emi'];

        if ($newRemaining < 0) {
            $newRemaining = 0;
        }

        // Payment history entry
        EmployeeAdvancePayment::create([

        'parent_id' => $advance['id'],

            'emp_id'            => $validated['emp_id'],

            'advance'           => $validated['advance_emi'],

            'payment_method'    => $validated['payment_method'],

            'cheque_no'         => $validated['cheque_no'] ?? null,

            'remaining_amount'  => $newRemaining,

            'narration'         =>
                'Advance EMI deducted from Salary - '
                . $validated['month']
                . ' '
                . $validated['year'],

            'date'              => $validated['date'],
        ]);

        // Update advance remaining
        $advance->update([

            'remaining_amount' => $newRemaining
        ]);
    }
}

        return redirect()
                ->route('SalaryMaster')
                ->with('success', 'Salary generated successfully.');
    }


    /* =========================================================
        EDIT
    ========================================================= */

    public function edit($id)
    {
        $old = SalaryMaster::findOrFail($id);

        $users = User::orderBy('name')
                    ->select('id', 'name')
                    ->get();

        $salaryNo = $old->salary_no;

        return view(
            'backend.SalaryMaster.create',
            compact('old', 'users', 'salaryNo')
        );
    }


    /* =========================================================
        UPDATE
    ========================================================= */

    public function update(Request $request)
    {
        $data = SalaryMaster::findOrFail($request->id);
        $oldAdvanceEmi = $data->advance_emi ?? 0;

        $validated = $request->validate([

            'date'                 => 'required|date',
            'month'                => 'required',
            'year'                 => 'required',

            'salary_no'            => 'required',

            'emp_id'               => 'required|exists:users,id',

            'basic_salary'         => 'required|numeric',
            'gross'                => 'required|numeric',

            'pf'                   => 'nullable|numeric',
            'esi'                  => 'nullable|numeric',

            'advance_emi'          => 'nullable|numeric',
            'late_deduction'       => 'nullable|numeric',

            'overtime_hours'       => 'nullable|numeric',
            'overtime_amount'      => 'nullable|numeric',

            'total_present_days'   => 'nullable|numeric',
            'total_absent_days'    => 'nullable|numeric',
            'paid_leaves'          => 'nullable|numeric',
            'working_days'         => 'nullable|numeric',

            'per_day_salary'       => 'nullable|numeric',
            'absent_deduction'     => 'nullable|numeric',

            'per_hour_ot_rate'     => 'nullable|numeric',

            'net_salary'           => 'required|numeric',

            'payment_method'       => 'required',

            'cheque_no'            => 'nullable',

            'narration'            => 'nullable',
        ]);


        /* =========================================================
            DUPLICATE CHECK
        ========================================================= */

        $exists = SalaryMaster::where('emp_id', $request->emp_id)
                    ->where('month', $request->month)
                    ->where('year', $request->year)
                    ->where('id', '!=', $request->id)
                    ->exists();

        if ($exists) {

            return back()
                    ->withInput()
                    ->withErrors([

                        'emp_id' =>
                        'Salary already generated for selected month and year.'
                    ]);
        }


        /* =========================================================
            UPDATE
        ========================================================= */

        $data->update([

            'date'                 => $validated['date'],
            'month'                => $validated['month'],
            'year'                 => $validated['year'],

            'salary_no'            => $validated['salary_no'],

            'emp_id'               => $validated['emp_id'],

            'basic_salary'         => $validated['basic_salary'],
            'gross'                => $validated['gross'],

            'pf'                   => $validated['pf'] ?? 0,
            'esi'                  => $validated['esi'] ?? 0,

            'advance_emi'          => $validated['advance_emi'] ?? 0,
            'late_deduction'       => $validated['late_deduction'] ?? 0,

            'overtime_hours'       => $validated['overtime_hours'] ?? 0,
            'overtime_amount'      => $validated['overtime_amount'] ?? 0,

            'total_present_days'   => $validated['total_present_days'] ?? 0,
            'total_absent_days'    => $validated['total_absent_days'] ?? 0,
            'paid_leaves'          => $validated['paid_leaves'] ?? 0,
            'working_days'         => $validated['working_days'] ?? 0,

            'per_day_salary'       => $validated['per_day_salary'] ?? 0,
            'absent_deduction'     => $validated['absent_deduction'] ?? 0,

            'per_hour_ot_rate'     => $validated['per_hour_ot_rate'] ?? 0,

            'net_salary'           => $validated['net_salary'],

            'payment_method'       => $validated['payment_method'],
            'cheque_no'            => $validated['cheque_no'],

            'narration'            => $validated['narration'],
        ]);


        /* =========================================================
    ADVANCE EMI PAYMENT UPDATE ENTRY
========================================================= */

$newAdvanceEmi = $validated['advance_emi'] ?? 0;

/*
|--------------------------------------------------------------------------
| CASE 1:
| OLD EMI = 0
| NEW EMI > 0
|--------------------------------------------------------------------------
*/

if ($oldAdvanceEmi == 0 && $newAdvanceEmi > 0) {

    $advance = EmployeeAdvance::where('emp_id', $validated['emp_id'])
                    ->where('remaining_amount', '>', 0)
                    ->latest()
                    ->first();

    if ($advance) {

        $newRemaining =
            $advance->remaining_amount - $newAdvanceEmi;

        if ($newRemaining < 0) {
            $newRemaining = 0;
        }

        EmployeeAdvancePayment::create([

            'emp_id'           => $validated['emp_id'],

            'advance'          => $newAdvanceEmi,

            'payment_method'   => $validated['payment_method'],

            'cheque_no'        => $validated['cheque_no'] ?? null,

            'remaining_amount' => $newRemaining,

            'narration'        =>
                'Advance EMI deducted from Salary Update - '
                . $validated['month']
                . ' '
                . $validated['year'],

            'date'             => $validated['date'],
        ]);

        $advance->update([

            'remaining_amount' => $newRemaining
        ]);
    }
}

/*
|--------------------------------------------------------------------------
| CASE 2:
| OLD EMI != NEW EMI
|--------------------------------------------------------------------------
*/

elseif ($oldAdvanceEmi != $newAdvanceEmi) {

    $advance = EmployeeAdvance::where('emp_id', $validated['emp_id'])
                    ->latest()
                    ->first();

    if ($advance) {

        /*
        |--------------------------------------------------------------------------
        | RETURN OLD EMI
        |--------------------------------------------------------------------------
        */

        $remaining =
            $advance->remaining_amount + $oldAdvanceEmi;

        /*
        |--------------------------------------------------------------------------
        | DEDUCT NEW EMI
        |--------------------------------------------------------------------------
        */

        $remaining =
            $remaining - $newAdvanceEmi;

        if ($remaining < 0) {
            $remaining = 0;
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE ADVANCE TABLE
        |--------------------------------------------------------------------------
        */

        $advance->update([

            'remaining_amount' => $remaining
        ]);

        /*
        |--------------------------------------------------------------------------
        | UPDATE PAYMENT ENTRY
        |--------------------------------------------------------------------------
        */

        EmployeeAdvancePayment::create([

            'emp_id'           => $validated['emp_id'],

            'advance'          => $newAdvanceEmi,

            'payment_method'   => $validated['payment_method'],

            'cheque_no'        => $validated['cheque_no'] ?? null,

            'remaining_amount' => $remaining,

            'narration'        =>
                'Advance EMI updated from Salary - '
                . $validated['month']
                . ' '
                . $validated['year'],

            'date'             => $validated['date'],
        ]);
    }
}


        

        return redirect()
                ->route('SalaryMaster')
                ->with('success', 'Salary updated successfully.');
    }


    /* =========================================================
        AJAX SALARY CALCULATION
    ========================================================= */

    public function getSalaryDetails($empId, $month, $year, $id = null)
    {
        $user = User::findOrFail($empId);

        $monthNumber = date('m', strtotime($month));


        /* =========================================================
            ATTENDANCE CHECK
        ========================================================= */

        $attendanceExists = AttendanceMaster::where('emp_id', $empId)
                                ->whereMonth('date', $monthNumber)
                                ->whereYear('date', $year)
                                ->exists();

        if (!$attendanceExists) {

            return response()->json([

                'status'  => false,

                'message' =>
                'Attendance not imported for selected month and year.'
            ]);
        }


        /* =========================================================
            SALARY EXISTS
        ========================================================= */

        $salaryExists = SalaryMaster::where('emp_id', $empId)
                    ->where('month', $month)
                    ->where('year', $year)

                    ->when($id, function ($query) use ($id) {

                        $query->where('id', '!=', $id);

                    })

                    ->exists();

        if ($salaryExists) {

            return response()->json([

                'status'  => false,

                'message' =>
                'Salary already generated for this month.'
            ]);
        }


        /* =========================================================
            ATTENDANCE DATA
        ========================================================= */

        $attendance = AttendanceMaster::where('emp_id', $empId)
                            ->whereMonth('date', $monthNumber)
                            ->whereYear('date', $year)
                            ->get();


        

        /* =========================================================
            PRESENT / ABSENT / LEAVE
        ========================================================= */

        $presentDays = $attendance->where('present', 1)->count();

        $actualAbsentDays = $attendance->where('absent', 1)->count();

        $paidLeaves = $attendance->where('emp_leave', 1)->count();


        /* =========================================================
            DAYS IN MONTH
        ========================================================= */

        $daysInMonth = cal_days_in_month(
            CAL_GREGORIAN,
            $monthNumber,
            $year
        );


        /* =========================================================
            TOTAL IMPORTED DAYS
        ========================================================= */

        $totalImportedDays = $attendance->count();


        /* =========================================================
            MISSING DAYS
        ========================================================= */

        $missingDays =
            $daysInMonth - $totalImportedDays;

        if ($missingDays < 0) {

            $missingDays = 0;
        }


        /* =========================================================
            FINAL ABSENT DAYS
        ========================================================= */

        $absentDays =
            $actualAbsentDays + $missingDays;


        /* =========================================================
            WORKING DAYS
        ========================================================= */

        $workingDays =
            $presentDays + $paidLeaves;


        /* =========================================================
            DAYS IN MONTH
        ========================================================= */

        $daysInMonth = cal_days_in_month(
            CAL_GREGORIAN,
            $monthNumber,
            $year
        );


        /* =========================================================
            PER DAY SALARY
        ========================================================= */

        $perDaySalary =
            ($user->total_salary ?? 0) / $daysInMonth;


        /* =========================================================
            ABSENT DEDUCTION
        ========================================================= */

        $absentDeduction =
            $perDaySalary * $absentDays;


        /* =========================================================
            OVERTIME
        ========================================================= */

        $totalOtMinutes = 0;

        foreach ($attendance as $row) {

            if (!empty($row->ot_hr)) {

                $time = explode(':', $row->ot_hr);

                $hours   = (int) ($time[0] ?? 0);
                $minutes = (int) ($time[1] ?? 0);

                $totalOtMinutes +=
                    ($hours * 60) + $minutes;
            }
        }

        $overtimeHours =
            $totalOtMinutes / 60;

        $perHourOtRate =
            $user->overtime_salary_perhour ?? 0;

        $overtimeAmount =
            $overtimeHours * $perHourOtRate;


        /* =========================================================
            LATE DEDUCTION
        ========================================================= */

        $late = LateMarkCalculation::where(
                        'employee_id',
                        $empId
                    )
                    ->where('month', $month)
                    ->where('year', $year)
                    ->first();

        $lateDeduction =
            $late->amount_reduce ?? 0;


        /* =========================================================
            GROSS
        ========================================================= */

        $gross =
            ($user->total_salary ?? 0)
            +
            ($user->allowance_amount ?? 0)
            +
            ($user->hra_allowance_amount ?? 0)
            +
            $overtimeAmount
            -
            $absentDeduction;


        /* =========================================================
            PF
        ========================================================= */

        $pf =
            $user->pf_amount ?? 0;


        /* =========================================================
            ESI
        ========================================================= */

        $esi = 0;

        if ($user->ESI == 'Yes') {

            $esi = ($gross * 0.0075);
        }

        /* =========================================================
            EMPLOYEE ADVANCE EMI
        ========================================================= */

        $employeeAdvance = EmployeeAdvance::where('emp_id', $empId)
                            ->where('remaining_amount', '>', 0)
                            ->latest()
                            ->first();

        $advanceEmi = 0;

        if ($employeeAdvance) {

            $advanceEmi = $employeeAdvance->emi_amount ?? 0;
        }


        /* =========================================================
            NET SALARY
        ========================================================= */

        $net =
            $gross
            -
            $pf
             -
            $advanceEmi
            -            
            $esi
            -
            $lateDeduction;


        return response()->json([

            'status' => true,

            'basic_salary'       => round($user->total_salary ?? 0, 2),

            'gross_salary'       => round($gross, 2),

            'pf'                 => round($pf, 2),

            'esi'                => round($esi, 2),
            
            'advance_emi' => round($advanceEmi, 2),

            'late_deduction'     => round($lateDeduction, 2),

            'net_salary'         => round($net, 2),

            'overtime_hours'     => round($overtimeHours, 2),

            'overtime_amount'    => round($overtimeAmount, 2),

            'total_present_days' => $presentDays,

            'total_absent_days'  => $absentDays,

            'paid_leaves'        => $paidLeaves,

            'working_days'       => $workingDays,

            'per_day_salary'     => round($perDaySalary, 2),

            'absent_deduction'   => round($absentDeduction, 2),

            'per_hour_ot_rate'   => round($perHourOtRate, 2),
        ]);
    }


    /* =========================================================
        SALARY SLIP
    ========================================================= */

    public function salarySlip($id)
    {
        $salary = SalaryMaster::with('user')
                    ->findOrFail($id);

        $company = CompanySetting::first();

        return view(
            'backend.SalaryMaster.salary-slip',
            compact('salary', 'company')
        );
    }

    public function generateSalaryPage()
    {
        return view(
            'backend.SalaryMaster.generate-salary'
        );
    }

    

    public function salaryReport(Request $request)
    {
        return view(
            'backend.SalaryMaster.salary-report',
            [
                'month' => $request->month,
                'year'  => $request->year
            ]
        );
    }

   public function generateSalary(Request $request)
{
    $request->validate([
        'month' => 'required',
        'year'  => 'required'
    ]);

    $month = $request->month;
    $year  = $request->year;

    $monthNumber = date('m', strtotime($month));

    $users = User::where('status', 1)->get();

    $generatedCount = 0;
    $alreadyGeneratedCount = 0;
    $attendanceMissingCount = 0;

    foreach ($users as $user) {

        /*
        |--------------------------------------------------------------------------
        | Salary Already Generated
        |--------------------------------------------------------------------------
        */

        $salaryExists = SalaryMaster::where('emp_id', $user->id)
                            ->where('month', $month)
                            ->where('year', $year)
                            ->exists();

        if ($salaryExists) {

            $alreadyGeneratedCount++;
            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | Attendance Check
        |--------------------------------------------------------------------------
        */

        $attendance = AttendanceMaster::where('emp_id', $user->emp_id)
                        ->whereMonth('date', $monthNumber)
                        ->whereYear('date', $year)
                        ->get();

        if ($attendance->count() == 0) {

            $attendanceMissingCount++;
            continue;
        }

        

        /*
        |--------------------------------------------------------------------------
        | Present Days
        |--------------------------------------------------------------------------
        */

        $presentDays = $attendance->where('present', 1)->count();

        if ($presentDays <= 0) {

            $attendanceMissingCount++;
            continue;
        }

        $paidLeaves = $attendance->where('emp_leave', 1)->count();

        $actualAbsent = $attendance->where('absent', 1)->count();

        /*
        |--------------------------------------------------------------------------
        | Month Days
        |--------------------------------------------------------------------------
        */

        $daysInMonth = cal_days_in_month(
            CAL_GREGORIAN,
            $monthNumber,
            $year
        );

        $missingDays = $daysInMonth - $attendance->count();

        if ($missingDays < 0) {
            $missingDays = 0;
        }

        $absentDays = $actualAbsent + $missingDays;

        $workingDays = $presentDays + $paidLeaves;

        /*
        |--------------------------------------------------------------------------
        | Salary Calculation
        |--------------------------------------------------------------------------
        */

        $basicSalary = $user->total_salary ?? 0;

        $perDaySalary = $basicSalary / $daysInMonth;

        $absentDeduction = $perDaySalary * $absentDays;

        /*
        |--------------------------------------------------------------------------
        | Overtime
        |--------------------------------------------------------------------------
        */

        $totalOtMinutes = 0;

        foreach ($attendance as $row) {

            if (!empty($row->ot_hr)) {

                $time = explode(':', $row->ot_hr);

                $hours = (int)($time[0] ?? 0);
                $mins  = (int)($time[1] ?? 0);

                $totalOtMinutes += (($hours * 60) + $mins);
            }
        }

        $overtimeHours = $totalOtMinutes / 60;

        $perHourOtRate = $user->overtime_salary_perhour ?? 0;

        $overtimeAmount = $overtimeHours * $perHourOtRate;

        /*
        |--------------------------------------------------------------------------
        | Late Deduction
        |--------------------------------------------------------------------------
        */

        $late = LateMarkCalculation::where('employee_id', $user->id)
                    ->where('month', $month)
                    ->where('year', $year)
                    ->first();

        $lateDeduction = $late->amount_reduce ?? 0;

        /*
        |--------------------------------------------------------------------------
        | Gross Salary
        |--------------------------------------------------------------------------
        */

        $gross =
            ($user->total_salary ?? 0)
            +
            ($user->allowance_amount ?? 0)
            +
            ($user->hra_allowance_amount ?? 0)
            +
            $overtimeAmount
            -
            $absentDeduction;

        /*
        |--------------------------------------------------------------------------
        | PF & ESI
        |--------------------------------------------------------------------------
        */

        $pf = $user->pf_amount ?? 0;

        $esi = 0;

        if (($user->ESI ?? '') == 'Yes') {

            $esi = $gross * 0.0075;
        }

        /*
        |--------------------------------------------------------------------------
        | Advance EMI
        |--------------------------------------------------------------------------
        */

        $advance = EmployeeAdvance::where('emp_id', $user->id)
                    ->where('remaining_amount', '>', 0)
                    ->latest()
                    ->first();

        $advanceEmi = $advance->emi_amount ?? 0;

        /*
        |--------------------------------------------------------------------------
        | Net Salary
        |--------------------------------------------------------------------------
        */

        $netSalary =
            $gross
            -
            $pf
            -
            $esi
            -
            $advanceEmi
            -
            $lateDeduction;

        /*
        |--------------------------------------------------------------------------
        | Salary Number
        |--------------------------------------------------------------------------
        */

        $salaryNo =
            'SAL-'
            . date('Ym')
            . '-'
            . rand(1000,9999);

        /*
        |--------------------------------------------------------------------------
        | Save Salary
        |--------------------------------------------------------------------------
        */

        SalaryMaster::create([

            'date' => now(),

            'salary_no' => $salaryNo,

            'emp_id' => $user->id,

            'month' => $month,
            'year' => $year,

            'basic_salary' => $basicSalary,

            'gross' => round($gross,2),

            'pf' => round($pf,2),

            'esi' => round($esi,2),

            'advance_emi' => round($advanceEmi,2),

            'late_deduction' => round($lateDeduction,2),

            'overtime_hours' => round($overtimeHours,2),

            'overtime_amount' => round($overtimeAmount,2),

            'total_present_days' => $presentDays,

            'total_absent_days' => $absentDays,

            'paid_leaves' => $paidLeaves,

            'working_days' => $workingDays,

            'per_day_salary' => round($perDaySalary,2),

            'absent_deduction' => round($absentDeduction,2),

            'per_hour_ot_rate' => round($perHourOtRate,2),

            'net_salary' => round($netSalary,2),

            'payment_method' => 'cash',

            'createdby' => Auth::id()
        ]);


        if($advance && $advanceEmi > 0){

    $remainingAmount =
        $advance->remaining_amount - $advanceEmi;

    if($remainingAmount < 0){
        $remainingAmount = 0;
    }

    EmployeeAdvancePayment::create([

        'parent_id'        => $advance->id,

        'emp_id'           => $user->id,

        'advance'          => $advanceEmi,

        'payment_method'   => 'cash',

        'remaining_amount' => $remainingAmount,

        'narration'        =>
            'Advance EMI deducted from Salary - '
            .$month.' '.$year,

        'date'             => now()

    ]);

    $advance->update([

        'remaining_amount' => $remainingAmount

    ]);
}

        $generatedCount++;
    }

    /*
    |--------------------------------------------------------------------------
    | Final Response
    |--------------------------------------------------------------------------
    */

    if ($generatedCount == 0) {

        return response()->json([
            'status' => false,
            'message' => 'No salary generated. Attendance missing or salary already generated.'
        ]);
    }

    return response()->json([
        'status' => true,
        'message' =>
            $generatedCount . ' Employee Salary Generated Successfully. ' .
            $alreadyGeneratedCount . ' Already Generated. ' .
            $attendanceMissingCount . ' Attendance Missing.'
    ]);
}


public function generatedMonthList()
{
    $data = SalaryMaster::select(
                'month',
                'year'
            )
            ->selectRaw('COUNT(*) as total_employee')
            ->selectRaw('SUM(net_salary) as total_salary')
            ->selectRaw('MAX(created_at) as generated_on')
            ->groupBy('month','year')
            ->orderByDesc('year')
            ->get();

    return DataTables::of($data)

        ->addIndexColumn()

        ->addColumn('generated_on', function($row){

            return date(
                'd-m-Y H:i',
                strtotime($row->generated_on)
            );
        })

        ->addColumn('total_salary', function($row){

            return number_format(
                $row->total_salary,
                2
            );
        })

        ->addColumn('action', function ($row) {

            return '
                <button
                    class="btn btn-info btn-sm viewSalary"
                    data-month="'.$row->month.'"
                    data-year="'.$row->year.'">
                    View
                </button>

                <button
                    class="btn btn-danger btn-sm deleteSalaryMonth"
                    data-month="'.$row->month.'"
                    data-year="'.$row->year.'">
                    Delete
                </button>

                <button
                    class="btn btn-warning btn-sm regenerateSalaryMonth"
                    data-month="'.$row->month.'"
                    data-year="'.$row->year.'">
                    Regenerate
                </button>
            ';
        })

        ->rawColumns(['action'])

        ->make(true);
}

public function getSalarySummary(Request $request)
{
    $month = $request->month;
    $year  = $request->year;

    $totalEmployees = User::where('status',1)->count();

    $generated = SalaryMaster::where('month',$month)
                    ->where('year',$year)
                    ->count();

    return response()->json([

        'total_employees' => $totalEmployees,

        'generated' => $generated,

        'pending' => ($totalEmployees - $generated),

        'already_generated' => ($generated > 0)
    ]);
}

public function deleteMonthSalary(Request $request)
{
    $salaries = SalaryMaster::where('month',$request->month)
                    ->where('year',$request->year)
                    ->get();

    foreach($salaries as $salary){

        if(($salary->advance_emi ?? 0) > 0){

            $advance = EmployeeAdvance::where(
                            'emp_id',
                            $salary->emp_id
                        )
                        ->latest()
                        ->first();

            if($advance){

                $advance->update([

                    'remaining_amount' =>
                        $advance->remaining_amount
                        +
                        $salary->advance_emi
                ]);
            }

            EmployeeAdvancePayment::where(
                    'emp_id',
                    $salary->emp_id
                )
                ->where(
                    'advance',
                    $salary->advance_emi
                )
                ->delete();
        }

        $salary->delete();
    }

    return response()->json([

        'status' => true,

        'message' =>
            'Salary Deleted Successfully & Advance Restored'
    ]);
}
public function salaryReportData(Request $request)
{
    $query = SalaryMaster::with('user');

    if(!empty($request->month)){
        $query->where('month', $request->month);
    }

    if(!empty($request->year)){
        $query->where('year', $request->year);
    }

    return DataTables::of($query)

        ->addIndexColumn()

        ->addColumn('employee_name', function ($row) {
            return $row->user->name ?? '';
        })

        ->addColumn('salary_month', function ($row) {
            return $row->month.' - '.$row->year;
        })

        ->addColumn('date', function ($row) {
            return \Carbon\Carbon::parse($row->date)
                ->format('d-m-Y');
        })

        ->addColumn('gross', function ($row) {
            return number_format($row->gross,2);
        })

        ->addColumn('net_salary', function ($row) {
            return number_format($row->net_salary,2);
        })

        ->make(true);
}
    /* =========================================================
            DELETE
        ========================================================= */

        public function destroy($id)
        {
            $salary = SalaryMaster::findOrFail($id);

            /*
            |--------------------------------------------------------------------------
            | ADVANCE EMI RETURN
            |--------------------------------------------------------------------------
            */

            if (($salary->advance_emi ?? 0) > 0) {

                // Find latest advance
                $advance = EmployeeAdvance::where('emp_id', $salary->emp_id)
                            ->latest()
                            ->first();

                if ($advance) {

                    /*
                    |--------------------------------------------------------------------------
                    | RETURN EMI AMOUNT
                    |--------------------------------------------------------------------------
                    */

                    $advance->update([

                        'remaining_amount' =>
                            $advance->remaining_amount + $salary->advance_emi
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | DELETE ADVANCE PAYMENT ENTRY
                |--------------------------------------------------------------------------
                */

                EmployeeAdvancePayment::where('emp_id', $salary->emp_id)

                    ->where('advance', $salary->advance_emi)

                    ->whereDate('date', $salary->date)

                    ->delete();
            }

            

            /*
            |--------------------------------------------------------------------------
            | DELETE SALARY
            |--------------------------------------------------------------------------
            */

            $salary->delete();

            return redirect()
                    ->route('SalaryMaster')
                    ->with('success', 'Salary and advance entry deleted successfully.');
        }
}