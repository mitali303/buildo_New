<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class EnquiryController extends Controller
{

    public function index(Request $request)
    {
        if ($request->ajax()) {

            $query = DB::table('leads')
                ->select(
                    'id',
                    'created_at',
                    'lead_name',
                    'mobile_1',
                    'email_1',
                    'address',
                    'description',
                    'status',
                    'generated_by'
                )
                ->latest('created_at');

            // Supervisor (Role 2) ला फक्त स्वतःच्याच entries दिसाव्यात
            if (Auth::user()->isSupervisor()) {
                $query->where('generated_by', Auth::user()->ID);
            }

            $data = $query;

            return DataTables::of($data)

                ->addIndexColumn()

                ->editColumn('status', function ($row) {
                    return $row->status == 1
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-secondary">Inactive</span>';
                })

                ->addColumn('action', function ($row) {

                    return '

                    <a href="tel:'.$row->mobile_1.'"
                        class="btn btn-success btn-sm">
                        <i data-feather="phone"></i>
                    </a>

                    <a href="'.route('enquiries.edit', $row->id).'"
                        class="btn btn-primary btn-sm">
                        <i data-feather="edit"></i>
                    </a>

                    <button
                        class="btn btn-danger btn-sm deleteBtn"
                        data-id="'.$row->id.'">

                        <i data-feather="trash"></i>

                    </button>

                    ';
                })

                ->editColumn('created_at', function ($row) {
                    return date('d-m-Y', strtotime($row->created_at));
                })

                ->rawColumns(['action', 'status'])

                ->make(true);
        }

        return view('backend.enquiries.index');
    }

    public function create()
    {
        $users = User::all();

        return view('backend.enquiries.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required',
            'phone_no'      => 'required',
            'email'         => 'nullable|email',
            'address'       => 'nullable',
            'queries'       => 'required',
            'status'        => 'required',
            'userID'        => 'required'
        ]);

        DB::table('leads')->insert([
            'company_name'   => $request->company_name ?? '',
            'lead_name'      => $request->customer_name,
            'mobile_1'       => $request->phone_no,
            'mobile_2'       => $request->mobile_no,
            'email_1'        => $request->email,
            'address'        => $request->address,
            'description'    => $request->queries,
            'scheme_id'      => $request->scheme_id,
            'status'         => $request->status,
            'assign_to'      => $request->userID,
            'lead_stage'     => 1,
            'country'        => 'India',
            'generated_by'   => Auth::user()->ID,   // 👈 कोणी बनवली ते save
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return redirect()
            ->route('enquiries.index')
            ->with('success', 'Enquiry Added Successfully');
    }

    public function edit($id)
    {
        $enquiry = DB::table('leads')
            ->select(
                'id as ID',
                'lead_name as customer_name',
                'mobile_1 as phone_no',
                'mobile_2 as mobile_no',
                'email_1 as email',
                'address',
                'description as queries',
                'status',
                'assign_to as userID',
                'scheme_id',
                'generated_by'
            )
            ->where('id', $id)
            ->first();

        // Supervisor दुसऱ्याची entry उघडायचा प्रयत्न करत असेल तर ब्लॉक
        if (Auth::user()->isSupervisor()
            && $enquiry
            && $enquiry->generated_by != Auth::user()->ID) {
            abort(403, 'You are not authorized to access this enquiry.');
        }

        $users = User::all();

        return view('backend.enquiries.create', compact('enquiry', 'users'));
    }

    public function update(Request $request, $id)
    {
        $enquiry = DB::table('leads')->where('id', $id)->first();

        if (Auth::user()->isSupervisor()
            && $enquiry
            && $enquiry->generated_by != Auth::user()->ID) {
            abort(403, 'You are not authorized to update this enquiry.');
        }

        DB::table('leads')->where('id', $id)->update([
            'lead_name'   => $request->customer_name,
            'mobile_1'    => $request->phone_no,
            'mobile_2'    => $request->mobile_no,
            'email_1'     => $request->email,
            'address'     => $request->address,
            'description' => $request->queries,
            'scheme_id'   => $request->scheme_id,
            'status'      => $request->status,
            'assign_to'   => $request->userID,
            'updated_at'  => now(),
        ]);

        return redirect()->route('enquiries.index')->with('success', 'Updated Successfully');
    }

    public function destroy($id)
    {
        $enquiry = DB::table('leads')->where('id', $id)->first();

        if (Auth::user()->isSupervisor()
            && $enquiry
            && $enquiry->generated_by != Auth::user()->ID) {
            return response()->json([
                'status'  => false,
                'message' => 'You are not authorized to delete this enquiry.'
            ], 403);
        }

        DB::table('leads')
            ->where('id', $id)
            ->delete();

        return response()->json([
            'status' => true
        ]);
    }
}