<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

use App\Models\User;
use App\Models\Backend\AttendanceMaster;
use App\Models\Backend\UserShiftAssignment;

use Illuminate\Support\Facades\Auth;

class AttendanceImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $rows = $rows->toArray();

        // STORE IMPORT ERRORS
        $errors = [];

        // DAYS ROW
        $daysRow = $rows[6] ?? [];

        // EMPLOYEE BLOCKS
        for ($i = 10; $i < count($rows); $i += 6)
        {
            // SKIP EMPTY BLOCK
            if (empty($rows[$i][3]))
            {
                continue;
            }

            // EMPLOYEE CODE
            $empCode = trim($rows[$i][3]);

            // REMOVE LEADING ZERO
            $empCode = ltrim($empCode, '0');

            // EMPLOYEE NAME
            $empName = trim($rows[$i][13] ?? '');

            // FIND EMPLOYEE
            $employee = User::whereRaw('TRIM(emp_id) = ?', [$empCode])->first();

            // EMPLOYEE NOT FOUND
            if (!$employee)
            {
                $errors[] = [
                    'emp_id'   => $empCode,
                    'emp_name' => $empName ?: 'N/A',
                    'date'     => '',
                    'error'    => 'Employee not found'
                ];

                continue;
            }

            // ATTENDANCE ROWS
            $statusRow  = $rows[$i + 1] ?? [];
            $inTimeRow  = $rows[$i + 2] ?? [];
            $outTimeRow = $rows[$i + 3] ?? [];
            $totalRow   = $rows[$i + 4] ?? [];

            // LOOP DATE COLUMNS
            for ($col = 2; $col <= 30; $col++)
            {
                // SKIP BLANK DAY
                if (empty($daysRow[$col]))
                {
                    continue;
                }

                // EXTRACT DAY
                preg_match('/(\d+)/', $daysRow[$col], $matches);

                if (!isset($matches[1]))
                {
                    continue;
                }

                $day = $matches[1];

                // REPORT TEXT
                $reportText = $rows[2][1] ?? '';

                preg_match('/([A-Za-z]+)\s+\d+\s+(\d{4})/', $reportText, $reportMatch);

                $monthName = $reportMatch[1] ?? date('F');
                $year      = $reportMatch[2] ?? date('Y');

                // MONTH
                $month = Carbon::parse("1 $monthName $year")->month;

                // FINAL DATE
                $date = Carbon::create($year, $month, $day)->format('Y-m-d');

                // STATUS
                $status = trim($statusRow[$col] ?? '');

                // DEFAULTS
                $present = 0;
                $absent  = 0;
                $leave   = 0;

                // STATUS CALCULATION
                if ($status == 'P')
                {
                    $present = 1;
                }
                elseif ($status == 'A')
                {
                    $absent = 1;
                }
                elseif ($status == '½P')
                {
                    $present = 0.5;
                    $absent  = 0.5;
                }
                else
                {
                    $leave = 1;
                }

                // INTIME
                $intime = trim($inTimeRow[$col] ?? '');

                // OUTTIME
                $outtime = trim($outTimeRow[$col] ?? '');

                // WORK HOURS
                $workHr = trim($totalRow[$col] ?? '');

                /*
                |--------------------------------------------------------------------------
                | FIND SHIFT ASSIGNMENT
                |--------------------------------------------------------------------------
                */

                $shiftAssign = UserShiftAssignment::with('shift')

                    ->where('emp_id', $employee->emp_id)

                    ->whereDate('from_date', '<=', $date)

                    ->where(function ($q) use ($date) {

                        $q->whereDate('to_date', '>=', $date)
                          ->orWhereNull('to_date');
                    })

                    ->where('is_active', 1)

                    ->first();

                // SHIFT NOT FOUND
                if (!$shiftAssign)
                {
                    $errors[] = [
                        'emp_id'   => $employee->emp_id,
                        'emp_name' => $employee->name ?? $empName,
                        'date'     => $date,
                        'error'    => 'Shift not assigned'
                    ];

                    continue;
                }

                // SHIFT DETAILS
                $shiftId      = $shiftAssign->shift_id;

                $shiftInTime  = $shiftAssign->shift->shift_intime ?? null;

                $shiftOutTime = $shiftAssign->shift->shift_outtime ?? null;

                /*
                |--------------------------------------------------------------------------
                | LATE MINUTES
                |--------------------------------------------------------------------------
                */

                $lateMinutes = 0;

                if ($intime && $shiftInTime)
                {
                    try
                    {
                        $shiftStart = Carbon::parse($date . ' ' . $shiftInTime);

                        $actualIn = Carbon::parse($date . ' ' . $intime);

                        if ($actualIn->gt($shiftStart))
                        {
                            $lateMinutes = $shiftStart->diffInMinutes($actualIn);
                        }
                    }
                    catch (\Exception $e)
                    {
                        $lateMinutes = 0;
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | OT CALCULATION
                |--------------------------------------------------------------------------
                */

                $otHr = '00:00';

                if (!empty($workHr))
                {
                    try
                    {
                        $timeParts = explode(':', $workHr);

                        $workHours   = (int) ($timeParts[0] ?? 0);

                        $workMinutes = (int) ($timeParts[1] ?? 0);

                        $totalMinutes = ($workHours * 60) + $workMinutes;

                        // 8 HOURS STANDARD
                        $normalMinutes = 480;

                        if ($totalMinutes > $normalMinutes)
                        {
                            $otMinutes = $totalMinutes - $normalMinutes;

                            $otH = floor($otMinutes / 60);

                            $otM = $otMinutes % 60;

                            $otHr = sprintf('%02d:%02d', $otH, $otM);
                        }
                    }
                    catch (\Exception $e)
                    {
                        $otHr = '00:00';
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | DUPLICATE CHECK
                |--------------------------------------------------------------------------
                */

                $alreadyExists = AttendanceMaster::where('date', $date)

                    ->where('emp_id', $employee->emp_id)

                    ->first();

                if ($alreadyExists)
                {
                    $errors[] = [
                        'emp_id'   => $employee->emp_id,
                        'emp_name' => $employee->name ?? $empName,
                        'date'     => $date,
                        'error'    => 'Attendance already exists'
                    ];

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | SAVE ATTENDANCE
                |--------------------------------------------------------------------------
                */

                try
                {
                    AttendanceMaster::create([

                        'date'        => $date,

                        'shift'       => $shiftId,

                        'emp_id'      => $employee->emp_id,

                        'designation' => $employee->designation ?? '',

                        'present'     => $present,

                        'absent'      => $absent,

                        'emp_leave'   => $leave,

                        'intime'      => $intime ?: null,

                        'outtime'     => $outtime ?: null,

                        'late_mins'   => $lateMinutes,

                        'early_dep'   => null,

                        'work_hr'     => $workHr ?: null,

                        'ot_hr'       => $otHr,

                        'createdby'   => Auth::id(),
                    ]);
                }
                catch (\Exception $e)
                {
                    $errors[] = [
                        'emp_id'   => $employee->emp_id,
                        'emp_name' => $employee->name ?? $empName,
                        'date'     => $date,
                        'error'    => $e->getMessage()
                    ];
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | FINAL SESSION ERRORS
        |--------------------------------------------------------------------------
        */

        if (count($errors) > 0)
        {
            session()->flash('import_errors', $errors);
        }
        else
        {
            session()->flash('success', 'Attendance imported successfully!');
        }
    }
}
