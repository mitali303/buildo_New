<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Backend\Role;
use App\Models\Backend\Permission;
use App\Models\Backend\Supplier_contractor;
use Illuminate\Support\Facades\Auth;


class Supplier_contractorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Supplier_contractor::select(['ID', 'Name', 'Address', 'Email', 'ContactPerson', 'ContactNumber', 'Created']);
            return DataTables::of($query)
                ->addIndexColumn()

               ->addColumn('actions', function ($row) {
                            $editUrl = route('SupplierContractor.edit', $row->ID);
                            $deleteUrl = route('SupplierContractor.delete', $row->ID);
                            $formId = 'delete-form-' . $row->ID;

                            $actions = '';

                            if (hasPermission('edit_supplier_contractor')) {
                                $actions .= '<a href="' . $editUrl . '" class="me-2 text-primary">
                    <i class="fas fa-edit"></i>
                </a>';
                            }

                            if (hasPermission('delete_supplier_contractor')) {
                                $actions .=  '<a href="#" class="text-danger delete-confirm" data-id="' . $formId . '">
                    <i class="fas fa-trash"></i>
                </a>
                                            <form id="' . $formId . '" action="' . $deleteUrl . '" method="POST" class="d-none">
                                                ' . csrf_field() . '
                                                ' . method_field('DELETE') . '
                                            </form>';
                            }

                            return $actions;
                        })
                        ->rawColumns(['actions'])
                ->make(true);
        }

        return view('backend.supplier_contractor.index');
    }

    /**
     * Show the form for creating a new resource. 
     */
    public function create()
    {
        $permissions = Permission::all();
        return view('backend.supplier_contractor.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
           'name' => [
        'required',
        'string',
        'max:255',
        'regex:/^[A-Za-z\s]+$/',
                Rule::unique('vendor', 'Name')
                    ->where(function ($query) use ($request) {
                        return $query->where('Type', $request->type);
                    }),],
            'type' => ['required', 'in:VENDOR,CONTRACTOR'],
            'address' => ['required', 'string', 'max:255'],
            // 'fax' => [
            //     'required',
            //     function ($attribute, $value, $fail) {
            //         $digits = preg_replace('/\D/', '', $value); // फक्त digits काढतो
            //         if (strlen($digits) < 7 || strlen($digits) > 15) {
            //             $fail('Fax number must be between 7 to 15 digits.');
            //         }
            //     },
            // ],
            'contact_person' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s]+$/'],
            'mobile_number' => ['required', 'regex:/^[0-9]{10}$/', 'unique:vendor,ContactNumber'],
            'email' => ['nullable', 'email:rfc,dns', 'max:255', 'regex:/^[a-z0-9._%+-]+@(gmail|yahoo)\.com$/'],
            'landline' => ['nullable', 'regex:/^(\d{3,5}[-\s]?)?\d{6,8}$/'],
            'pan_no' => ['nullable', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/i'],
            'gst_no' => ['nullable', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i'],
            'retention' => ['nullable', 'required_if:type,CONTRACTOR', 'numeric', 'min:0', 'max:100'],
        ], [
            'name.unique' => 'This name already exists. Please use a different name.',
            'type.required' => 'Type is required.',
            'type.in' => 'Type must be VENDOR or CONTRACTOR.',
            'address.required' => 'Address is required.',
            'fax.required' => 'Fax number is required.',
            'fax.digits_between' => 'Fax number must be between 7 to 15 digits.',
            'pan_no.regex' => 'Please enter a valid PAN number (e.g., ABCDE1234F).',
            'contact_person.required' => 'Contact Person is required.',
            'mobile_number.required' => 'Mobile Number is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'retention.required_if' => 'Retention % is required for CONTRACTOR.',
            'retention.numeric' => 'Retention % must be numeric.',
            'gst_no.regex' => 'Please enter a valid GST number (e.g., 22ABCDE1234F1Z5).',
            'retention.max' => 'Contractor Retention % cannot be greater than 100%.',
        ]);

        $supplier = new Supplier_contractor();
        $supplier->Created = now();
        $supplier->LastEdited = now();
        $supplier->Name = $validated['name'];
        $supplier->Type = $validated['type'];
        $supplier->Address = $validated['address'];
        $supplier->Email = $validated['email'] ?? null;
        // $supplier->Fax = $validated['fax'];
        $supplier->ContactPerson = $validated['contact_person'];
        $supplier->ContactNumber = $validated['mobile_number'];
        $supplier->Landline = $validated['landline'] ?? null;
        $supplier->pan_no = $validated['pan_no'] ?? null;
        $supplier->GST_no = $validated['gst_no'] ?? null;
        $supplier->retain_per = $request->filled('retention')
            ? (int) $validated['retention']
            : null;
        $supplier->ClientID = session('selected_scheme_id');
        $supplier->userID = Auth::id();

        $supplier->save();

        return redirect()->route('SupplierContractor')
            ->with('success', 'Record has been added successfully!');
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
    public function edit($id)
    {
        $supplier = Supplier_contractor::findOrFail($id);
        return view('backend.supplier_contractor.create', compact('supplier'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[^0-9]*$/'],
            'type' => ['required', 'in:VENDOR,CONTRACTOR'],
            'address' => ['required', 'string', 'max:255'],
            // 'fax' => [
            //     'required',
            //     function ($attribute, $value, $fail) {
            //         $digits = preg_replace('/\D/', '', $value); // फक्त digits काढतो
            //         if (strlen($digits) < 7 || strlen($digits) > 15) {
            //             $fail('Fax number must be between 7 to 15 digits.');
            //         }
            //     },
            // ],
            'contact_person' => ['required', 'string', 'max:255', 'regex:/^[^0-9]*$/'],
            'mobile_number' => ['required', 'regex:/^[0-9]{10}$/', 'unique:vendor,ContactNumber,' . $request->id . ',id'],
            'email' => ['nullable', 'email:rfc,dns', 'max:255', 'regex:/^[a-z0-9._%+-]+@(gmail|yahoo)\.com$/'],
            'landline' => ['nullable', 'regex:/^(\d{3,5}[-\s]?)?\d{6,8}$/'],
            'pan_no' => ['nullable', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/i'],
            'gst_no' => ['nullable', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i'],
            'retention' => ['nullable', 'required_if:type,CONTRACTOR', 'numeric', 'min:0', 'max:100'],
        ], [
            'name.required' => 'Name is required.',
            'type.required' => 'Type is required.',
            'type.in' => 'Type must be SUPPLIER or CONTRACTOR.',
            'address.required' => 'Address is required.',
            // 'fax.required' => 'Fax number is required.',
            // 'fax.digits_between' => 'Fax number must be between 7 to 15 digits.',
            'pan_no.regex' => 'Please enter a valid PAN number (e.g., ABCDE1234F).',
            'contact_person.required' => 'Contact Person is required.',
            'mobile_number.required' => 'Mobile Number is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'retention.required_if' => 'Retention % is required for CONTRACTOR.',
            'retention.numeric' => 'Retention % must be numeric.',
            'gst_no.regex' => 'Please enter a valid GST number (e.g., 22ABCDE1234F1Z5).',
            'retention.max' => 'Contractor Retention % cannot be greater than 100%.',
        ]);

        $supplier = Supplier_contractor::findOrFail($request->id);

        $supplier->LastEdited = now();
        $supplier->Name = $validated['name'];
        $supplier->Type = $validated['type'];
        $supplier->Address = $validated['address'];
        $supplier->Email = $validated['email'] ?? null;
        // $supplier->Fax = $validated['fax'];
        $supplier->ContactPerson = $validated['contact_person'];
        $supplier->ContactNumber = $validated['mobile_number'];
        $supplier->Landline = $validated['landline'] ?? null;
        $supplier->pan_no = $validated['pan_no'] ?? null;
        $supplier->GST_no = $validated['gst_no'] ?? null;
        $supplier->retain_per = $request->filled('retention')
            ? (int) $validated['retention']
            : null;
        $supplier->ClientID = session('selected_scheme_id');
        $supplier->userID = Auth::id();

        // dd($supplier);
        $supplier->save();

        return redirect()
            ->route('SupplierContractor')
            ->with('success', 'Record has been updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $supplier = Supplier_contractor::findOrFail($id);

        if (!canDeleteRecord('po_detail', 'purchasefrom', $id)) {
            return redirect()
                ->route('SupplierContractor')
                ->with('error', 'Cannot delete its used.');
        }

        // 🔹 Log before delete
        activity_log(
            'delete',
            'Supplier/Contractor deleted: ' . $supplier->Name,
            $supplier
        );

        $supplier->delete();

        return redirect()
            ->route('SupplierContractor')
            ->with('success', 'Record has been deleted successfully!');
    }

}