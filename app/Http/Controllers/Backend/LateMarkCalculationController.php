<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Backend\LateMarkCalculation;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class LateMarkCalculationController extends Controller
{
    public function index(Request $request)
{
    if ($request->ajax()) {

        $query = LateMarkCalculation::with('employee')
            ->select([
                'id',
                'employee_id',
                'month',
                'year',
                'late_mark_count',
                'total_late_time',
                'amount_reduce'
            ])
            ->orderByDesc('id');

        /*
        |--------------------------------------------------------------------------
        | Custom Filters
        |--------------------------------------------------------------------------
        */

        // Employee Name
        if ($request->filled('employee')) {

    $search = trim($request->employee);

    $query->whereHas('employee', function ($q) use ($search) {
        $q->where('name', 'LIKE', "%{$search}%");
    });
}

        // Month
        if ($request->filled('month')) {

            $query->where('month', 'like', '%' . trim($request->month) . '%');
        }

        // Year
        if ($request->filled('year')) {

            $query->where('year', 'like', '%' . trim($request->year) . '%');
        }

        // Late Mark Count
        if ($request->filled('late_mark_count')) {

            $query->where(
                'late_mark_count',
                'like',
                '%' . trim($request->late_mark_count) . '%'
            );
        }

        // Total Late Time
        if ($request->filled('total_late_time')) {

            $query->where(
                'total_late_time',
                'like',
                '%' . trim($request->total_late_time) . '%'
            );
        }

        // Amount Reduce
        if ($request->filled('amount_reduce')) {

            $query->where(
                'amount_reduce',
                'like',
                '%' . trim($request->amount_reduce) . '%'
            );
        }

        return datatables()->of($query)

            ->addIndexColumn()

            // Employee Name
            ->addColumn('employee', function ($row) {

                return $row->employee->Name
                    ?? '<span class="text-danger">N/A</span>';
            })

            // Total Late Time Format
            ->editColumn('total_late_time', function ($row) {

                if (empty($row->total_late_time)) {
                    return '-';
                }

                $time = explode(':', $row->total_late_time);

                $hours   = $time[0] ?? 0;
                $minutes = $time[1] ?? 0;

                return (int)$hours . ' Hr ' . $minutes . ' Min';
            })

            // Amount Format
            ->editColumn('amount_reduce', function ($row) {

                return number_format($row->amount_reduce, 2);
            })

            // Action Buttons
            ->addColumn('actions', function ($row) {

                $editUrl   = route('LateMarkCalculation.edit', $row->id);
                $deleteUrl = route('LateMarkCalculation.delete', $row->id);

                $formId = 'delete-form-' . $row->id;

                $btn = '';

                // Edit
                if (hasPermission('edit_LateMarkCalculation')) {

                    $btn .= '
                        <a href="' . $editUrl . '" class="me-2 text-primary">
                            <i data-feather="edit-2"></i>
                        </a>
                    ';
                }

                // Delete
                if (hasPermission('delete_LateMarkCalculation')) {

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

            ->rawColumns([
                'employee',
                'actions'
            ])

            ->make(true);
    }

    return view('backend.late_mark.index');
}

    public function create()
    {
        $employees = User::get();

        return view('backend.late_mark.create',compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([

            'employee_id'      => 'required',

            'month'            => 'required',

            'year'             => 'required',

            'late_mark_count'  => 'required|numeric|min:1',

            'total_late_time'  => 'required',

            'amount_reduce'    => 'required|numeric|min:0',

        ]);


        /* =========================================
        CHECK DUPLICATE ENTRY
        ========================================== */

        $exists = LateMarkCalculation::where('employee_id', $request->employee_id)

                    ->where('month', $request->month)

                    ->where('year', $request->year)

                    ->exists();

        if($exists){

            return back()

                ->withInput()

                ->withErrors([

                    'employee_id' =>

                    'This employee late mark already exists for selected month and year.'

                ]);
        }


        /* =========================================
        STORE DATA
        ========================================== */

        LateMarkCalculation::create([

            'employee_id'      => $request->employee_id,

            'month'            => $request->month,

            'year'             => $request->year,

            'late_mark_count'  => $request->late_mark_count,

            'total_late_time'  => date('H:i:s', strtotime($request->total_late_time)),

            'amount_reduce'    => $request->amount_reduce,

            'createdby'        => auth()->id(),

        ]);


        return redirect()

            ->route('LateMarkCalculation')

            ->with('success', 'Created Successfully');
    }

    public function edit($id)
    {
        $old = LateMarkCalculation::findOrFail($id);

        $employees = User::get();

        return view('backend.late_mark.create',compact('old','employees'));
    }

    public function update(Request $request)
    {
        $request->validate([

            'employee_id'      => 'required',

            'month'            => 'required',

            'year'             => 'required',

            'late_mark_count'  => 'required|numeric|min:1',

            'total_late_time'  => 'required',

            'amount_reduce'    => 'required|numeric|min:0',

        ]);


        /* =========================================
        CHECK DUPLICATE ENTRY
        ========================================== */

        $exists = LateMarkCalculation::where('employee_id', $request->employee_id)

                    ->where('month', $request->month)

                    ->where('year', $request->year)

                    ->where('id', '!=', $request->id)

                    ->exists();

        if($exists){

            return back()

                ->withInput()

                ->withErrors([

                    'employee_id' =>

                    'This employee late mark already exists for selected month and year.'

                ]);
        }


        /* =========================================
        FIND RECORD
        ========================================== */

        $data = LateMarkCalculation::findOrFail($request->id);


        /* =========================================
        UPDATE DATA
        ========================================== */

        $data->update([

            'employee_id'      => $request->employee_id,

            'month'            => $request->month,

            'year'             => $request->year,

            'late_mark_count'  => $request->late_mark_count,

            'total_late_time'  => date('H:i:s', strtotime($request->total_late_time)),

            'amount_reduce'    => $request->amount_reduce,

            'updatedby'        => auth()->id(),

        ]);


        return redirect()

            ->route('LateMarkCalculation')

            ->with('success', 'Updated Successfully');
    }

    public function destroy($id)
    {
        LateMarkCalculation::findOrFail($id)->delete();

        return redirect()->back()
            ->with('success','Deleted Successfully');
    }
}
