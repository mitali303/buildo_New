<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Backend\AccountTransfer;
use App\Models\Backend\Bank_Acc;
use App\Models\Backend\SchemeDetail;
use Carbon\Carbon;

class AccountTransferController extends Controller
{
        public function index(Request $request)
    {
        if ($request->ajax()) {

            $fromDate = $request->from_date
                ? Carbon::parse($request->from_date)->startOfDay()
                : Carbon::now()->startOfMonth();

            $toDate = $request->to_date
                ? Carbon::parse($request->to_date)->endOfDay()
                : Carbon::now()->endOfDay();

            $data = AccountTransfer::with(['accountFrom', 'accountTo'])
            ->whereBetween('account_transfer.Date', [$fromDate, $toDate])
            ->select(
                'account_transfer.ID',
                'account_transfer.Date',
                'account_transfer.account_from',
                'account_transfer.account_to',
                'account_transfer.payment_method',
                'account_transfer.balance',
                'account_transfer.amt_pay'
            )
            ->orderBy('account_transfer.Date', 'DESC');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('date', fn($row) => date('d-m-Y', strtotime($row->Date)))
                ->addColumn('account_from_name', fn($row) => $row->accountFrom->Name ?? '')
                ->addColumn('account_to_name', fn($row) => $row->accountTo->Name ?? '')
                ->addColumn('actions', function ($row) {

                    $editUrl   = route('account_transfer.edit', $row->ID);
                    $deleteUrl = route('account_transfer.delete', $row->ID);

                    $actions = '';

                    if (hasPermission('edit_add_bill')) {
                        $actions .= '<a href="'.$editUrl.'" class="text-primary me-2">
                                        <i class="align-middle" data-feather="edit"></i>
                                    </a>';
                    }

                    if (hasPermission('delete_add_bill')) {
                        $actions .= '<form action="'.$deleteUrl.'" method="POST" style="display:inline">
                                        '.csrf_field().'
                                        '.method_field("DELETE").'
                                        <button type="submit" class="btn btn-link text-danger p-0"
                                            onclick="return confirm(\'Are you sure?\')">
                                            <i class="align-middle" data-feather="trash"></i>
                                        </button>
                                    </form>';
                    }

                    return $actions;
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        /** ✅ DEFAULT VALUES FOR FIRST PAGE LOAD */
        return view('backend.account_transfer.index', [
            'fromDate' => $request->from_date 
                ?? Carbon::now()->startOfMonth()->format('Y-m-d'),

            'toDate' => $request->to_date 
                ?? Carbon::now()->format('Y-m-d'),
        ]);
    }


    public function create()
    {   
        //$accounttransfers = AccountTransfer::all();
        $banks = Bank_Acc::where('ClientID', session('selected_scheme_id'))->get();
        return view('backend.account_transfer.create', compact('banks'));
    }
   public function store(Request $request)
    {
        // dd($request);
        $account=$request->validate([
            'account_no'      => 'required|string',
            'account_no_to'   => 'required|string|different:account_no',
            'Date'            => 'required|date',
            'payment_method'  => 'required|string',
            'total_pay'       => 'required|numeric|min:0.01',
            'balance'         => 'nullable|numeric',
            'cheque_no'       => 'nullable|string|required_if:payment_method,cheque,e-payment',
        ]);
        // ✅ HERE (exact place)
        $date = \Carbon\Carbon::parse($request->Date)->format('Y-m-d');
        //dd($account);
        // 🔥 GET ACTUAL ACCOUNT BALANCE
        $availableBalance = getBalanceTransferRefund(
            $request->account_no,
            null,
            $date
        );

        // 🔴 HARD STOP
        if ($request->total_pay > $availableBalance) {
            return back()->withErrors([
                'total_pay' => 'Entered Amount greater than selected Account Balance is not allowed'
            ])->withInput();
        }
        AccountTransfer::create([
            'ID'       => uniqid(),
            'ClientID'       => session('selected_scheme_id'),
            'account_from'   => $request->account_no,
            'account_to'     => $request->account_no_to,
            'balance'        => $request->balance,
            'Date'           => $request->Date,
            'amt_pay'        => $request->total_pay,
            'payment_method' => $request->payment_method,
            'cheque_no'      => $request->cheque_no,
            'reconciliation' => 1,
            'userID'         => auth()->id(),
        ]);

        return redirect()
            ->route('account_transfer')
            ->with('success', 'Account transfer added successfully!');
    }


    public function edit($id)
    {

        $accounttransfers = AccountTransfer::findOrFail($id);
        $banks = Bank_Acc::all();

        return view('backend.account_transfer.create', compact(
            'accounttransfers','banks'
        ));
    }

    public function update(Request $request)
    {
        // Validate request
        $request->validate([
            'ID'              => 'required|exists:account_transfer,ID',
            'account_no'      => 'required|string',
            'account_no_to'   => 'required|string|different:account_no',
            'Date'            => 'required|date',
            'payment_method'  => 'required|string',
            'total_pay'       => 'required|numeric|min:0.01',
            'balance'         => 'nullable|numeric',
            'cheque_no'       => 'nullable|string',
            'reconciliation'  => 'nullable|boolean',
        ]);
        // ✅ HERE ALSO
        $date = \Carbon\Carbon::parse($request->Date)->format('Y-m-d');
        $availableBalance = getBalanceTransferRefund(
            $request->account_no,
            $request->ID,   // 👈 THIS IS IMPORTANT
            $date
        );

        if ((float) $request->total_pay > (float) $availableBalance) {
            return back()->withErrors([
                'total_pay' => 'Entered Amount greater than selected Account Balance is not allowed'
            ])->withInput();
        }
        // Find record using correct key
        $accountTransfer = AccountTransfer::findOrFail($request->ID);

        // Update record
        $accountTransfer->update([
            'ClientID'       => session('selected_scheme_id'),
            'account_from'   => $request->account_no,
            'account_to'     => $request->account_no_to,
            'balance'        => $request->balance,
            'Date'           => $request->Date,
            'amt_pay'        => $request->total_pay,
            'payment_method' => $request->payment_method,
            'cheque_no'      => $request->cheque_no,
           // 'Type_Payment'   => $request->Type_Payment ?? null,
            'reconciliation' => $request->reconciliation ?? 0,
            'userID'         => Auth::id(),
        ]);

        return redirect()
            ->route('account_transfer')
            ->with('success', 'Account transfer updated successfully!');
    }


    public function getAccountBalance(Request $request)
    {
        $balance = getBalanceTransferRefund(
            $request->matid,
            $request->payID ?? '0000',
            now()->format('Y-m-d')
        );

        return response()->json(['balance' => $balance]);
    }

    public function destroy($id)
    {
        $bankform = AccountTransfer::findOrFail($id);
        $bankform->delete();

        return redirect()
            ->route('account_transfer')
            ->with('success', 'Record has been deleted successfully!');
    }

}