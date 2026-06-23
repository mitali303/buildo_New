<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Backend\OwnerPayment;
use App\Models\Backend\OwnerPaymentDetail;
use App\Models\Backend\Permission;
use App\Models\Backend\SchemeDetail;
use App\Models\Backend\Role;
use App\Models\Backend\Scheme;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OwnerPaymentController extends Controller
{
    public function index(Request $request)
{
    if ($request->ajax()) {

        $clientId = session('selected_scheme_id');

        $query = OwnerPayment::where('ClientID', $clientId);

        // ✅ Date Filtering
        if ($request->from_date && $request->to_date) {
            $query->whereBetween('Date', [
                $request->from_date,
                $request->to_date
            ]);
        } elseif ($request->from_date) {
            $query->whereDate('Date', '>=', $request->from_date);
        } elseif ($request->to_date) {
            $query->whereDate('Date', '<=', $request->to_date);
        }

        return DataTables::of($query)
            ->addIndexColumn()

            ->editColumn('Date', function ($row) {
                return Carbon::parse($row->Date)->format('d-m-Y');
            })

            ->addColumn('actions', function ($row) {

                $editUrl   = route('Owner_Pay.edit', $row->ID);
                $deleteUrl = route('Owner_Pay.delete', $row->ID);
                $formId    = 'delete-form-' . $row->ID;

                $actions = '';

                if (hasPermission('edit_owner_payment')) {
                    $actions .= '<a href="'.$editUrl.'" class="me-2 text-primary">
                                    <i data-feather="edit-2"></i>
                                </a>';
                }

                if (hasPermission('delete_owner_payment')) {
                    $actions .= '<a href="#" class="text-danger delete-confirm" data-id="'.$formId.'">
                                    <i data-feather="trash"></i>
                                </a>
                                <form id="'.$formId.'" action="'.$deleteUrl.'" method="POST" class="d-none">
                                    '.csrf_field().'
                                    '.method_field("DELETE").'
                                </form>';
                }

                return $actions;
            })

            ->rawColumns(['actions'])
            ->make(true);
    }

    return view('backend.Owner_pay.index');
}

public function create()
{
    $schemeId = session('selected_scheme_id');

    $scheme = SchemeDetail::where('ID', $schemeId)->first();

    if (!$scheme) {
        return redirect()->back()->with('error', 'Scheme not found');
    }

    // Get Owner Titles
    $ownerDetails = DB::table('owner')
        ->where('scheme_ID', $schemeId)
        ->get();

    // Calculate per title paid + pending
    foreach ($ownerDetails as $row) {

        $paidPerTitle = DB::table('owner_payment_details')
            ->where('owner_id', $row->id ?? $row->ID)
            ->sum('payment_amt');

        $row->paid = $paidPerTitle;
        $row->pending = $row->amt - $paidPerTitle;
    }

    $totalAmount = $ownerDetails->sum('amt');
    $paidAmount = $ownerDetails->sum('paid');
    $pendingAmount = $ownerDetails->sum('pending');

    return view('backend.Owner_pay.create', compact(
        'scheme',
        'ownerDetails',
        'totalAmount',
        'paidAmount',
        'pendingAmount'
    ));
}

    public function store(Request $request)
{
    $request->validate([
        'scheme'      => 'required',
        'Pay_type'    => 'required',
        'amount_pay'  => 'required|numeric|min:0',
    ]);

    DB::transaction(function () use ($request) {

        $paymentId = uniqid();

        $formattedDate = Carbon::createFromFormat('d-m-Y', $request->Date)
                                ->format('Y-m-d');

        $clientId = session('selected_scheme_id');

        // ===============================
        // Insert Into owner_payment table
        // ===============================
        OwnerPayment::create([
            'ID'             => $paymentId,
            'ClientID'       => $clientId,
            'Date'           => $formattedDate,
            'type'           => 'individual',
            'Project_ID'     => $request->scheme,
            'payment_method' => $request->Pay_type,
            'amt_pay'        => $request->amount_pay,
            'cheque_no'      => $request->cheque_no,
            'bankcharge'     => $request->bnk_charge ?? 0,
            'total_pay'      => $request->payable ?? 0,
            'narration'      => $request->narration,
            'account_no'     => $request->account_no,
            'Type_Payment'   => 'owner payment',
            'recive_amt'     => $request->amount_pay,
            'created'     => now(),
            'last_edited'     => now(),
        ]);

        // =================================
        // Insert Into owner_payment_details
        // =================================
        if (!empty($request->details)) {

            foreach ($request->details as $row) {

                if (!empty($row['amount']) && $row['amount'] > 0) {

                    OwnerPaymentDetail::create([
                        'ID'         => uniqid(),
                        'ClientID'   => $clientId,
                        'Date'       => $formattedDate,
                        'owner_id'   => $row['owner_id'],
                        'title'       => $row['title'],
                        'payment_amt'=> $row['amount'],
                        'amount'     => $row['amount'], 
                        'paymentID'  => $paymentId,
                        'Created'    => now(),
                        'LastEdited' => now(),
                    ]);
                }
            }
        }
    });

    return redirect()->route('Owner_Pay')
        ->with('success', 'Record Added Successfully!');
}

    public function edit($id)
{
    $schemeId = session('selected_scheme_id');

    $payment = OwnerPayment::where('ID', $id)->firstOrFail();

    $scheme = SchemeDetail::where('ID', $payment->Project_ID)->first();

    // Get all owners of scheme
    $ownerDetails = DB::table('owner')
        ->where('scheme_ID', $schemeId)
        ->get();

    // Get payment details for this payment
    $paymentDetails = OwnerPaymentDetail::where('paymentID', $id)->get()
                        ->keyBy('owner_id');

    foreach ($ownerDetails as $row) {

    $ownerId = $row->id ?? $row->ID;

    // ✅ Total paid EXCLUDING this payment
    $totalPaid = DB::table('owner_payment_details')
        ->where('owner_id', $ownerId)
        ->where('paymentID', '!=', $id) // 🔥 exclude current payment
        ->sum('payment_amt');

    $row->paid = $totalPaid;

    // ✅ Add back current payment amount for editing
    $currentPaymentAmount = 0;

    if (isset($paymentDetails[$ownerId])) {
        $currentPaymentAmount = $paymentDetails[$ownerId]->payment_amt;
    }

    $row->pending = $row->amt - $totalPaid;

    // If owner exists in this payment
    if ($currentPaymentAmount > 0) {

        $row->selected     = true;
        $row->edit_amount  = $currentPaymentAmount;

        // 🔥 increase pending by current payment amount
        $row->pending += $currentPaymentAmount;

    } else {

        $row->selected     = false;
        $row->edit_amount  = null;
    }
}


    $totalAmount   = $ownerDetails->sum('amt');
    $paidAmount = DB::table('owner_payment_details')
    ->where('paymentID', '!=', $id)
    ->sum('payment_amt');

    $pendingAmount = $totalAmount - $paidAmount;

    return view('backend.Owner_pay.edit', compact(
        'payment',
        'scheme',
        'ownerDetails',
        'totalAmount',
        'paidAmount',
        'pendingAmount'
    ));
}

    public function update(Request $request, $id)
{
    $request->validate([
        'scheme'     => 'required',
        'Pay_type'   => 'required',
        'amount_pay' => 'required|numeric|min:0',
    ]);

    DB::transaction(function () use ($request, $id) {

        $payment = OwnerPayment::findOrFail($id);

        $formattedDate = Carbon::createFromFormat('d-m-Y', $request->Date)
                                ->format('Y-m-d');

        // =======================
        // Update Master
        // =======================
        $payment->update([
            'Date'           => $formattedDate,
            'payment_method' => $request->Pay_type,
            'amt_pay'        => $request->amount_pay,
            'cheque_no'      => $request->cheque_no,
            'bankcharge'     => $request->bnk_charge ?? 0,
            'total_pay'      => $request->payable ?? 0,
            'narration'      => $request->narration,
            'account_no'     => $request->account_no,
            'last_edited'    => now(),
        ]);

        // =======================
        // Delete Old Details
        // =======================
        OwnerPaymentDetail::where('paymentID', $id)->delete();

        // =======================
        // Insert New Details
        // =======================
        foreach ($request->details ?? [] as $row) {

            if (!empty($row['amount']) && $row['amount'] > 0) {

                OwnerPaymentDetail::create([
                    'ID'          => uniqid(),
                    'ClientID'    => session('selected_scheme_id'),
                    'Date'        => $formattedDate,
                    'owner_id'    => $row['owner_id'],
                    'title'       => $row['title'],
                    'payment_amt' => $row['amount'],
                    'amount'      => $row['amount'],
                    'paymentID'   => $id,
                    'Created'     => now(),
                    'LastEdited'  => now(),
                ]);
            }
        }
    });

    return redirect()->route('Owner_Pay')
        ->with('success', 'Payment Updated Successfully!');
}

    public function destroy($id)
{
    $schemeId = session('selected_scheme_id');

    $payment = OwnerPayment::where('ID', $id)
        ->where('ClientID', $schemeId)
        ->first();

    if (!$payment) {
        return redirect()->route('Owner_Pay')
            ->with('error', 'Payment not found or unauthorized access.');
    }

    DB::transaction(function () use ($id) {

        // Delete details first
        OwnerPaymentDetail::where('paymentID', $id)->delete();

        // Delete master
        OwnerPayment::where('ID', $id)->delete();
    });

    return redirect()->route('Owner_Pay')
        ->with('success', 'Payment deleted successfully!');
}


    // AJAX
    public function totals(Request $request)
    {
        $total = OwnerPaymentDetail::where('paymentID', $request->scheme)->sum('amount');
        $paid  = OwnerPayment::where('Project_ID', $request->scheme)->sum('amt_pay');

        return response()->json([
            'total'   => $total,
            'paid'    => $paid,
            'pending' => $total - $paid
        ]);
    }
}
