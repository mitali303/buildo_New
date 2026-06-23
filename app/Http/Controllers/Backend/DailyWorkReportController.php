<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DailyWorkReportController extends Controller
{
    public function index()
{
    
     $reports = DB::table('daily_work_entry as d')
        ->leftJoin('user as u', 'u.ID', '=', 'd.UserID')
        ->select(
            'd.ID',
            'd.date',
            'd.sitename',
            'd.workdone',
            'u.Name as username'
        )
        ->orderBy('ID', 'desc')
        ->get();

    return view('backend.Reports.dailyworkreport', compact('reports'));
}

    public function destroy($id)
    {
        $report = DB::table('daily_work_entry')
            ->where('ID', $id)
            ->first();

        if ($report && !empty($report->img)) {

            $imagePath = public_path('uploads/dailywork/' . $report->img);

            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        DB::table('daily_work_entry')
            ->where('ID', $id)
            ->delete();

        return redirect()
            ->route('dailyworkreport.index')
            ->with('success', 'Report deleted successfully.');
    }
}