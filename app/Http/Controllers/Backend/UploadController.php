<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Backend\Role;
use App\Models\Backend\Permission;
use App\Models\Backend\Scheme;
use App\Models\Backend\Supplier_contractor;


class UploadController extends Controller
{
public function upload(Request $request)
{
    if ($request->hasFile('file')) {
        $file = $request->file('file');
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME); // without extension
        $extension = $file->getClientOriginalExtension();
        $filename = $originalName . '_' . uniqid() . '.' . $extension;

        $file->storeAs('public/uploads', $filename); // Saves in storage/app/public/uploads

        return response()->json(['filename' => $filename]);
    }

    return response()->json(['error' => 'No file uploaded'], 400);
}

public function delete(Request $request)
{
    $filename = $request->input('name');
    $path = storage_path('app/public/uploads/' . $filename); // Use correct path

    if (file_exists($path)) {
        unlink($path);
        return response()->json(['success' => true]);
    }

    return response()->json(['error' => 'File not found'], 404);
}

}
