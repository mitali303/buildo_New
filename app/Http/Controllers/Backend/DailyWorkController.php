<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Backend\DailyWorkEntry;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Backend\SchemeDetail;



class DailyWorkController extends Controller
{

    /**
     * Display Daily Work List
     */
    public function index(Request $request)
    {

        if($request->ajax())
        {

      $query = DailyWorkEntry::from('daily_work_entry')
        ->leftJoin(
            'scheme_step1',
            'daily_work_entry.ClientID',
            '=',
            'scheme_step1.ID'
        )

        ->select([

            'daily_work_entry.ID',
            'daily_work_entry.ClientID',
            'daily_work_entry.date',
            'scheme_step1.Name as sitename',
            'daily_work_entry.workdone',
            'daily_work_entry.img',
            'daily_work_entry.Created'
        ]);


            // Normal user only own data
            if(Auth::user()->Role != 1)
            {
                $query->where(
                    'daily_work_entry.UserID',
                    Auth::id()
                );
            }


            return DataTables::of($query)
            ->addIndexColumn()
             ->addColumn('img', function ($row) {

                if (!empty($row->img)) {

                    $imageUrl = asset($row->img);

                    return '
                        <a href="' . $imageUrl . '" target="_blank">
                            <img src="' . $imageUrl . '"
                                style="width:70px;height:50px;object-fit:cover;border-radius:5px;border:1px solid #ddd;">
                        </a>
                    ';
                }

                return '<span class="text-muted">No Image</span>';
            })
            ->addColumn('actions',function($row){

        $editUrl = route('DailyWork.edit', $row->ID);


    $deleteUrl = route( 'DailyWork.delete', $row->ID );
    $formId = "delete-form-".$row->ID;
    $action = '';
    // Edit Button
    $action .= '

    <a href="'.$editUrl.'"  class="me-2 text-primary">
        <i data-feather="edit-2"></i>
    </a>';

    // Delete Button

    $action .= '

    <a href="#" class="text-danger delete-confirm" data-id="'.$formId.'">
        <i data-feather="trash"></i>
    </a>


    <form id="'.$formId.'" action="'.$deleteUrl.'" method="POST" class="d-none">
        '.csrf_field().'
        '.method_field('DELETE').'
    </form>';



    return $action;
    })


            ->rawColumns(['img', 'actions'])
            ->make(true);

        }
        return view('backend.DailyWork.index' );

    }
    /**
 * Show create form
 */
// public function create()
// {

//     $query = SchemeDetail::select('ID','Name');


//     if(Auth::user()->Role != 1)
//     {
//         $query->where('userID',Auth::id());
//     }


//     $schemes = $query->orderBy('Name')->get();


//     return view(
//         'backend.DailyWork.create',
//         compact('schemes')
//     );

// }
public function create()
{
    $user = Auth::user();

    if ($user->Role == 1) {

        // Admin -> all schemes
        $schemes = SchemeDetail::select('ID', 'Name')
            ->orderBy('Name')
            ->get();

    } else {

        // User/Supervisor -> assigned schemes from user.scheme JSON
        $schemeIds = json_decode($user->scheme, true);

        if (!is_array($schemeIds)) {
            $schemeIds = [];
        }

        $schemes = SchemeDetail::select('ID', 'Name')
            ->whereIn('ID', $schemeIds)
            ->orderBy('Name')
            ->get();
    }

    return view(
        'backend.DailyWork.create',
        compact('schemes')
    );
}

/**
 * Store Daily Work
 */
public function store(Request $request)
{
    $validated = $request->validate([

        'date' => 'required|date',
        'ClientID' => 'required|string',
        'sitename' => 'required|string|max:225',
        'workdone' => 'required|string',
        // Image validation
        'img' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:1024',

      ], [

        'img.required' => 'Please upload work image.',
        'img.image' => 'The uploaded file must be an image.',
        'img.mimes' => 'Only JPG, JPEG, PNG and WEBP images are allowed.',
        'img.max' => 'Image size must be less than 1 MB.',

    ]);


    $daily = new DailyWorkEntry();


    $daily->ClientID   = $validated['ClientID'];
    $daily->date       = $validated['date'];
    $daily->sitename   = $validated['sitename'];
    $daily->workdone   = $validated['workdone'];
    $daily->UserID     = Auth::id();
    $daily->Created    = now();
    $daily->LastEdited = now();
     // Upload Image
    if ($request->hasFile('img')) {

    $image = $request->file('img');

    $imageName = time() . '_' . uniqid() . '.' .
                 $image->getClientOriginalExtension();

    $image->move(
        public_path('uploads/daily_work'),
        $imageName
    );

    $daily->img = 'uploads/daily_work/' . $imageName;
}

    $daily->save();



    return redirect()
        ->route('DailyWork')
        ->with(
            'success',
            'Daily work added successfully!'
        );
}

// public function edit($id)
// {

//     $dailyWork = DailyWorkEntry::findOrFail($id);


//     $query = SchemeDetail::select('ID','Name');


//     // if(Auth::user()->Role != 1)
//     // {
//     //     $query->where('userID',Auth::id());
//     // }


//     $schemes = $query->orderBy('Name')->get();



//     return view(
//         'backend.DailyWork.create',
//         compact(
//             'dailyWork',
//             'schemes'
//         )
//     );

// }
public function edit($id)
{
    $dailyWork = DailyWorkEntry::findOrFail($id);

    $user = Auth::user();

    if ($user->Role == 1) {

        $schemes = SchemeDetail::select('ID', 'Name')
            ->orderBy('Name')
            ->get();

    } else {

        $schemeIds = json_decode($user->scheme, true);

        if (!is_array($schemeIds)) {
            $schemeIds = [];
        }

        $schemes = SchemeDetail::select('ID', 'Name')
            ->whereIn('ID', $schemeIds)
            ->orderBy('Name')
            ->get();
    }

    return view(
        'backend.DailyWork.create',
        compact('dailyWork', 'schemes')
    );
}
public function update(Request $request)
{

    $validated = $request->validate([

        'id' => 'required|exists:daily_work_entry,ID',
        'date' => 'required|date',
        'ClientID' => 'required|string',
        'sitename' => 'required|string|max:225',
        'workdone' => 'required|string',
        // Edit time image optional
        'img' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:1024',

    ], [

        'img.image' => 'The uploaded file must be an image.',
        'img.mimes' => 'Only JPG, JPEG, PNG and WEBP images are allowed.',
        'img.max' => 'Image size must be less than 1 MB.',

    ]);




    $daily = DailyWorkEntry::findOrFail(
        $validated['id']
    );


    $daily->ClientID = $validated['ClientID'];
    $daily->date = $validated['date'];
    $daily->sitename = $validated['sitename'];
    $daily->workdone = $validated['workdone'];
    $daily->LastEdited = now();
    // New image uploaded
    // Image update
    if ($request->hasFile('img')) {

        $image = $request->file('img');

        $imageName = time() . '_' . uniqid() . '.' .
                     $image->getClientOriginalExtension();

        $image->move(
            public_path('uploads/daily_work'),
            $imageName
        );

        // जुनी image delete करायची असल्यास
        if (!empty($daily->img)) {

            $oldImage = public_path($daily->img);

            if (file_exists($oldImage)) {
                unlink($oldImage);
            }
        }

        $daily->img = 'uploads/daily_work/' . $imageName;
    }
    $daily->save();



    return redirect()
        ->route('DailyWork')
        ->with(
            'success',
            'Daily work updated successfully!'
        );

}
public function destroy($id)
{


    $daily = DailyWorkEntry::findOrFail($id);
    // Delete image from storage
    if (!empty($daily->img)) {

        Storage::disk('public')->delete(
            $daily->img
        );
    }

    $daily->delete();

    return redirect()
        ->route('DailyWork')
        ->with(
            'success',
            'Daily work deleted successfully!'
        );

}



}