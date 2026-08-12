<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Backend\CallOutcome;
use App\Models\Backend\CallPurpose;
use App\Models\Backend\CallStatus;
use App\Models\Backend\Lead;
use App\Models\Backend\LeadCall;
use App\Models\Backend\LeadContact;
use App\Models\Backend\LeadSource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CreateLeadController extends Controller
{
    /**
     * Stage badge colors, shared by index(), show(), and the Blade views.
     * Cycles a fixed palette across whatever stages Lead::stageLabels() returns,
     * so it isn't tied to an assumed count of stages.
     */
    // public static function stageColors(): array
    // {
    //     $palette = ['#fd7e14', '#e83e8c', '#8e44ad', '#20c997', '#0dcaf0', '#dc3545', '#6f42c1', '#0d6efd', '#198754', '#6c757d'];

    //     $colors = [];
    //     $i = 0;
    //     foreach (array_keys(Lead::stageLabels()) as $key) {
    //         $colors[$key] = $palette[$i % count($palette)];
    //         $i++;
    //     }

    //     return $colors;
    // }
        public static function stageColors(): array
        {
            return DB::table('leads_stage')
                ->orderBy('sequence')
                ->pluck('color', 'id')
                ->toArray();
        }
    /**
     * True if the logged-in user's role slug is 'admin'.
     * Admins see every Lead; everyone else only sees Leads assigned to
     * them or created (generated) by them.
     */
    private function currentUserIsAdmin(): bool
    {
        return (bool) optional(Auth::user())->isAdmin();
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {

            $query = Lead::select([
                'leads.id',
                'leads.company_name',
                'leads.mobile_1',
                'leads.category_name',
                'leads.lead_type',
                'leads.lead_stage',
                'leads.lead_source_id',
                'leads.assign_to',
                'leads.generated_by',
                'leads.created_at',
                'leads.taluka',
            ])
                ->with(['leadSource', 'assignedUser', 'generatedByUser'])
                ->orderByDesc('id');

            // Visibility: Admin sees everything. Everyone else sees only
            // leads assigned to them OR created (generated) by them.
            if (!$this->currentUserIsAdmin()) {
                $userId = Auth::id();
                $query->where(function ($q) use ($userId) {
                    $q->where('assign_to', $userId)
                        ->orWhere('generated_by', $userId);
                });
            }

            // Stage tab filter (ALL / New / Contacted / ... )
            if ($request->filled('stage') && $request->stage !== 'all') {
                $query->where('lead_stage', $request->stage);
            }

            // Date range filter
            if ($request->filled('from')) {
                $query->whereDate('created_at', '>=', $request->from);
            }
            if ($request->filled('to')) {
                $query->whereDate('created_at', '<=', $request->to);
            }

            // Free-text search — company name, mobile no, or city/taluka
            if ($request->filled('search')) {
                $term = $request->search;
                $query->where(function ($q) use ($term) {
                    $q->where('company_name', 'like', "%{$term}%")
                        ->orWhere('mobile_1', 'like', "%{$term}%")
                        ->orWhere('taluka', 'like', "%{$term}%")
                        ->orWhere('category_name', 'like', "%{$term}%");
                });
            }

            $perPage = (int) $request->input('limit', 12);
            $paginator = $query->paginate($perPage, ['*'], 'page', (int) $request->input('page', 1));

            $typeLabels = [1 => 'Hot', 2 => 'Warm', 3 => 'Cold'];
            $stageLabels = Lead::stageLabels();
            // $stageColors = self::stageColors();
            $stageColors = Lead::stageColors();

            $data = $paginator->getCollection()->map(function ($row) use ($typeLabels, $stageLabels, $stageColors) {

                $actions = '<a href="' . route('CreateLead.show', $row->id) . '" class="text-secondary" title="View / Log Call">
                                <i class="align-middle" data-feather="eye"></i>
                            </a>';

                $actions .= '<a href="' . route('CreateLead.edit', $row->id) . '" class="text-primary" title="Edit">
                                    <i class="align-middle" data-feather="edit-2"></i>
                                 </a>';

                $formId = 'delete-form-' . $row->id;
                    $actions .= '<a href="#" class="text-danger delete-confirm" data-id="' . $formId . '" title="Delete">
                                    <i class="align-middle" data-feather="trash"></i>
                                 </a>
                                 <form id="' . $formId . '" action="' . route('CreateLead.delete', $row->id) . '" method="POST" class="d-none">
                                    ' . csrf_field() . method_field('DELETE') . '
                                 </form>';

                // if (hasPermission('edit_CreateLead')) {
                //     $actions .= '<a href="' . route('CreateLead.edit', $row->id) . '" class="text-primary" title="Edit">
                //                     <i class="align-middle" data-feather="edit-2"></i>
                //                  </a>';
                // }

                // if (hasPermission('delete_CreateLead')) {
                //     $formId = 'delete-form-' . $row->id;
                //     $actions .= '<a href="#" class="text-danger delete-confirm" data-id="' . $formId . '" title="Delete">
                //                     <i class="align-middle" data-feather="trash"></i>
                //                  </a>
                //                  <form id="' . $formId . '" action="' . route('CreateLead.delete', $row->id) . '" method="POST" class="d-none">
                //                     ' . csrf_field() . method_field('DELETE') . '
                //                  </form>';
                // }

                return [
                    'id' => $row->id,
                    'company_name' => $row->company_name,
                    'mobile_1' => $row->mobile_1,
                    'city' => $row->taluka ?? '-',
                    'category_name' => $row->category_name ?: '-',
                    'lead_type_id' => (int) $row->lead_type,
                    'lead_type' => $typeLabels[$row->lead_type] ?? '-',
                    'lead_stage_id' => (int) $row->lead_stage,
                    'lead_stage' => $stageLabels[$row->lead_stage] ?? '-',
                    'lead_stage_color' => $stageColors[$row->lead_stage] ?? 'secondary',
                    'lead_source_name' => $row->leadSource->name ?? '-',
                    'generated_by_name' => $row->generatedByUser->name ?? '-',
                    'assign_to_name' => $row->assignedUser->name ?? '-',
                    'created_at' => $row->created_at?->format('d-m-Y h:i A'),
                    'view_url' => route('CreateLead.show', $row->id),
                    'actions' => $actions,
                ];
            });

            return response()->json([
                'data' => $data,
                'total' => $paginator->total(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
            ]);
        }

        // Counts for the stage tabs — same visibility scoping as the ajax query above.
        // $stageLabels = Lead::stageLabels();
        // $stageColors = self::stageColors();

        // $countsBase = Lead::query();
        // Dynamic stages from leads_stage table
$stages = DB::table('leads_stage')
    ->orderBy('sequence', 'asc')
    ->get();

$stageLabels = [];
$stageColors = [];
$stageIcons = [];

foreach ($stages as $stage) {
    $stageLabels[$stage->id] = $stage->name;
    $stageColors[$stage->id] = $stage->color;
    $stageIcons[$stage->id] = $stage->icon;
}


$countsBase = Lead::query();
        if (!$this->currentUserIsAdmin()) {
            $userId = Auth::id();
            $countsBase->where(function ($q) use ($userId) {
                $q->where('assign_to', $userId)->orWhere('generated_by', $userId);
            });
        }

        $counts = ['all' => (clone $countsBase)->count()];
        foreach ($stageLabels as $value => $label) {
            $counts[$value] = (clone $countsBase)->where('lead_stage', $value)->count();
        }

        return view('backend.Lead.index', compact(
    'stages',
    'stageLabels',
    'stageColors',
    'stageIcons',
    'counts'
));
    }

    /**
     * Show Create Form
     */
    public function create()
    {
        $states = DB::table('states')->get();
        return view('backend.Lead.create', $this->formData() + ['states' => $states]);
    }

    /**
     * Store
     */
    public function store(Request $request)
    {
        $validated = $this->validateLead($request);

        DB::beginTransaction();

        try {

            $lead = new Lead();
            $this->fillLead($lead, $validated);
            $lead->generated_by = Auth::id();
            $lead->save();

            $this->saveContacts($lead, $request->input('contacts', []));

            // Auto-log an initial call entry so every new lead starts
            // with a call-history row (shows as "Pending" until acted on).
            LeadCall::create([
                'lead_id' => $lead->id,
                'call_date' => now(),
                'remarks' => 'Lead created.',
                'next_followup_date' => $lead->next_followup_date,
                'called_by' => Auth::id(),
                'is_completed' => false,
            ]);

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Something went wrong: ' . $e->getMessage());
        }

        return redirect()
            ->route('CreateLead')
            ->with('success', 'Lead created successfully!');
    }

    /**
     * Show Edit Form
     */
    public function edit(string $id)
    {
        $old = Lead::with('contacts')->findOrFail($id);
        $states = DB::table('states')->get();
        return view('backend.Lead.create', $this->formData() + ['old' => $old, 'states' => $states]);
    }

    /**
     * Update
     */
    public function update(Request $request)
    {
        $lead = Lead::findOrFail($request->id);

        $validated = $this->validateLead($request);

        DB::beginTransaction();

        try {

            $this->fillLead($lead, $validated);
            $lead->save();

            // Replace all contacts (simplest & safest for a repeater-style form)
            $lead->contacts()->delete();
            $this->saveContacts($lead, $request->input('contacts', []));

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Something went wrong: ' . $e->getMessage());
        }

        return redirect()
            ->route('CreateLead')
            ->with('success', 'Lead updated successfully!');
    }

    /**
     * View — shows full lead detail + contacts + call history + "Log a Call" form.
     */
    public function show(string $id)
    {
        $lead = Lead::with(['contacts', 'calls.callStatus', 'calls.callPurpose', 'calls.callOutcome', 'calls.calledByUser', 'leadSource', 'assignedUser'])
            ->findOrFail($id);

        $callStatuses = CallStatus::orderBy('name')->get();
        $callPurposes = CallPurpose::orderBy('name')->get();
        $callOutcomes = CallOutcome::orderBy('name')->get();
        $stages = DB::table('leads_stage')
    ->orderBy('sequence','asc')
    ->get();

$stageLabels = [];
$stageColors = [];

foreach($stages as $stage){
    $stageLabels[$stage->id] = $stage->name;
    $stageColors[$stage->id] = $stage->color;
}
        $typeLabels = [1 => 'Hot', 2 => 'Warm', 3 => 'Cold'];

        // Related quotations — read defensively since the Quotation model/relation
        // isn't part of this batch of files. Wire Lead::quotations() (hasMany, lead_id)
        // once the Quotation model is available and this will populate automatically.
        $quotations = method_exists($lead, 'quotations') ? $lead->quotations()->latest()->get() : collect();

        // Meetings logged against this lead — requires Lead::meetings() hasMany(LeadMeeting::class, 'lead_id')
        $meetings = method_exists($lead, 'meetings') ? $lead->meetings()->orderByDesc('meeting_date')->get() : collect();

        return view('backend.Lead.show', compact('lead', 'callStatuses', 'callPurposes', 'callOutcomes', 'stageLabels', 'stageColors', 'typeLabels', 'quotations', 'meetings'));
    }

    /**
     * Quick stage change — used by the "Convert" action on the Lead view page.
     * Only touches lead_stage, so it doesn't run the full lead validation.
     */
    public function updateStage(Request $request, string $id)
    {
        $validated = $request->validate([
            'lead_stage' => ['required', Rule::in(array_keys(Lead::stageLabels()))],
        ]);

        $lead = Lead::findOrFail($id);
        $lead->lead_stage = $validated['lead_stage'];
        $lead->save();

        $message = 'Lead stage updated to "' . Lead::stageLabels()[$validated['lead_stage']] . '".';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'lead_stage' => (int) $validated['lead_stage'],
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Delete (single) — explicitly cascades contacts + calls inside a transaction
     * rather than assuming a DB-level FK cascade is configured.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();

        try {
            $lead = Lead::findOrFail($id);
            $lead->contacts()->delete();
            $lead->calls()->delete();
            if (method_exists($lead, 'meetings')) {
                $lead->meetings()->delete();
            }
            $lead->delete();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Could not delete lead: ' . $e->getMessage());
        }

        return redirect()
            ->route('CreateLead')
            ->with('success', 'Lead deleted successfully!');
    }

    /**
     * Bulk delete — the checkbox + "Delete" button at the top of the list.
     * Explicitly cascades contacts + calls + meetings for every selected lead.
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('error', 'Please select at least one Lead to delete.');
        }

        DB::beginTransaction();

        try {
            $leads = Lead::whereIn('id', $ids)->get();
            foreach ($leads as $lead) {
                $lead->contacts()->delete();
                $lead->calls()->delete();
                if (method_exists($lead, 'meetings')) {
                    $lead->meetings()->delete();
                }
                $lead->delete();
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Could not delete selected leads: ' . $e->getMessage());
        }

        return redirect()
            ->route('CreateLead')
            ->with('success', count($ids) . ' Lead(s) deleted successfully!');
    }

    /**
     * Shared dropdown data for create/edit forms.
     */
    // private function formData(): array
    // {
    //     return [
    //         'leadSources' => LeadSource::orderBy('name')->get(),
    //         'users' => User::orderBy('name')->get(),
    //         'stageLabels' => Lead::stageLabels(),
    //     ];
    // }
private function formData(): array
{
    $stages = DB::table('leads_stage')
        ->select('id','name')
        ->orderBy('sequence', 'asc')
        ->get();

    $stageLabels = [];

    foreach ($stages as $stage) {
        $stageLabels[$stage->id] = $stage->name;
    }

    return [
        'leadSources' => LeadSource::orderBy('name')->get(),
        'users' => User::orderBy('name')->get(),
        'stageLabels' => $stageLabels,
    ];
}

    private function validateLead(Request $request): array
    {
        return $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'lead_name' => ['nullable', 'string', 'max:255'],
            'mobile_1' => ['required', 'digits:10', 'regex:/^[6-9][0-9]{9}$/'],
            'mobile_2' => ['nullable', 'digits:10', 'regex:/^[6-9][0-9]{9}$/'],
            'address' => ['nullable', 'string', 'max:500'],
            'country' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'district' => ['required', 'string'],
            'taluka' => ['required', 'string'],
            'pincode' => ['nullable', 'digits:6'],
            'category_name' => ['nullable', 'string', 'max:255'],
            'subcategory' => ['nullable', 'string', 'max:255'],
            'lead_type' => ['required', 'in:1,2,3'],
            'email_1' => ['nullable', 'email', 'max:255'],
            'email_2' => ['nullable', 'email', 'max:255'],
            'lead_stage' => [
                    'required',
                    Rule::in(DB::table('leads_stage')->pluck('id')->toArray())
                ],
            'lead_source_id' => ['required', 'exists:lead_source,id'],
            'description' => ['nullable', 'string'],
            'product_requirements' => ['nullable', 'string'],
            'assign_to' => ['required', 'exists:user,ID'],
            'next_followup_date' => ['required', 'date'],

            'contacts' => ['nullable', 'array'],
            'contacts.*.name' => ['nullable', 'string', 'max:255'],
            'contacts.*.email' => ['nullable', 'email', 'max:255'],
            'contacts.*.mobile' => ['nullable', 'digits:10'],
            'contacts.*.designation' => ['nullable', 'string', 'max:255'],
            'contacts.*.department' => ['nullable', 'string', 'max:255'],
        ], [
            'company_name.required' => 'Company Name is required.',
            'mobile_1.required' => 'Mobile No 1 is required.',
            'mobile_1.regex' => 'Enter a valid Indian mobile number.',
            'district.required' => 'Please select a District.',
            'taluka.required' => 'Please select a Taluka/City.',
            'lead_type.required' => 'Please select a Lead Type.',
            'lead_source_id.required' => 'Please select a Lead Source.',
            'assign_to.required' => 'Please select who this Lead is assigned to.',
            'next_followup_date.required' => 'Next Followup Date is required.',
        ]);
    }

    private function fillLead(Lead $lead, array $validated): void
    {
        $lead->company_name = $validated['company_name'];
        $lead->lead_name = $validated['lead_name'] ?? null;
        $lead->mobile_1 = $validated['mobile_1'];
        $lead->mobile_2 = $validated['mobile_2'] ?? null;
        $lead->address = $validated['address'] ?? null;
        $lead->country = $validated['country'] ?? 'India';
        $lead->state = $validated['state'] ?? null;
        $lead->district = $validated['district'];
        $lead->taluka = $validated['taluka'];
        $lead->pincode = $validated['pincode'] ?? null;
        $lead->category_name = $validated['category_name'] ?? null;
        $lead->subcategory = $validated['subcategory'] ?? null;
        $lead->lead_type = $validated['lead_type'];
        $lead->email_1 = $validated['email_1'] ?? null;
        $lead->email_2 = $validated['email_2'] ?? null;
        $lead->lead_stage = $validated['lead_stage'];
        $lead->lead_source_id = $validated['lead_source_id'];
        $lead->description = $validated['description'] ?? null;
        $lead->product_requirements = $validated['product_requirements'] ?? null;
        $lead->assign_to = $validated['assign_to'];
        $lead->next_followup_date = $validated['next_followup_date'];
    }

    private function saveContacts(Lead $lead, array $contacts): void
    {
        foreach ($contacts as $contact) {
            if (empty($contact['name'])) {
                continue;
            }

            LeadContact::create([
                'lead_id' => $lead->id,
                'name' => $contact['name'],
                'email' => $contact['email'] ?? null,
                'mobile' => $contact['mobile'] ?? null,
                'designation' => $contact['designation'] ?? null,
                'department' => $contact['department'] ?? null,
            ]);
        }
    }
}