<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\EmployeeAdvance;
use App\Models\Backend\Empadv_Pay;
use App\Models\Backend\Staff;
use App\Models\User;

use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class EmployeeAdvanceController extends Controller
{
    public function index(Request $request)
{
    if ($request->ajax()) {

        $query = EmployeeAdvance::leftJoin('staff', 'staff.ID', '=', 'employee_advance.emp_id')
            ->select([
                'employee_advance.id',
                'employee_advance.Date',
                'employee_advance.advance',
                'employee_advance.emi_amount',
                'employee_advance.total_installments',
                'staff.Name as employee_name'
            ])
            ->orderByDesc('employee_advance.id');

        return DataTables::of($query)
            ->addIndexColumn()

             ->editColumn('Date', function ($row) {
                    return \Carbon\Carbon::parse($row->Date)->format('d-m-Y');
                })

            ->addColumn('actions', function ($row) {

                $editUrl   = route('EmployeeAdvance.edit', $row->id);
                $deleteUrl = route('EmployeeAdvance.destroy', $row->id);
                $paymentUrl = url('backend/EmployeeAdvance/employee_advance_payment/' . $row->id);

                return '
                    <a href="'.$editUrl.'" class="me-2 text-primary">
                        <i data-feather="edit-2"></i>
                    </a>

                    <a href="'.$paymentUrl.'" class="me-2 text-success">
                        <i data-feather="credit-card"></i>
                    </a>

                    <a href="#" class="text-danger delete-confirm" data-id="delete-'.$row->id.'">
                        <i data-feather="trash"></i>
                    </a>

                    <form id="delete-'.$row->id.'" action="'.$deleteUrl.'" method="POST" class="d-none">
                        '.csrf_field().'
                        '.method_field('DELETE').'
                    </form>
                ';
            })

            ->rawColumns(['actions'])
            ->make(true);
    }

    return view('backend.EmployeeAdvance.index');
}

 public function create()
    {
         $users = User::select('id', 'name')->orderBy('name')->get();
         $staffs = Staff::select('ID', 'Name')->orderBy('ID')->get();

          // 🔥 Get last record_no
            $lastRecord = EmployeeAdvance::max('record_no');

            // If null → start from 1
            $nextRecordNo = $lastRecord ? $lastRecord + 1 : 1;

        return view('backend.EmployeeAdvance.create', compact('users','staffs', 'nextRecordNo'));
    }

     /**
     * Store a newly created resource in storage.
     */


    public function store(Request $request)
    {
        $clientId = session()->get('selected_scheme_id');

       $validated = $request->validate([
          'emp_id' => 'required',
          'record_no' => 'required|numeric',
          'date' => 'required|date',
          'advance' => 'required|numeric',
          'emi_amount' => 'required|numeric',
          'total_installments' => 'required|numeric',
          'remaining_amount' => 'required|numeric',
          'narration' => 'required|string|max:255|regex:/^[A-Za-z ]+$/',
          'cheque_no' => 'numeric',

          // ✅ ADD THESE
        'Pay_type' => 'required',
        'account_no' => 'required',

        ]);

        $data = new EmployeeAdvance;

        $data->record_no = $validated['record_no'];
        $data->ClientID = $clientId;
        $data->Date = $validated['date'];
        $data->emp_id = $validated['emp_id'];
        $data->advance = $validated['advance'];
        $data->emi_amount = $validated['emi_amount'];
        $data->total_installments = $validated['total_installments'];
        $data->remaining_amount = $validated['remaining_amount'];
        $data->narration = $validated['narration'];
        $data->cheque_no = $validated['cheque_no'];

        $data->payment_method = $validated['Pay_type'];
        $data->account_no     = $validated['account_no'];

        $data->createdby = Auth::id();

        $data->save(); // ✅ Save the rack

        return redirect()->route('EmployeeAdvance')->with('success', 'Employee Advance added successfully!');
    }

      public function edit(string $id)
    {
       $old = EmployeeAdvance::find($id);
        $users = User::select('id', 'name')->orderBy('name')->get();

        $staffs = Staff::select('ID', 'Name')->orderBy('ID')->get();

        return view('backend.EmployeeAdvance.create', compact('old','users', 'staffs'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $clientId = session()->get('selected_scheme_id');

        $id = $request->id;
        $data = EmployeeAdvance::find($id);

        $validated = $request->validate([
        'emp_id' => 'required',

          'record_no' => 'required|numeric',
         'date' => 'required|date',
          'advance' => 'required|numeric',
          'emi_amount' => 'required|numeric',
          'total_installments' => 'required|numeric',
          'remaining_amount' => 'required|numeric',
          'narration' => 'required|string|max:255|regex:/^[A-Za-z ]+$/',
          'cheque_no' => 'numeric',

          // ✅ ADD THESE
            'Pay_type' => 'required',
            'account_no' => 'required',
        ]);

        $data->record_no = $validated['record_no'];
        $data->ClientID = $clientId;
        $data->Date = $validated['date'];
        $data->emp_id = $validated['emp_id'];
        $data->advance = $validated['advance'];
        $data->emi_amount = $validated['emi_amount'];
        $data->total_installments = $validated['total_installments'];
        $data->remaining_amount = $validated['remaining_amount'];
        $data->narration = $validated['narration'];
        $data->cheque_no = $validated['cheque_no'];

        $data->payment_method = $validated['Pay_type'];
        $data->account_no     = $validated['account_no'];

        $data->save(); // ✅ Save the rack

        return redirect()->route('EmployeeAdvance')->with('success', 'Employee Advance updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = EmployeeAdvance::findOrFail($id);

        if (!canDeleteRecord('emp_avance_pay', 'pid', $id)) {
            return redirect()
                ->route('EmployeeAdvance')
                ->with('error', 'Cannot delete its used.');
        }
        
        $data->delete();
        return redirect()->route('EmployeeAdvance')->with('success', 'Employee Advance deleted successfully!');
    }


  public function employeeAdvancePayment($id)
{
    $old = EmployeeAdvance::findOrFail($id);

    $users = Staff::select('ID', 'Name')
        ->where('id', $old->emp_id)
        ->first(); // use first() instead of get() to get single model

    return view('backend.EmployeeAdvance.employee_advance_payment', compact('old','users'));
}

public function storePayment(Request $request)
{
    $clientId = session()->get('selected_scheme_id');

    $request->validate([
        'id'             => 'required', // EmployeeAdvance ID
        'date'           => 'required|date',
        'payment_method' => 'required',
        'account_no' => 'required',
        'amt_pay'        => 'required|numeric',
        'narration'      => 'required',
        'cheque_no'      => 'nullable|required_if:payment_method,cheque'
    ]);

    // Get advance record
    $advance = EmployeeAdvance::findOrFail($request->id);

    // 👉 Total already paid
    $totalPaid = Empadv_Pay::where('pid', $advance->id)->sum('amt_pay');

    // 👉 New remaining
    $remaining = $advance->advance - ($totalPaid + $request->amt_pay);

    // 👉 Save payment
    Empadv_Pay::create([
        'Id'          => uniqid(),
        'pid'         => $advance->id, // ✅ IMPORTANT
        'ClientID'         => $clientId, // ✅ IMPORTANT
        'emp_id'      => $advance->emp_id,
        'Date'        => $request->date,
        'account_no'        => $request->account_no,
        'payment_method'  => $request->payment_method,
        'amt_pay'     => $request->amt_pay,
        'cheque_no' => $request->payment_method == 'cheque' ? $request->cheque_no : null,
        'narration'   => $request->narration,
        'created_by'  => Auth::id(),
    ]);

    // 👉 Update remaining in main table (optional but recommended)
    $advance->remaining_amount = $remaining;
    $advance->save();

    return redirect()->route('EmployeeAdvance')
        ->with('success', 'Payment Saved Successfully!');
}


}
