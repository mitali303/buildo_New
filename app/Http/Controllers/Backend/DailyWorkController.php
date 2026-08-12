<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
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


            ->rawColumns(['actions'])
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

    ]);


    $daily = new DailyWorkEntry();


    $daily->ClientID   = $validated['ClientID'];
    $daily->date       = $validated['date'];
    $daily->sitename   = $validated['sitename'];
    $daily->workdone   = $validated['workdone'];
    $daily->UserID     = Auth::id();
    $daily->Created    = now();
    $daily->LastEdited = now();
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

    ]);



    $daily = DailyWorkEntry::findOrFail(
        $validated['id']
    );


    $daily->ClientID = $validated['ClientID'];
    $daily->date = $validated['date'];
    $daily->sitename = $validated['sitename'];
    $daily->workdone = $validated['workdone'];
    $daily->LastEdited = now();
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
    $daily->delete();

    return redirect()
        ->route('DailyWork')
        ->with(
            'success',
            'Daily work deleted successfully!'
        );

}



}