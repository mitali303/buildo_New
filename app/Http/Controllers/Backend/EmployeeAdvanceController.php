<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Backend\EmployeeAdvancePayment;
use Illuminate\Http\Request;
use App\Models\Backend\EmployeeAdvance;
use App\Models\User;
use Carbon\Carbon;

use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class EmployeeAdvanceController extends Controller
{
    public function index(Request $request)
{
    if ($request->ajax()) {

        $query = EmployeeAdvance::with('user')
            ->select([
                'id',
                'date',
                'emp_id',
                'advance',
                'remaining_amount',
                'emi_amount',
                'total_installments'
            ])
            ->orderByDesc('id');

        // Custom Search Filters
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->filled('employee_name')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->employee_name . '%');
            });
        }

        if ($request->filled('advance')) {
            $query->where('advance', 'like', '%' . $request->advance . '%');
        }

        if ($request->filled('emi_amount')) {
            $query->where('emi_amount', 'like', '%' . $request->emi_amount . '%');
        }

        if ($request->filled('remaining_amount')) {
            $query->where('remaining_amount', 'like', '%' . $request->remaining_amount . '%');
        }

        if ($request->filled('total_installments')) {
            $query->where('total_installments', 'like', '%' . $request->total_installments . '%');
        }

        return DataTables::of($query)

            ->addIndexColumn()

            ->addColumn('employee_name', function ($row) {
                return $row->user->name ?? 'N/A';
            })

            ->editColumn('date', function ($row) {
                return $row->date
                    ? \Carbon\Carbon::parse($row->date)->format('d-m-Y')
                    : '';
            })

            ->addColumn('actions', function ($row) {

                $editUrl = route('EmployeeAdvance.edit', $row->id);
                $deleteUrl = route('EmployeeAdvance.delete', $row->id);
                $paymentUrl = url('backend/EmployeeAdvance/employee_advance_payment/' . $row->id);
                $historyUrl = route('EmployeeAdvance.history', $row->id);

                $formId = 'delete-form-' . $row->id;

                $actions = '';

                if (hasPermission('edit_EmployeeAdvance')) {
                    $actions .= '
                        <a href="'.$editUrl.'" class="me-2 text-primary">
                            <i data-feather="edit-2"></i>
                        </a>';
                }

                if (hasPermission('edit_EmployeeAdvance')) {
                    $actions .= '
                        <a href="'.$paymentUrl.'" class="me-2 text-success">
                            <i data-feather="credit-card"></i>
                        </a>';
                }

                $actions .= '
                    <a href="'.$historyUrl.'" class="me-2 text-info">
                        <i data-feather="clock"></i>
                    </a>';

                if (hasPermission('delete_EmployeeAdvance')) {
                    $actions .= '
                        <a href="#" class="text-danger delete-confirm" data-id="'.$formId.'">
                            <i data-feather="trash"></i>
                        </a>

                        <form id="'.$formId.'" action="'.$deleteUrl.'" method="POST" class="d-none">
                            '.csrf_field().'
                            '.method_field('DELETE').'
                        </form>';
                }

                return $actions;
            })

            ->rawColumns(['actions'])
            ->make(true);
    }

    return view('backend.EmployeeAdvance.index');
}
 public function create()
    {
         $users = User::select('id', 'name')->orderBy('name')->get();
        $nextRecordNo = EmployeeAdvance::max('record_no') + 1;
        return view('backend.EmployeeAdvance.create', compact('users','nextRecordNo'));
    }

     /**
     * Store a newly created resource in storage.
     */


    public function store(Request $request)
    {

    $clientId = session('selected_scheme_id');

       $validated = $request->validate([
          'emp_id' => 'required',
          'record_no' => 'required|numeric',
          'date' => 'required|date',
          'advance' => 'required|numeric',
          'emi_amount' => 'required|numeric',
          'total_installments' => 'required|numeric',
          'remaining_amount' => 'required|numeric',
          'narration' => 'required|string|max:255|regex:/^[A-Za-z ]+$/',

        ]);

        $data = new EmployeeAdvance;

        $data->record_no = $validated['record_no'];
        $data->ClientID = $clientId;
        $data->account_no = $request['account_no'];
        $data->date = $validated['date'];
        $data->emp_id = $validated['emp_id'];
        $data->advance = $validated['advance'];
        $data->emi_amount = $validated['emi_amount'];
        $data->total_installments = $validated['total_installments'];
        $data->remaining_amount = $validated['remaining_amount'];
        $data->reconciliation = 0;
        $data->narration = $validated['narration'];

        $data->createdby = Auth::id();

        $data->save(); // ✅ Save the rack

        return redirect()->route('EmployeeAdvance')->with('success', 'Employee Advance added successfully!');
    }

      public function edit(string $id)
    {
       $old = EmployeeAdvance::find($id);
        $users = User::select('id', 'name')->orderBy('name')->get();
        return view('backend.EmployeeAdvance.create', compact('old','users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $id = $request->id;
        $data = EmployeeAdvance::find($id);

        $clientId = session('selected_scheme_id');

        $validated = $request->validate([
        'emp_id' => 'required',

          'record_no' => 'required|numeric',
         'date' => 'required|date',
          'advance' => 'required|numeric',
          'emi_amount' => 'required|numeric',
          'total_installments' => 'required|numeric',
          'remaining_amount' => 'required|numeric',
          'narration' => 'required|string|max:255|regex:/^[A-Za-z ]+$/',
        ]);

        $data->record_no = $validated['record_no'];
        $data->ClientID = $clientId;
        $data->date = $validated['date'];
        $data->account_no = $request['account_no'];
        $data->emp_id = $validated['emp_id'];
        $data->advance = $validated['advance'];
        $data->emi_amount = $validated['emi_amount'];
        $data->total_installments = $validated['total_installments'];
        $data->remaining_amount = $validated['remaining_amount'];
        $data->narration = $validated['narration'];
        $data->reconciliation = 0;
        $data->save(); // ✅ Save the rack

        return redirect()->route('EmployeeAdvance')->with('success', 'Employee Advance updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = EmployeeAdvance::findOrFail($id);
        $data->delete();
        return redirect()->route('EmployeeAdvance')->with('success', 'Employee Advance deleted successfully!');
    }

    public function paymentHistory($id)
{
    $advance = EmployeeAdvance::with('user')
                ->findOrFail($id);

    $payments = EmployeeAdvancePayment::where(
                    'parent_id',
                    $id
                )
                ->orderBy('id','desc')
                ->get();
    $paidInstallments =
    EmployeeAdvancePayment::where(
        'parent_id',
        $id
    )->count();

    $remainingInstallments =
    $advance->total_installments -
    $paidInstallments;            

    return view(
        'backend.EmployeeAdvance.payment_history',
        compact('advance','payments','remainingInstallments','paidInstallments')
    );
}


  public function employeeAdvancePayment($id)
{
    $old = EmployeeAdvance::findOrFail($id);

    $users = User::select('id', 'name')
        ->where('id', $old->emp_id)
        ->first(); // use first() instead of get() to get single model

    return view('backend.EmployeeAdvance.employee_advance_payment', compact('old','users'));
}

public function storePayment(Request $request)
{
    $request->validate([
        'id'               => 'required',
        'date'             => 'required|date',
        'payment_method'   => 'required',
        'cheque_no'        => 'required_if:payment_method,cheque',
        'received_amount' => 'required|numeric',
        'narration'        => 'required'
    ]);

    $advance = EmployeeAdvance::findOrFail($request->id);

    $newRemaining =
            $advance->remaining_amount - $request->received_amount;

        if ($newRemaining < 0) {
            $newRemaining = 0;
        }

    // Save Payment entry
    $payment = new EmployeeAdvancePayment();
    $payment->parent_id = $advance->id;
    $payment->emp_id              = $advance->emp_id;
    $payment->advance      = $request->received_amount;
    $payment->payment_method      = $request->payment_method;
    $payment->cheque_no           = $request->cheque_no;
    $payment->remaining_amount    = $newRemaining;
    $payment->narration           = $request->narration;
    $payment->date                = $request->date;
    $payment->save();

    // Update advance remaining
        $advance->update([

            'remaining_amount' => $newRemaining
        ]);

    return redirect()->route('EmployeeAdvance')
                     ->with('success','Employee Advance Payment Saved Successfully!');
}


}
