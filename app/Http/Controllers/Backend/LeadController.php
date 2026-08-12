<?php

namespace App\Http\Controllers\Backend;

use Yajra\DataTables\Facades\DataTables;
use App\Http\Controllers\Controller;
use App\Models\Backend\Lead;
use Illuminate\Http\Request;



class LeadController extends Controller
{
    // Display all leads
    public function index(Request $request)
{
    if ($request->ajax()) {

        $query = Lead::select(['ID', 'name', 'created_at']);

        return DataTables::of($query)

            ->addIndexColumn()

            ->addColumn('date', function ($row) {

                return date('d-m-Y', strtotime($row->created_at));

            })


            ->addColumn('actions', function ($row) {

                $editUrl   = route('leads.edit', $row->ID);
                $deleteUrl = route('leads.destroy', $row->ID);

                $formId = 'delete-form-' . $row->ID;


                $actions = '';


                // Edit Icon
                $actions .= '
                    <a href="'.$editUrl.'" class="me-2 text-primary">
                        <i class="align-middle" data-feather="edit-2"></i>
                    </a>
                ';


                // Delete Icon
                $actions .= '
                    <a href="#" 
                       class="text-danger delete-confirm" 
                       data-id="'.$formId.'">

                        <i class="align-middle" data-feather="trash"></i>

                    </a>


                    <form id="'.$formId.'" 
                          action="'.$deleteUrl.'" 
                          method="POST" 
                          class="d-none">

                        '.csrf_field().'
                        '.method_field('DELETE').'

                    </form>
                ';


                return $actions;

            })


            ->rawColumns(['actions'])

            ->make(true);

    }


    return view('backend.leads.index');
}


    // Show create form
    public function create()
    {
         return view('backend.leads.create');
    }


    // Store lead
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required'
        ]);

        Lead::create([
            'name' => $request->name
        ]);

        return redirect()->route('leads.index')
                         ->with('success', 'Lead Added Successfully');
    }


    // Edit form
    public function edit($ID)
    {
        $lead = Lead::findOrFail($ID);

        return view('backend.leads.create', compact('lead'));
    }


    // Update lead
    public function update(Request $request, $ID)
    {
        $request->validate([
            'name' => 'required'
        ]);

        $lead = Lead::findOrFail($ID);

        $lead->update([
            'name' => $request->name
        ]);

        return redirect()->route('leads.index')
                         ->with('success', 'Lead Updated Successfully');
    }


    // Delete lead
    public function destroy($ID)
    {
        $lead = Lead::findOrFail($ID);

        $lead->delete();

        return redirect()->route('leads.index')
                         ->with('success', 'Lead Deleted Successfully');
    }
}