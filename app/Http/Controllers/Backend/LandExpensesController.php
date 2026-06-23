<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Backend\Bank_Acc;
use App\Models\Backend\SchemeDetail;
use App\Models\Backend\Booking_Customer;
use App\Models\Backend\LandExpenses;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LandExpensesController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = LandExpenses::where('ClientID', session('selected_scheme_id'))
                ->with(['accountNo', 'schemes'])
                ->when($request->from_date, function ($q) use ($request) {
                    $q->whereDate('Date', '>=', $request->from_date);
                })
                ->when($request->to_date, function ($q) use ($request) {
                    $q->whereDate('Date', '<=', $request->to_date);
                })
                ->select(
                    'ID',
                    'Date',
                    'title',
                    'amt_pay',
                    'Exp_type',
                    'payment_method',
                    'schemeID',
                    'narration'
                )
                ->orderBy('Date', 'DESC');

            return DataTables::of($data)
                ->addIndexColumn()

                ->addColumn('date', function ($row) {
                    return date('d-m-Y', strtotime($row->Date));
                })


                ->addColumn('schemes_name', function ($row) {
                    return $row->schemes->Name ?? '';
                })

                ->addColumn('accounts_name', function ($row) {
                    return $row->accountNo->Name ?? '';
                })

              
                ->addColumn('actions', function ($row) {

                    $actions = '';

                    if (hasPermission('edit_land_expenses')) {
                        $actions .= '<a href="' . route('land_expenses.edit', $row->ID) . '" class="me-2 text-primary">
                                        <i data-feather="edit-2"></i>
                                    </a>';
                    }

                    if (hasPermission('delete_land_expenses')) {
                        $actions .= '<a href="#" class="text-danger delete-confirm" data-id="delete-form-' . $row->ID . '">
                                        <i data-feather="trash"></i>
                                    </a>
                                    <form id="delete-form-' . $row->ID . '" action="' . route('land_expenses.delete', $row->ID) . '" method="POST" class="d-none">
                                        ' . csrf_field() . method_field('DELETE') . '
                                    </form>';
                    }

                    return $actions;
                })

               


                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('backend.land_expenses.index');
    }
    public function create()
    {   
        $clientId = session('selected_scheme_id');
        $banks = Bank_Acc::where('ClientID', $clientId)->get();
        $schemes = SchemeDetail::where('completFalg', 0)
        ->where('ID', $clientId)
        ->get();

        $db_record = new LandExpenses();
        return view('backend.land_expenses.create', compact('banks','schemes','db_record'));
    }

    public function getTitleLandExp(Request $request)
    {
        $titles = LandExpenses::where('Exp_type', $request->exptype)
           // ->groupBy('title')
            ->whereRaw("UPPER(title) != 'OTHER'")
            ->get();

        $html = '<select name="title" id="title"
    class="input-lg form-control chosen-select required"
    data-placeholder="Select">';

        $html .= '<option value="">Select</option>';

        foreach ($titles as $row) {
            $html .= '<option value="' . e($row->title) . '">' . e($row->title) . '</option>';
        }

        $html .= '</select>';

        return response($html);
    }
    public function getLandExpenseOptions(Request $request)
    {
       $payMethod = $request->pay_method;

        if ($payMethod === 'cash') {
            $accounts = Bank_Acc::where('Name', 'Cash In Hand')
                ->where('ClientID', session('selected_scheme_id'))
               
                ->get();
            $selectOption = '';
        } else {
            $accounts = Bank_Acc::where('Name', '!=', 'Cash In Hand')
                ->where('ClientID', session('selected_scheme_id'))
                ->orderBy('Name')
                ->get();
                //print_r($accounts);
            $selectOption = '<option value="">Select</option>';
        }

        $html = '<select id="account_no" name="account_no"
                    class="form-control chosen-select required"
                    onchange="getBalance();">';

        $html .= $selectOption;

        foreach ($accounts as $account) {
            $html .= '<option value="'.$account->ID.'">'
                . $account->Name.' ('.substr($account->ACNo, -3).')'
                . '</option>';
        }

        $html .= '</select>';

        $html .= '
            <script>
                $(".chosen-select").chosen();
            </script>
        ';

        return response($html);
    }
    public function getLandExpenseBalances(Request $request)
    {
        $matId = $request->input('Matid');
        $payId = $request->input('PayID');
        $opening = $request->input('opening', 'no');
        $date = $request->input('Date', now()->format('Y-m-d'));
        

        // Adjust date if opening is yes
        if ($opening === 'yes') {
            $date = now()->subDay()->format('Y-m-d');
        }

       
        $balance = getBalanceTransferRefund($matId, $payId, $date);

        return response()->json(['balance' => $balance]);
    }

    public function store(Request $request)
    {
        // dd($request);
        $account=$request->validate([
            'scheme'      => 'required|string',
            'Pay_type'  => 'required|string',
            'amount_pay'       => 'required|numeric|min:0.01',
            'cheque_no'       => 'required_if:Pay_type,cheque,e-payment|nullable|string',
            'account_no'       => 'required_if:Pay_type,cheque,e-payment|nullable|string',
            'bnk_charge'       => 'nullable|numeric',
            'PaymentId'       => 'nullable|numeric',
            'bFlag'       => 'nullable|numeric',
            'narration'       => 'nullable|string',
            'title'       => 'nullable|string',
            'exptype'       => 'nullable|string',
        ]);

        $balance = (float) getBalanceTransferRefund($request->account_no, null, $request->date);
        $amount = (float) $request->amount_pay;

        if ($amount > $balance) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Amount greater than balance not allowed!');
        }
         $id = uniqid();

     $finalTitle = $request->title;

