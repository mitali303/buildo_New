<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Backend\CallStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;

class CallStatusController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = CallStatus::select(['id','name','type','status'])->orderByDesc('id');
            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('status', function ($row) {
                    return $row->status == 1
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                })
                ->filterColumn('status', function ($query, $keyword) {

                    if ($keyword === '1') {
                        $query->where('status', 1);
                    }

                    if ($keyword === '0') {
                        $query->where('status', 0);
                    }

                })
                ->addColumn('actions', function ($row) {
                    $editUrl = route('CallStatus.edit', $row->id);
                    $deleteUrl = route('CallStatus.delete', $row->id);
                    $formId = 'delete-form-' . $row->id;
                    $actions = '';

                                $actions .= '<a href="' . $editUrl . '" class="me-2 text-primary">
                                        <i class="align-middle" data-feather="edit-2"></i>
                                     </a>';
                                     
                                      $actions .= '<a href="#" class="text-danger delete-confirm" data-id="' . $formId . '">
                                        <i class="align-middle" data-feather="trash"></i>
                                     </a>
                                     <form id="' . $formId . '" action="' . $deleteUrl . '" method="POST" class="d-none">
                                        ' . csrf_field() . '
                                        ' . method_field('DELETE') . '
                                     </form>';
                    // if (hasPermission('edit_CallStatus')) {
                    //     $actions .= '<a href="' . $editUrl . '" class="me-2 text-primary">
                    //                     <i class="align-middle" data-feather="edit-2"></i>
                    //                  </a>';
                    // }

                    // if (hasPermission('delete_CallStatus')) {
                    //     $actions .= '<a href="#" class="text-danger delete-confirm" data-id="' . $formId . '">
                    //                     <i class="align-middle" data-feather="trash"></i>
                    //                  </a>
                    //                  <form id="' . $formId . '" action="' . $deleteUrl . '" method="POST" class="d-none">
                    //                     ' . csrf_field() . '
                    //                     ' . method_field('DELETE') . '
                    //                  </form>';
                    // }

                    return $actions;
                })
                ->rawColumns(['status','actions'])
                ->make(true);
        }

        return view('backend.CallStatus.index');
    }


     /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        return view('backend.CallStatus.create');
    }

     /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       $validated = $request->validate([
            'name' => [
                'required',
                'max:255',
                'regex:/^[a-zA-Z\s]+$/',
                Rule::unique('call_status')->where(function ($query) use ($request) {
                    return $query->whereRaw('LOWER(name) = ?', [strtolower($request->name)]);
                })
            ],
            'type' => 'required|string',
            'status' => 'required|in:0,1'
        ]);

        $data = new CallStatus;
        $data->name = $validated['name'];
        $data->type = $validated['type'];
        $data->status = $validated['status'];
        $data->createdby = Auth::id();
        $data->save(); // ✅ Save the rack

        return redirect()->route('CallStatus')->with('success', 'Call Status added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
       $old = CallStatus::find($id);
        return view('backend.CallStatus.create', compact('old'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $id = $request->id;
        $data = CallStatus::find($id);

        $validated = $request->validate([
            'name'      => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-]+$/',
            'type' => 'required|string',
            'status' => 'required|in:0,1'
        ]);

        $data->name = $validated['name'];
        $data->type = $validated['type'];
        $data->status = $validated['status'];
        $data->save(); // ✅ Save the rack

        return redirect()->route('CallStatus')->with('success', 'Call Status updated successfully!');
    }

     /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = CallStatus::findOrFail($id);
        $data->delete();
        return redirect()->route('CallStatus')->with('success', 'Call Status deleted successfully!');
    }
}
