<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Backend\LeadSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;

class LeadSourceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $query = LeadSource::select(['id', 'name', 'status'])
                ->orderByDesc('id');

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

                    $editUrl = route('LeadSource.edit', $row->id);
                    $deleteUrl = route('LeadSource.delete', $row->id);

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
                    // if (hasPermission('edit_LeadSource')) {
                    //     $actions .= '<a href="' . $editUrl . '" class="me-2 text-primary">
                    //                     <i class="align-middle" data-feather="edit-2"></i>
                    //                  </a>';
                    // }

                    // if (hasPermission('delete_LeadSource')) {
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

                ->rawColumns(['status', 'actions'])
                ->make(true);
        }

        return view('backend.LeadSource.index');
    }

    /**
     * Show Create Form
     */
    public function create()
    {
        return view('backend.LeadSource.create');
    }

    /**
     * Store
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-]+$/',
                Rule::unique('lead_source')->where(function ($query) use ($request) {
                    return $query->whereRaw('LOWER(name) = ?', [strtolower($request->name)]);
                }),
            ],
            'status' => 'required|in:0,1',
        ]);

        $data = new LeadSource();

        $data->name = $validated['name'];
        $data->status = $validated['status'];
        $data->createdby = Auth::id();

        $data->save();

        return redirect()
            ->route('LeadSource')
            ->with('success', 'Lead Source added successfully!');
    }

    /**
     * Display
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Edit
     */
    public function edit(string $id)
    {
        $old = LeadSource::findOrFail($id);

        return view('backend.LeadSource.create', compact('old'));
    }

    /**
     * Update
     */
    public function update(Request $request)
    {
        $id = $request->id;

        $data = LeadSource::findOrFail($id);

        $validated = $request->validate([
            'name' => [
                'required',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-]+$/',
                Rule::unique('lead_source')
                    ->ignore($id)
                    ->where(function ($query) use ($request) {
                        return $query->whereRaw('LOWER(name) = ?', [strtolower($request->name)]);
                    }),
            ],
            'status' => 'required|in:0,1',
        ]);

        $data->name = $validated['name'];
        $data->status = $validated['status'];

        $data->save();

        return redirect()
            ->route('LeadSource')
            ->with('success', 'Lead Source updated successfully!');
    }

    /**
     * Delete
     */
    public function destroy(string $id)
    {
        $data = LeadSource::findOrFail($id);

        $data->delete();

        return redirect()
            ->route('LeadSource')
            ->with('success', 'Lead Source deleted successfully!');
    }
}