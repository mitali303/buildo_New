use App\Models\MaterialRequest;

public function materialRequestList(Request $request)
{
    $schemeId = $request->scheme_id;

    $requests = MaterialRequest::with('items')
        ->where('ClientID', $schemeId)
        ->orderBy('Created','DESC')
        ->get();

    return view('material_request.list',compact('requests'));
}