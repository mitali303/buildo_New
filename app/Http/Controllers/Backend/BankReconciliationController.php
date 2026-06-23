<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class BankReconciliationController extends Controller
{
   public function index(Request $request)
    {
        if ($request->ajax()) {

            $clientId = session('selected_scheme_id');

            /* =========================
               ALL UNION QUERIES
            ==========================*/

            $queries = [

                DB::table('loan')->selectRaw("
                    ID, Date, amt_pay as amount, cheque_no, account_no,
                    payment_method, 'loan' as table_name,
                    Type_Payment as type, customer, reconciliation
                ")->where([
                    ['ClientID', $clientId],
                    ['payment_method', 'cheque'],
                    ['paytype', 'Received'],
                    ['reconciliation', 0]
                ]),

                DB::table('booking_payment')->selectRaw("
                    ID, Date, amt_pay as amount, cheque_no, account_no,
                    payment_method, 'booking_payment' as table_name,
                    'Booking Payment' as type, Booking_ID as customer, reconciliation
                ")->where([
                    ['ClientID', $clientId],
                    ['payment_method', 'cheque'],
                    ['reconciliation', 0]
                ]),

                DB::table('partners_loan')->selectRaw("
                    ID, Date, amt_pay as amount, cheque_no, account_no,
                    payment_method, 'partners_loan' as table_name,
                    Type_Payment as type, partners as customer, reconciliation
                ")->where([
                    ['ClientID', $clientId],
                    ['payment_method', 'cheque'],
                    // ['paytype', 'Received'],
                    ['reconciliation', 0]
                ]),

                DB::table('project_payment')->selectRaw("
                    ID, Date, amt_pay as amount, cheque_no, account_no,
                    payment_method, 'project_payment' as table_name,
                    Type_Payment as type, projectID as customer, reconciliation
                ")->where([
                    ['ClientID', $clientId],
                    ['payment_method', 'cheque'],
                    ['reconciliation', 0]
                ]),

                DB::table('income_payment')->selectRaw("
                    ID, Date, amt_pay as amount, cheque_no, account_no,
                    payment_method, 'income_payment' as table_name,
                    Type_Payment as type, conID as customer, reconciliation
                ")->where([
                    ['ClientID', $clientId],
                    ['payment_method', 'cheque'],
                    ['reconciliation', 0]
                ]),

                DB::table('daily_trans')->selectRaw("
                    ID, Date, amt_pay as amount, cheque_no, account_no,
                    payment_method, 'daily_trans' as table_name,
                    Type_Payment as type, empID as customer, reconciliation
                ")->where([
                    ['ClientID', $clientId],
                    ['payment_method', 'cheque'],
                    ['reconciliation', 0]
                ]),

                DB::table('site_expences')->selectRaw("
                    ID, Date, amt_pay as amount, cheque_no, account_no,
                    payment_method, 'site_expences' as table_name,
                    Type_Payment as type, empID as customer, reconciliation
                ")->where([
                    ['ClientID', $clientId],
                    ['payment_method', 'cheque'],
                    ['reconciliation', 0]
                ]),

                DB::table('stampotherexpenses')->selectRaw("
                    ID, Date, amt_pay as amount, cheque_no, account_no,
                    payment_method, 'stampotherexpenses' as table_name,
                    Type_Payment as type, empID as customer, reconciliation
                ")->where([
                    ['ClientID', $clientId],
                    ['payment_method', 'cheque'],
                    ['reconciliation', 0]
                ]),

                DB::table('land')->selectRaw("
                    ID, Date, amt_pay as amount, cheque_no, account_no,
                    payment_method, 'land' as table_name,
                    Type_Payment as type, empID as customer, reconciliation
                ")->where([
                    ['ClientID', $clientId],
                    ['payment_method', 'cheque'],
                    ['reconciliation', 0]
                ]),

                DB::table('inv_payment')->selectRaw("
                    ID, payment_date as Date, amt_pay as amount, cheque_no, account_no,
                    payment_method, 'inv_payment' as table_name,
                    Type_Payment as type, PurchaseFrom as customer, reconciliation
                ")->where([
                    ['ClientID', $clientId],
                    ['payment_method', 'cheque'],
                    ['reconciliation', 0]
                ]),

                DB::table('workorder_payment')->selectRaw("
                    ID, Date, amt_pay as amount, cheque_no, account_no,
                    payment_method, 'workorder_payment' as table_name,
                    Type_Payment as type, conID as customer, reconciliation
                ")->where([
                    ['ClientID', $clientId],
                    ['payment_method', 'cheque'],
                    ['reconciliation', 0]
                ]),

                DB::table('tds_payment')->selectRaw("
                    ID, payment_date as Date, amt_pay as amount, cheque_no, account_no,
                    payment_method, 'tds_payment' as table_name,
                    Type_Payment as type, conID as customer, reconciliation
                ")->where([
                    ['ClientID', $clientId],
                    ['payment_method', 'cheque'],
                    ['reconciliation', 0]
                ]),

                DB::table('customer_refund')->selectRaw("
                    ID, Date, amt_pay as amount, cheque_no, account_no,
                    payment_method, 'customer_refund' as table_name,
                    paytype as type, bookingcustomer as customer, reconciliation
                ")->where([
                    ['ClientID', $clientId],
                    ['payment_method', 'cheque'],
                    ['reconciliation', 0]
                ]),

                DB::table('booking_cancel')->selectRaw("
                    ID, Date, amt_pay as amount, cheque_no, account_no,
                    payment_method, 'booking_cancel' as table_name,
                    Type_Payment as type, BookingID as customer, reconciliation
                ")->where([
                    ['ClientID', $clientId],
                    ['payment_method', 'cheque'],
                    ['reconciliation', 0]
                ]),
                
                DB::table('owner_payment')->selectRaw("
                    ID, Date, amt_pay as amount, cheque_no, account_no,
                    payment_method, 'owner_payment' as table_name,
                    Type_Payment as type, Project_ID as customer, reconciliation
                ")->where([
                    ['ClientID', $clientId],
                    ['payment_method', 'cheque'],
                    ['reconciliation', 0]
                ]),

                DB::table('labour_payment')->selectRaw("
                    ID, Date, amt_pay as amount, cheque_no, account_no,
                    payment_method, 'labour_payment' as table_name,
                    'labour payment' as type, NULL as customer, reconciliation
                ")->where([
                    ['ClientID', $clientId],
                    ['payment_method', 'cheque'],
                    ['reconciliation', 0]
                ]),

                //payroll advance and salary

                DB::table('employee_advance')->selectRaw("
                    id, Date, advance as amount, cheque_no, account_no,
                    payment_method, 'employee_advance' as table_name,
                    'employee advance payment' as type, NULL as customer, reconciliation
                ")->where([
                    ['payment_method', 'cheque'],
                    ['reconciliation', 0]
                ]),

                DB::table('salarymaster')->selectRaw("
                    id, Date, amount as amount, cheque_no, account_no,
                    payment_method, 'salarymaster' as table_name,
                    'salary payment' as type, NULL as customer, reconciliation
                ")->where([
                    ['payment_method', 'cheque'],
                    ['reconciliation', 0]
                ]),
            ];

            $finalQuery = array_shift($queries);
            foreach ($queries as $query) {
                $finalQuery->unionAll($query);
            }
            
            $finalQuery = DB::query()->fromSub($finalQuery, 't')
            ->orderBy('Date', 'desc');

            return DataTables::of($finalQuery)
                ->filter(function ($query) use ($request) {

                    $search = $request->input('search.value');

                    if (!empty($search)) {

                        $query->where(function ($q) use ($search) {

                            // normal columns
                            $q->where('t.cheque_no', 'like', "%{$search}%")
                                ->orWhere('t.type', 'like', "%{$search}%")
                                ->orWhere('t.amount', 'like', "%{$search}%");

                                $q->orWhereIn('t.account_no', function ($sub) use ($search) {
                                    $sub->select('ID')
                                        ->from('accounts')
                                        ->where('Name', 'like', "%{$search}%");
                                });


                            // ✅ CUSTOMER NAME SEARCH (SUBQUERY)
                            $q->orWhereIn('t.customer', function ($sub) use ($search) {
                                $sub->select('ID')
                                    ->from('partners')
                                    ->where('Name', 'like', "%{$search}%");
                            });

                            // booking customer
                            $q->orWhereIn('t.customer', function ($sub) use ($search) {
                                $sub->select('ID')
                                    ->from('booking_customer')
                                    ->where('CutomerName', 'like', "%{$search}%");
                            });

                            // vendor
                            $q->orWhereIn('t.customer', function ($sub) use ($search) {
                                $sub->select('ID')
                                    ->from('vendor')
                                    ->where('Name', 'like', "%{$search}%");
                            });

                            // staff
                            $q->orWhereIn('t.customer', function ($sub) use ($search) {
                                $sub->select('ID')
                                    ->from('staff')
                                    ->where('Name', 'like', "%{$search}%");
                            });

                        });
                    }
                })
                ->addIndexColumn()
                ->editColumn('Date', function ($row) {
                            return \Carbon\Carbon::parse($row->Date)->format('d-m-Y');
                        })
                ->addColumn('bank_name', function ($row) {
                    return DB::table('accounts')->where('ID', $row->account_no)->value('Name');
                })
                ->addColumn('customer_name', function ($row) {
                    return $this->resolveCustomerName($row);
                })
                ->addColumn('actions', function ($row) {
                    return '
                        <button class="btn btn-success btn-sm clear-cheque"
                            data-id="'.$row->ID.'" data-table="'.$row->table_name.'">
                            ✔
                        </button>
                        <button class="btn btn-danger btn-sm bounce-cheque"
                            data-id="'.$row->ID.'" data-table="'.$row->table_name.'">
                            ✖
                        </button>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('backend.bank_reconciliation.index');
    }

    private function resolveCustomerName($row)
    {
        return match ($row->table_name) {
            'booking_payment', 'customer_refund', 'booking_cancel'
                => DB::table('booking_customer')->where('ID', $row->customer)->value('CutomerName'),

            'loan', 'partners_loan'
                => DB::table('partners')->where('ID', $row->customer)->value('Name'),

            'workorder_payment', 'income_payment'
                => DB::table('vendor')->where('ID', $row->customer)->value('Name'),

            'site_expences', 'stampotherexpenses'
                => DB::table('staff')->where('ID', $row->customer)->value('Name'),

            default => '-'
        };
    }

    public function clearCheque(Request $request)
    {
        DB::table($request->table)
            ->where('ID', $request->id)
            ->update(['reconciliation' => 1]);

        return response()->json(['status' => 'success']);
    }

    public function bounceCheque(Request $request)
    {
        DB::table($request->table)
            ->where('ID', $request->id)
            ->update([
                'reconciliation' => 2,
                'reject_reason'  => $request->reason
            ]);

        return response()->json(['status' => 'success']);
    }

}