<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Backend\MaterialRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class MaterialRequestController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = MaterialRequest::orderBy('Created', 'desc');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('Date', function ($row) {
                    return $row->Date ? Carbon::parse($row->Date)->format('d-m-Y') : '-';
                })
                ->addColumn('Created', function ($row) {
                    return $row->Created ? Carbon::parse($row->Created)->format('d-m-Y H:i:s') : '-';
                })
                ->addColumn('LastEdited', function ($row) {
                    return $row->LastEdited ? Carbon::parse($row->LastEdited)->format('d-m-Y H:i:s') : '-';
                })
                ->addColumn('actions', function ($row) {
                    $editUrl = route('material_request.edit', $row->ID);
                    $deleteUrl = route('material_request.destroy', $row->ID);
                    $formId = 'material-request-delete-' . $row->ID;

                    $html = '<div class="d-flex gap-2">';
                    $html .= '<a href="' . $editUrl . '" class="text-primary" title="Edit"><i data-feather="edit-2"></i></a>';
                    $html .= '<a href="#" class="text-danger delete-confirm" data-id="' . $formId . '" title="Delete"><i data-feather="trash"></i></a>';
                    $html .= '<form id="' . $formId . '" action="' . $deleteUrl . '" method="POST" class="d-none">' . csrf_field() . method_field('DELETE') . '</form>';
                    $html .= '</div>';

                    return $html;
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('backend.MaterialRequest.index');
    }

    public function create()
    {
        $materialRequest = null;

        return view('backend.MaterialRequest.create', compact('materialRequest'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'request_no' => 'required|string',
            'description' => 'nullable|string',
            'remark' => 'nullable|string',
        ]);

        $id = $request->input('id') ?? uniqid();

        MaterialRequest::updateOrCreate(
            ['ID' => $id],
            [
                'Date' => $validated['date'],
                'request_no' => $validated['request_no'],
                'ClientID' => session('selected_scheme_id', ''),
                'description' => $validated['description'] ?? '',
                'remark' => $validated['remark'] ?? '',
                'userID' => auth()->id(),
                'Created' => now(),
                'LastEdited' => now(),
            ]
        );

        return redirect()->route('material_request.list')
            ->with('success', 'Material request saved successfully.');
    }

    public function edit($id)
    {
        $materialRequest = MaterialRequest::where('ID', $id)->firstOrFail();

        return view('backend.MaterialRequest.create', compact('materialRequest'));
    }

    public function update(Request $request, $id)
    {
        $request->merge(['id' => $id]);

        return $this->store($request);
    }

    public function destroy($id)
    {
        MaterialRequest::where('ID', $id)->delete();

        return redirect()->route('material_request.list')
            ->with('success', 'Material request deleted successfully.');
    }
}
