<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserAppController;
use App\Http\Controllers\Api\AndroidController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
// Routes for login
Route::post('/applogin', [AndroidController::class, 'loginapp'])->middleware('throttle:5,1');
// User App Login Api
Route::post('/userapplogin', [UserAppController::class, 'loginapp'])->middleware('throttle:5,1');


// Authenticated API group
Route::post('/getschemes', [UserAppController::class, 'getschemes']);

Route::post('/getDashboardData', [UserAppController::class, 'getDashboardData']);

Route::post('/saveDailyWork', [UserAppController::class, 'saveDailyWork']);

Route::post('/getDailyWorkReports', [UserAppController::class, 'getDailyWorkReports']); 

Route::post('/getLaborWorkReports', [UserAppController::class, 'getLaborWorkReports']);

Route::post('/getAgencies', [UserAppController::class, 'getAgencies']); 

Route::post('/getAgencyRates', [UserAppController::class, 'getAgencyRates']); 

Route::post('/saveLaborWork', [UserAppController::class, 'saveLaborWork']); 

Route::post('/deleteLaborWork', [UserAppController::class, 'deleteLaborWork']); 


    // Transfer_material
    Route::post('/getMaterialTransfers', [UserAppController::class, 'getMaterialTransfers']);

    Route::post('/getSites', [UserAppController::class, 'getSites']);

    Route::post('/getMaterials', [UserAppController::class, 'getMaterials']);

    Route::post('/getMaterialTypesByName', [UserAppController::class, 'getMaterialTypesByName']);

    Route::post('/getMaterialStock', [UserAppController::class, 'getMaterialStock']);

    Route::post('/saveMaterialTransfer', [UserAppController::class, 'saveMaterialTransfer']);

    Route::post('/deleteMaterialTransfer', [UserAppController::class, 'deleteMaterialTransfer']);

    
    
    // Material Consumption
    Route::post('/getMaterialConsumptions', [UserAppController::class, 'getMaterialConsumptions']);

    Route::post('/saveMaterialConsumption', [UserAppController::class, 'saveMaterialConsumption']);

    Route::post('/deleteMaterialConsumption', [UserAppController::class, 'deleteMaterialConsumption']);


    // Material Inward
    Route::post('/getMaterialInward', [UserAppController::class, 'getMaterialInward']);


    Route::post('/getMaterialInwardDetails', [UserAppController::class, 'getMaterialInwardDetails']);

    Route::post('/getNextInvoiceNo', [UserAppController::class, 'getNextInvoiceNo']);

    Route::post('/getVendors', [UserAppController::class, 'getVendors']);
    
    Route::post('/uploadFile', [UserAppController::class, 'uploadFile']);

    Route::post('/saveMaterialInward', [UserAppController::class, 'saveMaterialInward']);

    Route::post('/getAttachment', [UserAppController::class, 'getAttachment']);

    Route::post('/deleteMaterialInward', [UserAppController::class, 'deleteMaterialInward']);


    Route::post('/getSchemesForEnquiry', [UserAppController::class, 'getSchemesForEnquiry']);

    Route::post('/submitEnquiry', [UserAppController::class, 'submitEnquiry']);

    Route::post('/getEnquiries', [UserAppController::class, 'getEnquiries']);
    
    Route::post('/deleteEnquiry', [UserAppController::class, 'deleteEnquiry']);


    Route::post('/updateEnquiryStatus', [UserAppController::class, 'updateEnquiryStatus']);


     
