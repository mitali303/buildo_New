<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\Shift;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class ShiftController extends Controller
{
    public function index(Request $request)
{
    if ($request->ajax()) {


        $query = Shift::select(['id','shift','shift_intime','shift_outtime'])
                    ->orderByDesc('id');

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('status', function ($row) {
                return $row->status == 1
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';
            })
            ->addColumn('actions', function ($row) {
                $editUrl = route('Shift.edit', $row->id);
                $deleteUrl = route('Shift.delete', $row->id);
                $formId = 'delete-form-' . $row->id;
                $actions = '';

                if (hasPermission('edit_Shift')) {
                    $actions .= '<a href="' . $editUrl . '" class="me-2 text-primary">
                                    <i data-feather="edit-2"></i>
                                 </a>';
                }

                if (hasPermission('delete_Shift')) {
                    $actions .= '<a href="#" class="text-danger delete-confirm" data-id="' . $formId . '">
                                    <i data-feather="trash"></i>
                                 </a>
                                 <form id="' . $formId . '" action="' . $deleteUrl . '" method="POST" class="d-none">
                                    ' . csrf_field() . '
                                    ' . method_field('DELETE') . '
                                 </form>';
                }

                return $actions;
            })
            ->rawColumns(['status','actions'])
            ->make(true);
    }

    return view('backend.Shift.index');
}



       /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        return view('backend.Shift.create');
    }

     /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       $validated = $request->validate([
          'shift' => 'required|string|max:255|regex:/^[A-Za-z ]+$/',
          'shift_intime'  => 'required',
          'shift_outtime' => 'required',
        ]);

        $data = new Shift;

        $data->shift = $validated['shift'];
        $data->shift_intime = $validated['shift_intime'];
        $data->shift_outtime = $validated['shift_outtime'];
        $data->createdby = Auth::id();

        $data->save(); // ✅ Save the rack

        return redirect()->route('Shift')->with('success', 'Shift added successfully!');
    }

      /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
       $old = Shift::find($id);
        return view('backend.Shift.create', compact('old'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $id = $request->id;
        $data = Shift::find($id);

        $validated = $request->validate([
            'shift'      => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
           'shift_intime'  => 'required',
          'shift_outtime' => 'required',
        ]);

        $data->shift = $validated['shift'];
        $data->shift_intime = $validated['shift_intime'];
        $data->shift_outtime = $validated['shift_outtime'];
        $data->save(); // ✅ Save the rack

        return redirect()->route('Shift')->with('success', 'Shift updated successfully!');
    }

     /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Shift::findOrFail($id);
        $data->delete();
        return redirect()->route('Shift')->with('success', 'Shift deleted successfully!');
    }


}
