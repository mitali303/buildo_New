<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\Enquiry;
use Yajra\DataTables\Facades\DataTables;

class EnquiryController extends Controller
{
    public function index(Request $request)
    {
        if($request->ajax())
        {
            $data = Enquiry::latest('Created');

            return DataTables::of($data)

                ->addIndexColumn()
                
                ->addColumn('action', function ($row) {

                        return '

                        <a href="tel:'.$row->phone_no.'"
                            class="btn btn-success btn-sm">
                            <i data-feather="phone"></i>
                        </a>

                        <a href="'.route('enquiries.edit',$row->ID).'"
                            class="btn btn-primary btn-sm">
                            <i data-feather="edit"></i>
                        </a>

                        <button
                            class="btn btn-danger btn-sm deleteBtn"
                            data-id="'.$row->ID.'">

                            <i data-feather="trash"></i>

                        </button>

                        ';
                    })

                ->editColumn('Created',function($row){

                    return date('d-m-Y',strtotime($row->Created));

                })

                ->rawColumns(['action'])

                ->make(true);
        }

        return view('backend.enquiries.index');
    }

    public function edit($id)
    {
        $enquiry = Enquiry::findOrFail($id);

        return view(
            'backend.enquiries.edit',
            compact('enquiry')
        );
    }

    public function update(Request $request,$id)
    {
        $enquiry = Enquiry::findOrFail($id);

        $enquiry->update([
            'customer_name'=>$request->customer_name,
            'email'=>$request->email,
            'phone_no'=>$request->phone_no,
            'address'=>$request->address,
            'queries'=>$request->queries,
            'status'=>$request->status
        ]);

        return redirect()
            ->route('enquiries.index')
            ->with('success','Updated Successfully');
    }

    public function destroy($id)
    {
        Enquiry::findOrFail($id)->delete();

        return response()->json([
            'status'=>true
        ]);
    }
}