if ($request->title === 'NEW') {
    $finalTitle = trim($request->Exptitles);

    if (empty($finalTitle)) {
        return back()
            ->withInput()
            ->with('error', 'Please enter new title');
    }
}

    $expense = LandExpenses::create([
        'ID'             => $id,
        'ClientID'       => session('selected_scheme_id'),
        'Date'           => $request->date,
        'amt_pay'        => $request->amount_pay,
        'bankcharge'     => $request->bnk_charge,
        'schemeID'       => $request->scheme,
        'payment_method' => $request->Pay_type,
        'cheque_no'      => $request->cheque_no,
        'account_no'     => $request->account_no,
        'narration'      => $request->narration,
        'title' => $finalTitle,
        'Exp_type'       => $request->exptype,
        'empID'          => $request->empName,
        'month'          => $request->mth,
        'year'           => $request->year,
        'fromdate'       => Carbon::parse($request->fromDate)->format('Y-m-d'),
        'todate'         => Carbon::parse($request->toDate)->format('Y-m-d'),
         'userID'         => auth()->id(),
    ]);

    // If payment type is e-Payment, add bank charges entry
    if ($request->Pay_type === 'e-Payment') {
        LandExpenses::create([
            'ID'             => uniqid(),
            'ClientID'       => session('Client_Id'),
            'Date'           => $request->date,
            'amt_pay'        => $request->bnk_charge,
            'payment_method' => $request->Pay_type,
            'cheque_no'      => $request->cheque_no,
            'account_no'     => $request->account_no,
            'narration'      => $request->narration,
            'title'          => 'Site Expences Payment Bank Charges',
            'schemeID'       => $request->scheme,
            'Exp_type'       => 'E-Payment Bank Charges',
            'PaymentId'      => $id,
            'Type_Payment'   => 'Site Expense(Bank Charges)',
            'bFlag'          => 1,
             'userID'         => auth()->id(),
        ]);
    }

        return redirect()
            ->route('land_expenses')
            ->with('success', 'Record added successfully!');
    }
    public function edit($id)
    {
        $clientId = session('selected_scheme_id');
       $postdated = LandExpenses::where('ClientID', $clientId)
        ->where('ID', $id)
        ->firstOrFail();

        $banks = Bank_Acc::where('ClientID', $clientId)->get();
       // $schemes = SchemeDetail::where('ID', $clientId)->get();
        $schemes = SchemeDetail::where('completFalg', 0)
        ->where('ID', $clientId)
        ->get();
        $db_record = new LandExpenses();
        

        return view('backend.land_expenses.create', compact(
            'postdated','banks','schemes','db_record'
        ));
    }
    public function update(Request $request)
    {
        // Validate request'

        //dd($request);
        $request->validate([
           'scheme'      => 'required|string',
            'Pay_type'  => 'required|string',
            'amount_pay'       => 'required|numeric|min:0.01',
            'cheque_no'       => 'required_if:Pay_type,cheque,e-payment|nullable|string',
            'account_no'       => 'required_if:Pay_type,cheque,e-payment|nullable|string',
            'bnk_charge'       => 'nullable|numeric',
            'PaymentId'       => 'nullable|numeric',
            'bFlag'       => 'nullable|numeric',
            'narration'       => 'nullable|string',
            'title'       => 'nullable|string',
            'exptype'       => 'nullable|string',
        ]);

        // Find record using correct key
        $accountTransfer = LandExpenses::findOrFail($request->ID);

        $finalTitle = $request->title;

        $finalTitle = $request->title;

        if ($request->title === 'NEW') {
            $finalTitle = trim($request->Exptitles);

            if (empty($finalTitle)) {
                return back()
                    ->withInput()
                    ->with('error', 'Please enter new title');
            }
        }


        // Update record
        $accountTransfer->update([
            'ClientID'       => session('selected_scheme_id'),
            'Date'           => $request->date,
            'amt_pay'        => $request->amount_pay,
            'bankcharge'     => $request->bnk_charge,
            'schemeID'       => $request->scheme,
            'payment_method' => $request->Pay_type,
            'cheque_no'      => $request->cheque_no,
            'account_no'     => $request->account_no,
            'narration'      => $request->narration,
            'title' => $finalTitle,
            'Exp_type'       => $request->exptype,
            'empID'          => $request->empName,
            'month'          => $request->mth,
            'year'           => $request->year,
            'reconciliation' => $request->reconciliation ?? 0,
            'fromdate'       => Carbon::parse($request->fromDate)->format('Y-m-d'),
            'todate'         => Carbon::parse($request->toDate)->format('Y-m-d'),
            'userID'         => auth()->id(),
        ]);
         if ($request->Pay_type === 'e-Payment') {
            $accountTransfer->update([
               
                'ClientID'       => session('Client_Id'),
                'Date'           => $request->date,
                'amt_pay'        => $request->bnk_charge,
                'payment_method' => $request->Pay_type,
                'cheque_no'      => $request->cheque_no,
                'account_no'     => $request->account_no,
                'narration'      => $request->narration,
                'title'          => 'Site Expences Payment Bank Charges',
                'schemeID'       => $request->scheme,
                'Exp_type'       => 'E-Payment Bank Charges',
                 'PaymentId' => $request->ID,
                'Type_Payment'   => 'Site Expense(Bank Charges)',
                'bFlag'          => 1,
                'userID'         => auth()->id(),
            ]);
        }

        return redirect()
            ->route('land_expenses')
            ->with('success', 'Record updated successfully!');
    }
     public function destroy($id)
    {
        $bankform = LandExpenses::findOrFail($id);
        $bankform->delete();

        return redirect()
            ->route('land_expenses')
            ->with('success', 'Record has been deleted successfully!');
    }


}