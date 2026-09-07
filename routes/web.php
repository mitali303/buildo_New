<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Backend\UsersController;
use App\Http\Controllers\Backend\RolesController;
use App\Http\Controllers\Backend\Supplier_contractorController;
use App\Http\Controllers\Backend\SchemeController;
use App\Http\Controllers\Backend\PaymentSlabController;
use App\Http\Controllers\Backend\StaffController;
use App\Http\Controllers\Backend\PartnerLoanInvestorController;
use App\Http\Controllers\Backend\AgencyController;
use App\Http\Controllers\Backend\LabourWorkController;
use App\Http\Controllers\Backend\BankFormController;
use App\Http\Controllers\Backend\BankAccController;
use App\Http\Controllers\Backend\MaterialController;
use App\Http\Controllers\Backend\SChemeDetailController;
use App\Http\Controllers\Backend\FlatDetailController;
use App\Http\Controllers\Backend\UploadController;
use App\Http\Controllers\Backend\SchemeCompleteController;
use App\Http\Controllers\Backend\PurchaseOrderController;
use App\Http\Controllers\Backend\PurchaseInvoiceController;
use App\Http\Controllers\Backend\Rejected_MaterialController;
use App\Http\Controllers\Backend\Transfer_MaterialController;
use App\Http\Controllers\Backend\Site_work_orderController;
use App\Http\Controllers\Backend\Site_work_payController;
use App\Http\Controllers\Backend\OwnerPaymentController;
use App\Http\Controllers\Backend\Labour_payController;
use App\Http\Controllers\Backend\Material_payController;
use App\Http\Controllers\Backend\Partner_payController;
use App\Http\Controllers\Backend\Site_exp_payController;
use App\Http\Controllers\Backend\Return_PayController;
use App\Http\Controllers\Backend\Tds_payController;
use App\Http\Controllers\Backend\Material_ConsumptionController;
use App\Http\Controllers\Backend\Report_Controller;
use App\Http\Controllers\Backend\Demand_raiseController;
use App\Http\Controllers\Backend\Booking_CancelController;
use App\Http\Controllers\Backend\Add_BillController;

use App\Http\Controllers\Backend\LoanManagementController;
use App\Http\Controllers\Backend\AccountTransferController;
use App\Http\Controllers\Backend\BankReconciliationController;
use App\Http\Controllers\Backend\CustomerPaymentController;
use App\Http\Controllers\Backend\PostDateChequeController;
use App\Http\Controllers\Backend\CustomerRefundController;
use App\Http\Controllers\Backend\StampExpensesController;
use App\Http\Controllers\Backend\LandExpensesController;
use App\Http\Controllers\Backend\CustomerBookingController;
use App\Http\Controllers\Backend\CompanySettingController;
use App\Http\Controllers\Backend\EmployeeAdvanceController;
use App\Http\Controllers\Backend\ShiftController;
use App\Http\Controllers\Backend\AttendanceMasterController;
use App\Http\Controllers\Backend\SalaryMasterController;
use App\Http\Controllers\Backend\UserShiftAssignmentController;
use App\Http\Controllers\Backend\LateMarkCalculationController;
// use App\Http\Controllers\Backend\EmployeeAdvanceController;
// use App\Http\Controllers\Backend\AttendanceMasterController;
// use App\Http\Controllers\Backend\SalaryMasterController;
use App\Http\Controllers\Backend\DailyWorkReportController;
use App\Http\Controllers\Backend\EnquiryController;
use App\Http\Controllers\Backend\LeadController;
use App\Http\Controllers\Backend\CallOutcomeController;
use App\Http\Controllers\Backend\CallStatusController;
use App\Http\Controllers\Backend\CallPurposeController;
use App\Http\Controllers\Backend\LeadCallController;
use App\Http\Controllers\Backend\CreateLeadController;
use App\Http\Controllers\Backend\LeadSourceController;
use App\Http\Controllers\Backend\LeadMeetingController;
use App\Http\Controllers\Backend\QuotationController;
use App\Http\Controllers\Backend\EstimateController;
use App\Http\Controllers\Backend\DailyWorkController;
use App\Http\Controllers\Backend\MaterialRequestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Auth::routes();

Route::group(['middleware' => 'auth'], function () {
    // xss protection
    Route::group(['middleware' => 'XSS'], function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        // Users Controller
        Route::get('/users', [UsersController::class, 'index'])->name('users');
        Route::get('/users/create', [UsersController::class, 'create'])->name('users.create');
        Route::post('/users/save', [UsersController::class, 'store'])->name('users.store');
        Route::get('/users/edit/{id}', [UsersController::class, 'edit'])->name('users.edit');
        Route::put('/users/update', [UsersController::class, 'update'])->name('users.update');
        Route::delete('/users/delete/{id}', [UsersController::class, 'destroy'])->name('users.delete');
        // Roles Controller
        Route::get('/roles', [RolesController::class, 'index'])->name('roles');
        Route::get('/roles/create', [RolesController::class, 'create'])->name('roles.create');
        Route::post('/roles/save', [RolesController::class, 'store'])->name('roles.store');
        Route::get('/roles/edit/{id}', [RolesController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/update', [RolesController::class, 'update'])->name('roles.update');
        Route::delete('/roles/delete/{id}', [RolesController::class, 'destroy'])->name('roles.delete');
        // Supplier And Contractor
        Route::get('/supplier-contractors', [Supplier_contractorController::class, 'index'])->name('SupplierContractor');
        Route::get('/supplier-contractors/create', [Supplier_contractorController::class, 'create'])->name('SupplierContractor.create');
        Route::post('/supplier-contractors/save', [Supplier_contractorController::class, 'store'])->name('SupplierContractor.store');
        Route::get('/supplier-contractors/edit/{id}', [Supplier_contractorController::class, 'edit'])->name('SupplierContractor.edit');
        Route::put('/supplier-contractors/update', [Supplier_contractorController::class, 'update'])->name('SupplierContractor.update');
        Route::delete('/supplier-contractors/delete/{id}', [Supplier_contractorController::class, 'destroy'])->name('SupplierContractor.delete');
        // Payment Slab
        Route::get('/payment-slab', [PaymentSlabController::class, 'index'])->name('PaymentSlab');
        Route::get('/payment-slab/create', [PaymentSlabController::class, 'create'])->name('PaymentSlab.create');
        Route::post('/payment-slab/save', [PaymentSlabController::class, 'store'])->name('PaymentSlab.store');
        Route::get('/payment-slab/edit/{id}', [PaymentSlabController::class, 'edit'])->name('PaymentSlab.edit');
        Route::put('/payment-slab/update', [PaymentSlabController::class, 'update'])->name('PaymentSlab.update');
        Route::delete('/payment-slab/delete/{id}', [PaymentSlabController::class, 'destroy'])->name('PaymentSlab.delete');
        // Staff
        Route::get('/staff', [StaffController::class, 'index'])->name('Staff');
        Route::get('/staff/create', [StaffController::class, 'create'])->name('Staff.create');
        Route::post('/staff/save', [StaffController::class, 'store'])->name('Staff.store');
        Route::get('/staff/edit/{id}', [StaffController::class, 'edit'])->name('Staff.edit');
        Route::put('/staff/update', [StaffController::class, 'update'])->name('Staff.update');
        Route::delete('/staff/delete/{id}', [StaffController::class, 'destroy'])->name('Staff.delete');
        // Partners Loan or Investors
        Route::get('/partner-loan-investors', [PartnerLoanInvestorController::class, 'index'])->name('PartnerLoanInvestor');
        Route::get('/partner-loan-investors/create', [PartnerLoanInvestorController::class, 'create'])->name('PartnerLoanInvestor.create');
        Route::post('/partner-loan-investors/save', [PartnerLoanInvestorController::class, 'store'])->name('PartnerLoanInvestor.store');
        Route::get('/partner-loan-investors/edit/{id}', [PartnerLoanInvestorController::class, 'edit'])->name('PartnerLoanInvestor.edit');
        Route::put('/partner-loan-investors/update', [PartnerLoanInvestorController::class, 'update'])->name('PartnerLoanInvestor.update');
        Route::delete('/partner-loan-investors/delete/{id}', [PartnerLoanInvestorController::class, 'destroy'])->name('PartnerLoanInvestor.delete');
        Route::post(
            '/partner-loan-investors/toggle‑status',
            [PartnerLoanInvestorController::class, 'toggleStatus']
        )->name('PartnerLoanInvestor.toggle');

        // Bank Form
        Route::get('/bank-loan-requests', [BankFormController::class, 'index'])->name('BankForm');
        Route::get('/bank-loan-requests/create', [BankFormController::class, 'create'])->name('BankForm.create');
        Route::post('/bank-loan-requests/save', [BankFormController::class, 'store'])->name('BankForm.store');
        Route::get('/bank-loan-requests/edit/{id}', [BankFormController::class, 'edit'])->name('BankForm.edit');
        Route::put('/bank-loan-requests/update', [BankFormController::class, 'update'])->name('BankForm.update');
        Route::delete('/bank-loan-requests/delete/{id}', [BankFormController::class, 'destroy'])->name('BankForm.delete');

        Route::get('/bank-form/print/{id}', [BankFormController::class, 'print'])
            ->name('BankForm.print');

        // Bank Account
        Route::get('/bank-accounts', [BankAccController::class, 'index'])->name('BankAcc');
        Route::get('/bank-accounts/create', [BankAccController::class, 'create'])->name('BankAcc.create');
        Route::post('/bank-accounts/save', [BankAccController::class, 'store'])->name('BankAcc.store');
        Route::get('/bank-accounts/edit/{id}', [BankAccController::class, 'edit'])->name('BankAcc.edit');
        Route::put('/bank-accounts/update', [BankAccController::class, 'update'])->name('BankAcc.update');
        Route::delete('/bank-accounts/delete/{id}', [BankAccController::class, 'destroy'])->name('BankAcc.delete');

        // Agency
        Route::get('/agency', [AgencyController::class, 'index'])->name('Agency');
        Route::get('/agency/create', [AgencyController::class, 'create'])->name('Agency.create');
        Route::post('/agency/save', [AgencyController::class, 'store'])->name('Agency.store');
        Route::get('/agency/edit/{id}', [AgencyController::class, 'edit'])->name('Agency.edit');
        Route::put('/agency/update', [AgencyController::class, 'update'])->name('Agency.update');
        Route::delete('/agency/delete/{id}', [AgencyController::class, 'destroy'])->name('Agency.delete');
        Route::get('/get-agency-rates/{id}', [AgencyController::class, 'getRates'])->name('agency.getRates');

        // Labour Work
        Route::get('/Labour_Work', [LabourWorkController::class, 'index'])->name('Labour_Work');
        Route::get('/Labour_Work/create', [LabourWorkController::class, 'create'])->name('Labour_Work.create');
        Route::post('/Labour_Work/save', [LabourWorkController::class, 'store'])->name('Labour_Work.store');
        Route::get('/Labour_Work/edit/{id}', [LabourWorkController::class, 'edit'])->name('Labour_Work.edit');
        Route::put('/Labour_Work/update', [LabourWorkController::class, 'update'])->name('Labour_Work.update');
        Route::delete('/Labour_Work/delete/{id}', [LabourWorkController::class, 'destroy'])->name('Labour_Work.delete');

        // Material
        Route::get('/materials', [MaterialController::class, 'index'])->name('Material');
        Route::get('/materials/create', [MaterialController::class, 'create'])->name('Material.create');
        Route::post('/materials/save', [MaterialController::class, 'store'])->name('Material.store');
        Route::get('/materials/edit/{id}', [MaterialController::class, 'edit'])->name('Material.edit');
        Route::put('/materials/update', [MaterialController::class, 'update'])->name('Material.update');
        Route::delete('/materialss/delete/{id}', [MaterialController::class, 'destroy'])->name('Material.delete');

        // Scheme Details
        Route::get('/scheme-details', [SChemeDetailController::class, 'index'])->name('Scheme');
        Route::get('/scheme-details/create', [SChemeDetailController::class, 'create'])->name('Scheme.create');
        Route::post('/scheme-details/save', [SChemeDetailController::class, 'store'])->name('Scheme.store');
        Route::get('/scheme-details/edit/{id}', [SChemeDetailController::class, 'edit'])->name('Scheme.edit');
        Route::put('/scheme-details/update', [SChemeDetailController::class, 'update'])->name('Scheme.update');
        Route::delete('/scheme-details/delete/{id}', [SChemeDetailController::class, 'destroy'])->name('Scheme.delete');

        // Flat Details
        Route::get('/flat-details', [FlatDetailController::class, 'index'])->name('Flat');
        Route::get('/flat-details/create', [FlatDetailController::class, 'create'])->name('Flat.create');
        Route::post('/flat-details/save', [FlatDetailController::class, 'store'])->name('Flat.store');
        Route::get('/flat-details/edit/{id}', [FlatDetailController::class, 'edit'])->name('Flat.edit');
        Route::put('/flat-details/update', [FlatDetailController::class, 'update'])->name('Flat.update');
        Route::delete('/flat-details/delete/{id}', [FlatDetailController::class, 'destroy'])->name('Flat.delete');
        Route::get('/get-scheme-details/{id}', [FlatDetailController::class, 'getSchemeDetails']);
        Route::post('/upload-image', [UploadController::class, 'upload'])->name('upload.image');
        Route::post('/upload/delete', [UploadController::class, 'delete'])->name('delete.image');
        Route::post('/flat-details/save-row', [FlatDetailController::class, 'saveRow'])->name('flatDetails.saveRow');
        Route::post('/flat-details/update-row', [FlatDetailController::class, 'updateRow'])->name('flatDetails.updateRow');

        //scheme Completion
        Route::get('/scheme-completions', [SchemeCompleteController::class, 'index'])->name('SchemeComplete');
        Route::post(
            '/scheme-completions/toggle‑status',
            [SchemeCompleteController::class, 'toggleStatus']
        )->name('SchemeComplete.toggle');

        //Purchase Order
        //Purchase Order
        Route::get('/purchase-order/print/{id}', [PurchaseOrderController::class, 'print'])
            ->name('PurchaseOrder.print');

        Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('PurchaseOrder');
        Route::get('/purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('PurchaseOrder.create');
        Route::post('/purchase-orders/save', [PurchaseOrderController::class, 'store'])->name('PurchaseOrder.store');
        Route::get('/purchase-orders/edit/{id}', [PurchaseOrderController::class, 'edit'])->name('PurchaseOrder.edit');
        Route::put('/purchase-orders/update', [PurchaseOrderController::class, 'update'])->name('PurchaseOrder.update');
        Route::delete('/purchase-orders/delete/{id}', [PurchaseOrderController::class, 'destroy'])->name('PurchaseOrder.delete');
        Route::post('/material/get-types', [PurchaseOrderController::class, 'getTypes'])->name('material.getTypes');
        Route::post('/purchase-order/add-row', [PurchaseOrderController::class, 'addRow'])->name('PurchaseOrder.addRow');

        //Purchase Invoice
        Route::get('/purchase-invoices', [PurchaseInvoiceController::class, 'index'])->name('PurchaseInvoice');
        Route::get('/purchase-invoices/create', [PurchaseInvoiceController::class, 'create'])->name('PurchaseInvoice.create');
        Route::post('/purchase-invoices/save', [PurchaseInvoiceController::class, 'store'])->name('PurchaseInvoice.store');
        Route::get('/purchase-invoices/edit/{id}', [PurchaseInvoiceController::class, 'edit'])->name('PurchaseInvoice.edit');
        Route::put('/purchase-invoices/update', [PurchaseInvoiceController::class, 'update'])->name('PurchaseInvoice.update');
        Route::delete('/purchase-invoices/delete/{id}', [PurchaseInvoiceController::class, 'destroy'])->name('PurchaseInvoice.delete');
        Route::post('/material-invoices/get-types', [PurchaseInvoiceController::class, 'getTypes'])->name('material.getTypes');
        Route::post('/purchase-invoices/add-row', [PurchaseInvoiceController::class, 'addRow'])->name('PurchaseInvoice.addRow');
        Route::post('/purchase-invoices-material/get-types', [PurchaseInvoiceController::class, 'getTypes'])->name('PurchaseInvoice.material.getTypes');
        Route::post('/purchase-invoices/add-row', [PurchaseInvoiceController::class, 'addRow'])->name('PurchaseInvoice.addRow');
        Route::post('/purchase-invoices/upload', [PurchaseInvoiceController::class, 'uploadImage'])->name('upload.attachmentinv');
        Route::post('/purchase-invoices/delete-image', [PurchaseInvoiceController::class, 'deleteImage'])
            ->name('delete.imageinv');


        //Site Work Order
        Route::get('/site-work-orders', [Site_work_orderController::class, 'index'])->name('Site_work_order');
        Route::get('/site-work-orders/create', [Site_work_orderController::class, 'create'])->name('Site_work_order.create');
        Route::post('/site-work-orders/save', [Site_work_orderController::class, 'store'])->name('Site_work_order.store');
        Route::get('/site-work-orders/edit/{id}', [Site_work_orderController::class, 'edit'])->name('Site_work_order.edit');
        Route::put('/site-work-orders/update', [Site_work_orderController::class, 'update'])->name('Site_work_order.update');
        Route::delete('/site-work-orders/delete/{id}', [Site_work_orderController::class, 'destroy'])->name('Site_work_order.delete');
        Route::post('/site-work-orders/get-types', [Site_work_orderController::class, 'getTypes'])->name('material.getTypes');
        Route::post('/site-work-orders/add-row', [Site_work_orderController::class, 'addRow'])->name('Site_work_order.addRow');
        Route::post('/site-work-orders/get-types', [Site_work_orderController::class, 'getTypes'])->name('Site_work_order.material.getTypes');
        Route::post('/site-work-orders/add-row', [Site_work_orderController::class, 'addRow'])->name('Site_work_order.addRow');
        Route::post('/site-work-orders/upload', [Site_work_orderController::class, 'uploadImage'])->name('upload.attachmentsite');
        Route::post('/site-work-orders/delete-image', [Site_work_orderController::class, 'deleteImage'])
            ->name('delete.imagesite');
        Route::post('/vendor/get-retain', [Site_work_orderController::class, 'getRetain'])
            ->name('vendor.getRetain');

        //Site Work Order Payment
        Route::get('/site-work-order-payments', [Site_work_payController::class, 'index'])->name('Site_work_pay');
        Route::get('/site-work-order-payments/create', [Site_work_payController::class, 'create'])->name('Site_work_pay.create');
        Route::post('/site-work-order-payments/save', [Site_work_payController::class, 'store'])->name('Site_work_pay.store');
        Route::get('/site-work-order-payments/edit/{id}', [Site_work_payController::class, 'edit'])->name('Site_work_pay.edit');
        Route::put('/site-work-pay/{id}', [App\Http\Controllers\Backend\Site_work_payController::class, 'update'])
            ->name('Site_work_pay.update');

        Route::get('/site-work-order-payments/print/{id}', [Site_work_payController::class, 'printPayment'])->name('Site_work_pay.print');

        Route::delete('/site-work-order-payments/delete/{id}', [Site_work_payController::class, 'destroy'])->name('Site_work_pay.delete');
        Route::post('/site-work-order-payments/add-row', [Site_work_payController::class, 'addRow'])->name('Site_work_pay.addRow');
        Route::post('/site-work-order-payments/get-types', [Site_work_payController::class, 'getTypes'])->name('Site_work_pay.material.getTypes');
        Route::post('/site-work-order-payments/add-row', [Site_work_payController::class, 'addRow'])->name('Site_work_pay.addRow');
        Route::get('/site-work-order-payments/make-payment/{id}', [Site_work_payController::class, 'makePayment'])
            ->name('Site_work_pay.makePayment');
        Route::get('/view-payment/{id}', [Site_work_payController::class, 'viewPayment'])->name('Site_work_pay.viewPayment');
        Route::get('/view-payment-data/{id}', [Site_work_payController::class, 'viewPaymentData'])->name('Site_work_pay.viewPayment.data');
        Route::post('/get-account-list', [Site_work_payController::class, 'getAccountList'])
            ->name('ajax.getAccountList');
        Route::post('/ajax/get-balance', [Site_work_payController::class, 'ajaxGetBalance'])
            ->name('ajax.getBalance');

        //owner payment
        Route::get('/owner-payments', [OwnerPaymentController::class, 'index'])->name('Owner_Pay');
        Route::get('/owner-payments/create', [OwnerPaymentController::class, 'create'])->name('Owner_Pay.create');
        Route::post('/owner-payments/save', [OwnerPaymentController::class, 'store'])->name('Owner_Pay.store');
        Route::get('/owner-payments/edit/{id}', [OwnerPaymentController::class, 'edit'])->name('Owner_Pay.edit');
        Route::put('/owner-payments/{id}', [OwnerPaymentController::class, 'update'])
            ->name('Owner_Pay.update');

        Route::delete('/owner-payments/delete/{id}', [OwnerPaymentController::class, 'destroy'])->name('Owner_Pay.delete');

        //Labour Payment
        Route::get('/labour-payments', [Labour_payController::class, 'index'])->name('Labour_work_pay');
        Route::get('/labour-payments/details/{id}', [Labour_payController::class, 'paymentDetails'])->name('Labour_work_pay.details');
        Route::get('/labour-payments/create', [Labour_payController::class, 'create'])->name('Labour_work_pay.create');
        Route::post('/labour-payments/save', [Labour_payController::class, 'store'])->name('Labour_work_pay.store');
        Route::get('/labour-payments/edit/{id}', [Labour_payController::class, 'edit'])->name('Labour_work_pay.edit');
        Route::put('/labour-payments/{id}', [Labour_payController::class, 'update'])
            ->name('Labour_work_pay.update');

        Route::get('/labour-payments/print/{id}', [Labour_payController::class, 'printPayment'])->name('Labour_work_pay.print');

        Route::delete('/labour-payments/delete/{id}', [Labour_payController::class, 'destroy'])->name('Labour_work_pay.delete');
        Route::get('/labour-payments/make-payment/{id}', [Labour_payController::class, 'makePayment'])
            ->name('Labour_work_pay.makePayment');
        Route::get('/labour-view-payment/{id}', [Labour_payController::class, 'viewPayment'])->name('Labour_work_pay.viewPayment');
        Route::get('/labour-view-payment-data/{id}', [Labour_payController::class, 'viewPaymentData'])->name('Labour_work_pay.viewPayment.data');

        //TDS Payment
        Route::get('/tds-payments', [Tds_payController::class, 'index'])->name('Tds_pay');
        Route::get('/tds-payments/create', [Tds_payController::class, 'create'])->name('Tds_pay.create');
        Route::post('/tds-payments/save', [Tds_payController::class, 'store'])->name('Tds_pay.store');
        Route::get('/tds-payments/edit/{id}', [Tds_payController::class, 'edit'])->name('Tds_pay.edit');
        Route::put('/tds-payments/{id}', [App\Http\Controllers\Backend\Tds_payController::class, 'update'])
            ->name('Tds_pay.update');

        Route::delete('/tds-payments/delete/{id}', [Tds_payController::class, 'destroy'])->name('Tds_pay.delete');
        Route::post('/tds-payments/get-types', [Tds_payController::class, 'getTypes'])->name('Tds_pay.material.getTypes');
        Route::post('/tds-payments/add-row', [Tds_payController::class, 'addRow'])->name('Tds_pay.addRow');
        Route::get('/tds-payments/make-payment/{id}', [Tds_payController::class, 'makePayment'])
            ->name('Tds_pay.makePayment');
        Route::get('/view-payment-tds/{id}', [Tds_payController::class, 'viewPayment'])->name('Tds_pay.viewPayment');
        Route::get('/view-payment-data-tds/{id}', [Tds_payController::class, 'viewPaymentData'])->name('Tds_pay.viewPayment.data');


        //Partner Investor Payment
        Route::prefix('payments/{type}')->whereIn('type', ['partner', 'investor'])->group(function () {
        Route::get('/', [Partner_payController::class, 'index'])->name('Partner_pay');
        Route::get('/mainpay/{partner}', [Partner_payController::class, 'mainpay'])->name('Partner_pay.mainpay');
        Route::get('/create', [Partner_payController::class, 'create'])->name('Partner_pay.create');
        Route::post('/store', [Partner_payController::class, 'store'])->name('Partner_pay.store');
        Route::get('/edit/{id}', [Partner_payController::class, 'edit'])->name('Partner_pay.edit');
        Route::put('/update/{id}', [Partner_payController::class, 'update'])->name('Partner_pay.update');
        Route::delete('/delete/{id}', [Partner_payController::class, 'destroy'])->name('Partner_pay.delete');
        Route::get('/make-payment/{id}', [Partner_payController::class, 'makePayment'])->name('Partner_pay.makePayment');
            });

        //Site Exp Pay
        Route::prefix('site-expenses')
            ->group(function () {

                Route::get('/', [Site_exp_payController::class, 'index'])
                    ->name('Site_exp_pay');

                Route::get('/create', [Site_exp_payController::class, 'create'])
                    ->name('Site_exp_pay.create');

                Route::post('/store', [Site_exp_payController::class, 'store'])
                    ->name('Site_exp_pay.store');

                Route::get('/edit/{id}', [Site_exp_payController::class, 'edit'])
                    ->name('Site_exp_pay.edit');

                Route::put('/update/{id}', [Site_exp_payController::class, 'update'])
                    ->name('Site_exp_pay.update');

                Route::delete('/delete/{id}', [Site_exp_payController::class, 'destroy'])
                    ->name('Site_exp_pay.delete');

                Route::get('/make-payment/{id}', [Site_exp_payController::class, 'makePayment'])
                    ->name('Site_exp_pay.makePayment');

                Route::post(
                    '/employee/salary-details',
                    [Site_exp_payController::class, 'getEmployeeSalary']
                )->name('employee.salary.details');
            });


        //Return Pay
        Route::prefix('return-payments')
            ->group(function () {

                Route::get('/', [Return_PayController::class, 'index'])
                    ->name('Return_pay');

                Route::get('/create', [Return_PayController::class, 'create'])
                    ->name('Return_pay.create');

                Route::post('/store', [Return_PayController::class, 'store'])
                    ->name('Return_pay.store');

                Route::get('/edit/{id}', [Return_PayController::class, 'edit'])
                    ->name('Return_pay.edit');

                Route::put('/update/{id}', [Return_PayController::class, 'update'])
                    ->name('Return_pay.update');

                Route::delete('/delete/{id}', [Return_PayController::class, 'destroy'])
                    ->name('Return_pay.delete');

                Route::get('/make-payment/{id}', [Return_PayController::class, 'makePayment'])
                    ->name('Return_pay.makePayment');

                Route::post('/ajax/get-contractor-invoices', [Return_PayController::class, 'getContractorInvoices'])
                    ->name('ajax.getContractorInvoices');

                Route::post('/ajax/get-supplier-invoices', [Return_PayController::class, 'getSupplierOrder'])
                    ->name('ajax.getSopplierOrder');

                Route::post(
                    '/ajax/get-invoice-pending',
                    [Return_PayController::class, 'getConInvoicesPending']
                )->name('ajax.getInvoicePending');

                Route::post(
                    '/ajax/get-order-pending',
                    [Return_PayController::class, 'getSupOrderPending']
                )->name('ajax.getorderPending');

                Route::post('/ajax/get-order-details', [Return_PayController::class, 'getOrderDetails'])
                    ->name('ajax.getOrderDetails');

                Route::post(
                    '/ajax/get-material-transfer-balance',
                    [Return_PayController::class, 'getMaterialTransferBalance']
                )->name('ajax.getMaterialTransferBalance');
            });


        //Material Payment
        Route::get('/material-payments', [Material_payController::class, 'index'])->name('Material_pay');
        Route::get('/material-payments/mainpay', [Material_payController::class, 'mainpayment'])->name('Material_pay.mainpay');
        Route::get(
            '/material-payments/invoicewise/{supplier}',
            [Material_payController::class, 'invoiceWise']
        )->name('Material_pay.invoicewise');

        Route::get('/material-payments/create', [Material_payController::class, 'create'])->name('Material_pay.create');
        Route::post('/material-payments/save', [Material_payController::class, 'store'])->name('Material_pay.store');
        Route::get('/material-payments/edit/{id}', [Material_payController::class, 'edit'])->name('Material_pay.edit');
        Route::put('/material-payments/update/{id}', [Material_payController::class, 'update'])
            ->name('Material_pay.update');

        Route::delete('/material-payments/delete/{id}', [Material_payController::class, 'destroy'])->name('Material_pay.delete');
        Route::post('/material-payments/add-row', [Material_payController::class, 'addRow'])->name('Material_pay.addRow');
        Route::post('/material-payments/get-types', [Material_payController::class, 'getTypes'])->name('Material_pay.material.getTypes');
        Route::post('/material-payments/add-row', [Material_payController::class, 'addRow'])->name('Material_pay.addRow');
        Route::post('material-pay/get-invoices', [Material_payController::class, 'getInvoicesBySupplier'])->name('Material_pay.getInvoicesBySupplier');
        Route::post('material-pay/get-debit-by-supplier', [Material_payController::class, 'getDebitBySupplier'])->name('Material_pay.getDebitBySupplier');



        //Rejected Material
        Route::get('/return-materials', [Rejected_MaterialController::class, 'index'])->name('Rejected_Material');
        Route::get('/return-materials/create', [Rejected_MaterialController::class, 'create'])->name('Rejected_Material.create');
        Route::post('/return-materials/get-invoice', [Rejected_MaterialController::class, 'getInvoice'])
            ->name('Rejected_Material.getInvoice');
        Route::post('/get-invoice-details', [Rejected_MaterialController::class, 'getInvoiceDetails'])
            ->name('Rejected_Material.getInvoiceDetails');

        Route::post('/return-materials/save', [Rejected_MaterialController::class, 'store'])->name('Rejected_Material.store');
        Route::get('/return-materials/edit/{id}', [Rejected_MaterialController::class, 'edit'])->name('Rejected_Material.edit');
        Route::put('/return-materials/update', [Rejected_MaterialController::class, 'update'])->name('Rejected_Material.update');
        Route::delete('/return-materials/delete/{id}', [Rejected_MaterialController::class, 'destroy'])->name('Rejected_Material.delete');
        Route::post('/material-invoices/get-types', [Rejected_MaterialController::class, 'getTypes'])->name('material.getTypes');
        Route::post('/return-materials/add-row', [Rejected_MaterialController::class, 'addRow'])->name('Rejected_Material.addRow');
        Route::post('/return-materials-material/get-types', [Rejected_MaterialController::class, 'getTypes'])->name('Rejected_Material.material.getTypes');
        Route::post('/return-materials/add-row', [Rejected_MaterialController::class, 'addRow'])->name('Rejected_Material.addRow');
        Route::post('/return-materials/upload', [Rejected_MaterialController::class, 'uploadImage'])->name('upload.attachment');

        //Transfer Material
        Route::get('/material-transfers', [Transfer_MaterialController::class, 'index'])->name('Transfer_Material');
        Route::get('/material-transfers/create', [Transfer_MaterialController::class, 'create'])->name('Transfer_Material.create');
        Route::post('/material-transfers/get-invoice', [Transfer_MaterialController::class, 'getInvoice'])
            ->name('Transfer_Material.getInvoice');

        Route::post('/material-transfers/save', [Transfer_MaterialController::class, 'store'])->name('Transfer_Material.store');
        Route::get('/material-transfers/edit/{id}', [Transfer_MaterialController::class, 'edit'])->name('Transfer_Material.edit');
        Route::put('/material-transfers/{id}', [Transfer_MaterialController::class, 'update'])->name('Transfer_Material.update');
        Route::delete('/material-transfers/delete/{id}', [Transfer_MaterialController::class, 'destroy'])->name('Transfer_Material.delete');
        Route::post('/material-invoices/get-types', [Transfer_MaterialController::class, 'getTypes'])->name('material.getTypes');
        Route::post('/material-transfers/add-row', [Transfer_MaterialController::class, 'addRow'])->name('Transfer_Material.addRow');
        Route::post('/material-transfers-material/get-types', [Transfer_MaterialController::class, 'getTypes'])->name('Transfer_Material.material.getTypes');
        Route::post('/material-transfers/upload', [Transfer_MaterialController::class, 'uploadImage'])->name('upload.attachment');
        Route::post('/material/get-available-stock', [Transfer_MaterialController::class, 'getAvailableStock'])->name('material.getAvailableStock');
        Route::post('/material/get-stock', [Transfer_MaterialController::class, 'getMaterialStock'])
            ->name('material.getStock');
        Route::post('/material/get-id', [Transfer_MaterialController::class, 'getMaterialId'])
            ->name('material.getId');

        //Consumption Material
        Route::get('/material-consumptions', [Material_ConsumptionController::class, 'index'])->name('Material_Consumption');
        Route::get('/material-consumptions/create', [Material_ConsumptionController::class, 'create'])->name('Material_Consumption.create');
        Route::post('/material-consumptions/get-invoice', [Material_ConsumptionController::class, 'getInvoice'])
            ->name('Material_Consumption.getInvoice');

        Route::post('/material-consumptions/save', [Material_ConsumptionController::class, 'store'])->name('Material_Consumption.store');
        Route::get('/material-consumptions/edit/{id}', [Material_ConsumptionController::class, 'edit'])->name('Material_Consumption.edit');
        Route::put('/material-consumptions/{id}', [Material_ConsumptionController::class, 'update'])->name('Material_Consumption.update');
        Route::delete('/material-consumptions/delete/{id}', [Material_ConsumptionController::class, 'destroy'])->name('Material_Consumption.delete');
        Route::post('/material-consumptions/get-stock', [Material_ConsumptionController::class, 'getMaterialStock'])
            ->name('Material_Consumption.getStock');

        Route::post('/material-consumptions/add-row', [Material_ConsumptionController::class, 'addRow'])->name('Material_Consumption.addRow');

        // Material Request
        Route::get('/material-requests', [MaterialRequestController::class, 'index'])->name('material_request.list');
        Route::get('/material-requests/create', [MaterialRequestController::class, 'create'])->name('material_request.create');
        Route::post('/material-requests/store', [MaterialRequestController::class, 'store'])->name('material_request.store');
        Route::get('/material-requests/edit/{id}', [MaterialRequestController::class, 'edit'])->name('material_request.edit');
        Route::put('/material-requests/update/{id}', [MaterialRequestController::class, 'update'])->name('material_request.update');
        Route::delete('/material-requests/delete/{id}', [MaterialRequestController::class, 'destroy'])->name('material_request.destroy');

        //Report
        Route::get('/reports/site-labour-payment-report', [Report_Controller::class, 'site_lbr_pay'])->name('report.Site_lbr_pay');
        Route::get('/reports/supplier-payment-report', [Report_Controller::class, 'material_pay'])->name('report.Material_pay');
        Route::get('reports/partners-payment-report/{type}', [Report_Controller::class, 'partner_pay'])->name('report.partner_pay');
        Route::get('reports/material-consumption', [Report_Controller::class, 'material_cunsum'])->name('report.material_cunsum');
        Route::get('reports/return-payment-report', [Report_Controller::class, 'return_pay'])->name('report.return_pay');
        Route::get('reports/daily-diary', [Report_Controller::class, 'dailyDiary'])
            ->name('report.daily_diary');
        Route::get('reports/income-expense-report', [Report_Controller::class, 'incomeExpense'])
            ->name('report.income_expense');
        Route::get('/report/income-expense/export', [ReportController::class, 'exportIncomeExpense'])
            ->name('report.income_expense.export');
        Route::get('reports/tds-report', [Report_Controller::class, 'tdsReport'])
            ->name('report.tds');
        Route::get('reports/material-transfer-report', [Report_Controller::class, 'material_transfer_Report'])
            ->name('report.material_transfer');
        Route::get('reports/material-recieve-report', [Report_Controller::class, 'material_recieve_Report'])
            ->name('report.material_recieve');
        // routes/web.php
        Route::match(['get', 'post'], '/reports/abstract', [Report_Controller::class, 'abstractReport'])->name('report.abstract');
        Route::get('/reports/customer_refund', [Report_Controller::class, 'customerRefund'])->name('reports.customer_refund');
        Route::get('/customer-refund-export', [ReportController::class, 'customerRefundExport'])
            ->name('customer_refund_export');
        Route::get('/reports/lbrpay_report', [Report_Controller::class, 'lbrpay_report'])->name('reports.lbrpay_report');
        Route::get('/reports/stamp_other_expense_report', [Report_Controller::class, 'stampOtherExpense'])
            ->name('reports.stamp_other_expense_report');


        Route::get('backend/reports/stamp-other-expense/{id}/edit', [Report_Controller::class, 'edit'])->name('stampother_expense.edit');
        Route::delete('backend/reports/stamp-other-expense/{id}', [Report_Controller::class, 'destroy'])->name('stampother_expense.destroy');
        Route::get('bank-transaction-report', [Report_Controller::class, 'index'])
            ->name('bank.transaction.report');

        Route::post('bank-transaction-report-data', [Report_Controller::class, 'reportData'])
            ->name('bank.transaction.report.data');

        Route::get('/reports/loan_payment_report', [Report_Controller::class, 'loanreport'])->name('reports.loan_payment_report');

        Route::get('/reports/loan_detail_report/{customer}', [Report_Controller::class, 'loandetail'])->name('reports.loan_detail_report');

        Route::get('/reports/stock_report', [Report_Controller::class, 'StockReport'])->name('reports.stock_report');

        Route::get('/reports/material_purchase_report', [Report_Controller::class, 'MaterialPurchase'])
            ->name('reports.material_purchase_report');

        Route::get('/reports/site_expenses_report', [Report_Controller::class, 'SiteExpense'])->name('reports.site_expenses_report');

        Route::get('/reports/available_flat_report', [Report_Controller::class, 'availableFlats'])->name('reports.available_flat_report');

        Route::get('/reports/land_expense_report', [Report_Controller::class, 'LandExpenseReport'])->name('reports.land_expense_report');

        Route::get('/reports/rera_report', [Report_Controller::class, 'ReraReport'])
            ->name('reports.rera_report');

        Route::get('/reports/gst_report', [Report_Controller::class, 'GstReport'])->name('reports.gst_report');

        Route::prefix('reports/customer-payment')->group(function () {

            Route::get('/customer', [Report_Controller::class, 'customerpaymentReport'])
                ->name('reports.customer_payment.customer');

            Route::get('/bank', [Report_Controller::class, 'CustomerbankPayment'])
                ->name('reports.customer_payment.bank');

            Route::get('/self', [Report_Controller::class, 'CustomerSelfPayment'])
                ->name('reports.customer_payment.self');
        });

        Route::get('reports/customer-payment-report', [Report_Controller::class, 'customerReport'])->name('reports.customer.payment.report');

        Route::post('customer-payment-report-data', [Report_Controller::class, 'customerReportData'])->name('customer.payment.report.data');

        Route::get('/daily-work-report', [DailyWorkReportController::class, 'index'])
            ->name('dailyworkreport.index');

        Route::delete('/daily-work-report/{id}', [DailyWorkReportController::class, 'destroy'])
            ->name('dailyworkreport.destroy');
                
        //Scheme
        Route::get('/schemes', [SchemeController::class, 'getSchemenames'])->name('schemes.schemenames');
        Route::post('/set-session', [SchemeController::class, 'setSession'])->name('schemes.setSession');
        Route::post('/scheme/add-row', [SchemeController::class, 'addRow'])->name('scheme.addRow');
        Route::post('/scheme/flat-details', [SchemeController::class, 'getFlatDetails'])->name('scheme.flatDetails');
        Route::get('/get-flat-types', [SchemeController::class, 'getFlatTypes'])->name('get.flat.types');

        //scheme management 
        // customer demand raise
        Route::get('/customer-demand-raise', [Demand_raiseController::class, 'index'])->name('demand_raise');
        Route::get('/customer-demand-raise/create', [Demand_raiseController::class, 'create'])->name('demand_raise.create');
        Route::post('/customer-demand-raise/save', [Demand_raiseController::class, 'store'])->name('demand_raise.store');
        Route::get('/customer-demand-raise/edit/{id}', [Demand_raiseController::class, 'edit'])->name('demand_raise.edit');
        Route::put('/customer-demand-raise/update', [Demand_raiseController::class, 'update'])->name('demand_raise.update');
        Route::delete('/customer-demand-raise/delete/{id}', [Demand_raiseController::class, 'destroy'])->name('demand_raise.delete');
        Route::get('/get-flatnos-by-wing', [Demand_raiseController::class, 'getFlatNosByWing'])
            ->name('get.flatnos.by.wing');
        Route::get('/get-flat-details', [Demand_raiseController::class, 'getFlatDetails'])
            ->name('get.flat.details');

        // Add Bill
        Route::get('/add-bill', [Add_BillController::class, 'index'])->name('Add_bill');
        Route::get('/add-bill/create', [Add_BillController::class, 'create'])->name('Add_bill.create');
        Route::post('/add-bill/save', [Add_BillController::class, 'store'])->name('Add_bill.store');
        Route::get('/add-bill/edit/{id}', [Add_BillController::class, 'edit'])->name('Add_bill.edit');
        Route::put('/add-bill/update', [Add_BillController::class, 'update'])->name('Add_bill.update');
        Route::delete('/add-bill/delete/{id}', [Add_BillController::class, 'destroy'])->name('Add_bill.delete');
        Route::post('/add-bill/upload', [Add_BillController::class, 'uploadImage'])->name('upload.attachmentbill');
        Route::post('/add-bill/delete-image', [Add_BillController::class, 'deleteImage'])
            ->name('delete.imagebill');

        // Booking Cancel
        Route::get('/cancel-booking', [Booking_CancelController::class, 'index'])->name('booking_cancel');
        Route::get('/cancel-booking/create', [Booking_CancelController::class, 'create'])->name('booking_cancel.create');
        Route::post('/cancel-booking/save', [Booking_CancelController::class, 'store'])->name('booking_cancel.store');
        Route::get('/cancel-booking/edit/{id}', [Booking_CancelController::class, 'edit'])->name('booking_cancel.edit');
        Route::put('/cancel-booking/update', [Booking_CancelController::class, 'update'])->name('booking_cancel.update');
        Route::delete('/cancel-booking/delete/{id}', [Booking_CancelController::class, 'destroy'])->name('booking_cancel.delete');

        Route::post('/ajax/booking-date', [Booking_CancelController::class, 'getBookingDate'])
            ->name('ajax.booking.date');

        Route::post('/ajax/booking-detail', [Booking_CancelController::class, 'getBookingDetail'])
            ->name('ajax.booking.detail');



        ////////////////////////////////////////////////////////////////////////////////////////

        // Loan 
        Route::get('/loan_management', [LoanManagementController::class, 'index'])->name('loan_management');

        Route::get('/loan_management/create', [LoanManagementController::class, 'create'])->name('loan_management.create');

        Route::post('/loan_management/store', [LoanManagementController::class, 'store'])->name('loan_management.store');

        Route::get('/loan_management/edit/{id}', [LoanManagementController::class, 'edit'])->name('loan_management.edit');

        Route::put('/loan_management/update', [LoanManagementController::class, 'update'])->name('loan_management.update');

        Route::delete('/loan_management/delete/{id}', [LoanManagementController::class, 'destroy'])->name('loan_management.delete');


        Route::get('/loan/get-pending', [LoanManagementController::class, 'getPendingLoan'])
            ->name('loan.getPending');

        Route::post('/ajax/cashmethod', [LoanManagementController::class, 'cashMethod'])
            ->name('ajax.cashmethod');
        Route::post('/get-balanceLoan', [LoanManagementController::class, 'getLoanBalances'])->name('get.balanceLoan');
        Route::post('/get-pay-type', [LoanManagementController::class, 'getPayType'])
            ->name('get.pay.type');
        Route::post('/get-receivepaytype', [LoanManagementController::class, 'getReceivedPaytype'])
            ->name('get.receivepaytype');

        Route::get('/loan-management/{customer}', [LoanManagementController::class, 'detail'])->name('loan_detail');

        Route::post('/ajax/paytypeaccounts', [LoanManagementController::class, 'getAccounts'])->name('ajax.paytypeaccounts');


        // Account Transfer

        Route::get('/account_transfer', [AccountTransferController::class, 'index'])->name('account_transfer');

        Route::get('/account_transfer/create', [AccountTransferController::class, 'create'])->name('account_transfer.create');

        Route::post('/account_transfer/store', [AccountTransferController::class, 'store'])->name('account_transfer.store');

        Route::get('/account_transfer/edit/{id}', [AccountTransferController::class, 'edit'])->name('account_transfer.edit');

        Route::put('/account_transfer/update', [AccountTransferController::class, 'update'])->name('account_transfer.update');

        Route::delete('/account_transfer/delete/{id}', [AccountTransferController::class, 'destroy'])->name('account_transfer.delete');

        Route::post('/get-account-balance', [AccountTransferController::class, 'getAccountBalance'])
            ->name('get.account.balance');

        // Bank Reconciliation
        //   Route::get('/bank_reconciliation', [BankReconciliationController::class, 'index'])->name('bank_reconciliation');

        //   Route::post('bank_reconciliation/clear', [BankReconciliationController::class, 'clear'])
        //       ->name('bank_reconciliation.clear');

        //   Route::post('bank_reconciliation/bounce', [BankReconciliationController::class, 'bounce'])
        //       ->name('bank_reconciliation.bounce');

        Route::get('bank_reconciliation', [BankReconciliationController::class, 'index'])
            ->name('bank_reconciliation');

        Route::post('bank_reconciliation/clear', [BankReconciliationController::class, 'clearCheque'])
            ->name('bank_reconciliation.clear');

        Route::post('bank_reconciliation/bounce', [BankReconciliationController::class, 'bounceCheque'])
            ->name('bank_reconciliation.bounce');


        // Customer Payment

        Route::get('/customer_payment', [CustomerPaymentController::class, 'index'])->name('customer_payment');

        Route::get('/customer_payment/create', [CustomerPaymentController::class, 'create'])->name('customer_payment.create');

        Route::post('/customer_payment/store', [CustomerPaymentController::class, 'store'])->name('customer_payment.store');

        Route::get('/customer_payment/edit/{id}', [CustomerPaymentController::class, 'edit'])->name('customer_payment.edit');

        Route::put('/customer_payment/update', [CustomerPaymentController::class, 'update'])->name('customer_payment.update');

        Route::delete('/customer_payment/delete/{id}', [CustomerPaymentController::class, 'destroy'])->name('customer_payment.delete');
        Route::post('/get-wing-no', [CustomerPaymentController::class, 'getWingNo'])
            ->name('get.wing.no');

        Route::post('/booking/get-flats', [CustomerPaymentController::class, 'getFlats'])->name('booking.getFlats');

        Route::post('/get-customer', [CustomerPaymentController::class, 'getCustomer'])->name('flats.getCustomer');
        Route::post('/ajax/account-by-payment', [CustomerPaymentController::class, 'accountByPayment'])
            ->name('ajax.account.by.payment');
        Route::post('/ajax/get-booking-pending', [CustomerPaymentController::class, 'getBookingPending'])
            ->name('ajax.get.booking.pending');

        Route::get('/customer-payment/{id}', [CustomerPaymentController::class, 'show'])->name('customer_payment.show');
        Route::get('/customer_payment/print/{id}', [CustomerPaymentController::class, 'receipt'])
    ->name('customer_payment.print');

    //    Route::get('/customer_payment/{id}', [CustomerPaymentController::class, 'receipt'])
    // ->name('customer_payment.receipt');


        // Post Dated Cheque

        Route::get('/post_dated_cheque', [PostDateChequeController::class, 'index'])->name('post_dated_cheque');

        Route::get('/post_dated_cheque/create', [PostDateChequeController::class, 'create'])->name('post_dated_cheque.create');

        Route::post('/post_dated_cheque/store', [PostDateChequeController::class, 'store'])->name('post_dated_cheque.store');

        Route::get('/post_dated_cheque/edit/{id}', [PostDateChequeController::class, 'edit'])->name('post_dated_cheque.edit');

        Route::put('/post_dated_cheque/update', [PostDateChequeController::class, 'update'])->name('post_dated_cheque.update');

        Route::delete('/post_dated_cheque/delete/{id}', [PostDateChequeController::class, 'destroy'])->name('post_dated_cheque.delete');
        Route::post('/get-postwing-no', [PostDateChequeController::class, 'getWingNoPosted'])
            ->name('get.postwing.no');

        Route::post('/postbooking/get-flats', [PostDateChequeController::class, 'getFlatsPost'])->name('postbooking.getFlats');

        Route::post('/get-customerpost', [PostDateChequeController::class, 'getCustomerPost'])->name('flats.getCustomerpost');
        Route::post('/ajax/account-by-paymentpost', [PostDateChequeController::class, 'accountByPaymentPost'])
            ->name('ajax.account.by.paymentpost');
        Route::post('/ajax/get-booking-pendingpost', [PostDateChequeController::class, 'getBookingPendingPost'])
            ->name('ajax.get.booking.pendingpost');

        // Customer Refund
        Route::get('/customer_refund', [CustomerRefundController::class, 'index'])->name('customer_refund');

        Route::get('/customer_refund/create', [CustomerRefundController::class, 'create'])->name('customer_refund.create');

        Route::post('/customer_refund/store', [CustomerRefundController::class, 'store'])->name('customer_refund.store');

        Route::get('/customer_refund/edit/{id}', [CustomerRefundController::class, 'edit'])->name('customer_refund.edit');

        Route::put('/customer_refund/update', [CustomerRefundController::class, 'update'])->name('customer_refund.update');

        Route::delete('/customer_refund/delete/{id}', [CustomerRefundController::class, 'destroy'])->name('customer_refund.delete');
        Route::post('/get-booking-customer', [CustomerRefundController::class, 'getBookingCustomer'])
            ->name('get.booking.customer');
        Route::post('/get-total-paid', [CustomerRefundController::class, 'getTotalPaid'])
            ->name('get.total.paid');
        Route::post('/ajax/payment-method', [CustomerRefundController::class, 'paymentMethod'])
            ->name('ajax.payment.method');

        Route::post('/ajax/get-account-options', [CustomerRefundController::class, 'getAccountOptions'])->name('ajax.getAccountOptions');

        Route::post('/get-balance', [CustomerRefundController::class, 'getCustomerRefundBalance'])->name('get.balance');

        // Stamp Other Expenses
        Route::get('/stamp_other_expenses', [StampExpensesController::class, 'index'])->name('stamp_other_expenses');

        Route::get('/stamp_other_expenses/create', [StampExpensesController::class, 'create'])->name('stamp_other_expenses.create');

        Route::post('/stamp_other_expenses/store', [StampExpensesController::class, 'store'])->name('stamp_other_expenses.store');

        Route::get('/stamp_other_expenses/edit/{id}', [StampExpensesController::class, 'edit'])->name('stamp_other_expenses.edit');

        Route::put('/stamp_other_expenses/update', [StampExpensesController::class, 'update'])->name('stamp_other_expenses.update');

        Route::delete('/stamp_other_expenses/delete/{id}', [StampExpensesController::class, 'destroy'])->name('stamp_other_expenses.delete');

        Route::post('/get-emp-details', [StampExpensesController::class, 'getEmpDetails']);

        Route::post('/get-stampexpense', [StampExpensesController::class, 'getStampExpenses'])
            ->name('get.stampexpense');

        Route::post('/ajax/get-stamp-expenses', [StampExpensesController::class, 'getStampExpenseOptions'])->name('ajax.getStampExpenses');

        Route::post('/get-balanceExpenses', [StampExpensesController::class, 'getStampExpenseBalances'])->name('get.balanceExpenses');
        Route::post('/expenses/add-new-type', [StampExpensesController::class, 'addNewExpenseType'])->name('expenses.addNewType');

        // Land Expenses
        Route::get('/land_expenses', [LandExpensesController::class, 'index'])->name('land_expenses');

        Route::get('/land_expenses/create', [LandExpensesController::class, 'create'])->name('land_expenses.create');

        Route::post('/land_expenses/store', [LandExpensesController::class, 'store'])->name('land_expenses.store');

        Route::get('/land_expenses/edit/{id}', [LandExpensesController::class, 'edit'])->name('land_expenses.edit');

        Route::put('/land_expenses/update', [LandExpensesController::class, 'update'])->name('land_expenses.update');

        Route::delete('/land_expenses/delete/{id}', [LandExpensesController::class, 'destroy'])->name('land_expenses.delete');

        Route::post('/get-title-land-exp', [LandExpensesController::class, 'getTitleLandExp'])
            ->name('get.title.land.exp');

        Route::post('/ajax/get-land-expenses', [LandExpensesController::class, 'getLandExpenseOptions'])->name('ajax.getLandExpenses');

        Route::post('/get-landbalanceExpenses', [LandExpensesController::class, 'getLandExpenseBalances'])->name('get.landbalanceExpenses');

        ///////////////////////////////////////////////////////////////

        //Customer Booking

        Route::get('/customer_booking', [CustomerBookingController::class, 'index'])->name('customer_booking');

        Route::get('/customer_booking/create', [CustomerBookingController::class, 'create'])->name('customer_booking.create');

        Route::post('/customer_booking/store', [CustomerBookingController::class, 'store'])->name('customer_booking.store');

        Route::get('/customer_booking/edit/{id}', [CustomerBookingController::class, 'edit'])->name('customer_booking.edit');

        Route::put('/customer_booking/update', [CustomerBookingController::class, 'update'])->name('customer_booking.update');

        Route::delete('/customer_booking/delete/{id}', [CustomerBookingController::class, 'destroy'])->name('customer_booking.delete');

        Route::post('/get-flats-no', [CustomerBookingController::class, 'getFlatsNo'])
            ->name('get.flats.no');

        Route::post('/get-flat-info', [CustomerBookingController::class, 'getFlatInfo'])
            ->name('get.flat.info');

        Route::post('/ajax/account-by-bookingpayment', [CustomerBookingController::class, 'accountByBookingPayment'])->name('ajax.account.by.bookingpayment');

        Route::get('customer-booking/{id}/reminder', [CustomerBookingController::class, 'reminder'])->name('customer_booking.reminder');



        Route::post('/customer-booking/reminder-letter', [CustomerBookingController::class, 'show'])->name('customer_booking.reminder.letter');
        Route::get('/customer-booking/{id}/payment-schedule', [CustomerBookingController::class, 'paymentSchedule'])->name('customer_booking.payment_schedule');


        // Company setting Controller
        Route::get('/company-setting', [CompanySettingController::class, 'edit'])->name('companysettings');
        Route::post('/company-setting', [CompanySettingController::class, 'update'])->name('companysettings.update');


        Route::get('/agency/list', function () {
            return \App\Models\Backend\Agency::select('ID', 'Name')
                ->orderBy('Name')
                ->get();
        })->name('Agency.list');


        //EmployeeAdvanceController
        // Route::get('/EmployeeAdvance', [EmployeeAdvanceController::class, 'index'])
        //     ->name('EmployeeAdvance');

        // Route::get('/EmployeeAdvance/create', [EmployeeAdvanceController::class, 'create'])
        //     ->name('EmployeeAdvance.create');
        // Route::post('/EmployeeAdvance/save', [EmployeeAdvanceController::class, 'store'])
        //     ->name('EmployeeAdvance.store');

        // Route::get('/EmployeeAdvance/edit/{id}', [EmployeeAdvanceController::class, 'edit'])
        //     ->name('EmployeeAdvance.edit');
        // Route::put('/EmployeeAdvance/update', [EmployeeAdvanceController::class, 'update'])
        //     ->name('EmployeeAdvance.update');

        // Route::delete('/EmployeeAdvance/delete/{id}', [EmployeeAdvanceController::class, 'destroy'])
        //     ->name('EmployeeAdvance.destroy');
        // // Route::delete('/EmployeeAdvance/delete/{id}', [EmployeeAdvanceController::class, 'destroy'])->name('EmployeeAdvance.delete');

        // Route::get(
        //     'backend/EmployeeAdvance/employee_advance_payment/{id}',
        //     [EmployeeAdvanceController::class, 'employeeAdvancePayment']
        // )->name('EmployeeAdvance.payment');

        // Route::post(
        //     '/employee-advance-payment/store',
        //     [EmployeeAdvanceController::class, 'storePayment']
        // )->name('EmployeeAdvance.storePayment');

        //AttendanceMaster Controller
        // Route::get('/AttendanceMaster', [AttendanceMasterController::class, 'index'])->name('AttendanceMaster');
        // Route::get('/AttendanceMaster/create', [AttendanceMasterController::class, 'create'])->name('AttendanceMaster.create');
        // Route::post('/AttendanceMaster/save', [AttendanceMasterController::class, 'store'])->name('AttendanceMaster.store');
        // Route::get('/AttendanceMaster/edit/{id}', [AttendanceMasterController::class, 'edit'])->name('AttendanceMaster.edit');
        // Route::put('/AttendanceMaster/update', [AttendanceMasterController::class, 'update'])->name('AttendanceMaster.update');
        // Route::delete('/AttendanceMaster/delete/{id}', [AttendanceMasterController::class, 'destroy'])->name('AttendanceMaster.destroy');

        //SalaryMasterController Controller
        // Route::get('/SalaryMaster', [SalaryMasterController::class, 'index'])->name('SalaryMaster');
        // Route::get('/SalaryMaster/create', [SalaryMasterController::class, 'create'])->name('SalaryMaster.create');
        // Route::post('/SalaryMaster/save', [SalaryMasterController::class, 'store'])->name('SalaryMaster.store');
        // Route::get('/SalaryMaster/edit/{id}', [SalaryMasterController::class, 'edit'])->name('SalaryMaster.edit');
        // Route::put('/SalaryMaster/update', [SalaryMasterController::class, 'update'])->name('SalaryMaster.update');
        // Route::delete('/SalaryMaster/delete/{id}', [SalaryMasterController::class, 'destroy'])->name('SalaryMaster.destroy');
        // Route::get('/get-employee-salary/{id}', [SalaryMasterController::class, 'getEmployeeSalary']);
        // Route::get('/salary-slip/{id}', [SalaryMasterController::class, 'printSlip'])
        //     ->name('SalaryMaster.print');

            // Enquiry
        Route::get('/enquiries',[EnquiryController::class,'index'])
            ->name('enquiries.index');

        Route::get('/enquiries/edit/{id}',[EnquiryController::class,'edit'])
            ->name('enquiries.edit');

        Route::put('/enquiries/update/{id}',[EnquiryController::class,'update'])
            ->name('enquiries.update');

        Route::delete('/enquiries/delete/{id}',[EnquiryController::class,'destroy'])
            ->name('enquiries.destroy');

        Route::get('enquiries/create',[EnquiryController::class,'create'])->name('enquiries.create');

        Route::post('enquiries',[EnquiryController::class,'store'])->name('enquiries.store');

            //EmployeeAdvanceController
     Route::get('/EmployeeAdvance', [EmployeeAdvanceController::class, 'index'])
            ->name('EmployeeAdvance')
            ->middleware('hasPermission:EmployeeAdvance_read');
            Route::get('/EmployeeAdvance/create', [EmployeeAdvanceController::class, 'create'])
            ->name('EmployeeAdvance.create')
            ->middleware('hasPermission:create_EmployeeAdvance');

        Route::post('/EmployeeAdvance/save', [EmployeeAdvanceController::class, 'store'])
            ->name('EmployeeAdvance.store');

        Route::get('/EmployeeAdvance/edit/{id}', [EmployeeAdvanceController::class, 'edit'])
            ->name('EmployeeAdvance.edit')
            ->middleware('hasPermission:edit_EmployeeAdvance');

        Route::put('/EmployeeAdvance/update', [EmployeeAdvanceController::class, 'update'])
            ->name('EmployeeAdvance.update');

        Route::delete('/EmployeeAdvance/delete/{id}', [EmployeeAdvanceController::class, 'destroy'])
            ->name('EmployeeAdvance.destroy')
            ->middleware('hasPermission:delete_EmployeeAdvance');
            Route::delete('/EmployeeAdvance/delete/{id}', [EmployeeAdvanceController::class, 'destroy'])->name('EmployeeAdvance.delete')->middleware('hasPermission:delete_Shift');

        Route::get('/backend/EmployeeAdvance/employee_advance_payment/{id}',
            [EmployeeAdvanceController::class, 'employeeAdvancePayment']
        )->name('EmployeeAdvance.payment');

        Route::post('/EmployeeAdvancePayment/save', [EmployeeAdvanceController::class, 'storePayment'])
            ->name('EmployeeAdvancePayment.store');
        //     Route::post('/employee-advance-payment/store',
        //     [EmployeeAdvanceController::class, 'storePayment']
        //  )->name('EmployeeAdvance.storePayment');

        Route::get( '/EmployeeAdvance/history/{id}', [EmployeeAdvanceController::class,'paymentHistory']
        )->name('EmployeeAdvance.history');    

        Route::get('/salary/generate',
            [SalaryMasterController::class,'generateSalaryPage'])
            ->name('salary.generate.page');

        Route::post('/salary/generate',
            [SalaryMasterController::class,'generateSalary'])
            ->name('salary.generate');

        Route::get('/salary-report',
            [SalaryMasterController::class,'salaryReport'])
            ->name('salary.report');    
                
           Route::get(
                '/SalaryReport/data',
                [SalaryMasterController::class, 'salaryReportData']
            )->name('SalaryReport.data');  
            
            Route::post(
                '/salary-summary',
                [SalaryMasterController::class,'getSalarySummary']
            )->name('salary.summary');

            Route::post(
                '/salary-delete-month',
                [SalaryMasterController::class,'deleteMonthSalary']
            )->name('salary.delete.month');

            Route::get(
                '/salary/generated-month-list',
                [SalaryMasterController::class,'generatedMonthList']
            )->name('salary.generated.month.list');

            

     // Shift Controller
        Route::get('/Shift', [ShiftController::class, 'index'])->name('Shift')->middleware('hasPermission:Shift_read');
         Route::get('/Shift/create', [ShiftController::class, 'create'])->name('Shift.create')->middleware('hasPermission:create_Shift');
        Route::post('/Shift/save', [ShiftController::class, 'store'])->name('Shift.store');
        Route::get('/Shift/edit/{id}', [ShiftController::class, 'edit'])->name('Shift.edit')->middleware('hasPermission:edit_Shift');
         Route::put('/Shift/update', [ShiftController::class, 'update'])->name('Shift.update');
        Route::delete('/Shift/delete/{id}', [ShiftController::class, 'destroy'])->name('Shift.delete')->middleware('hasPermission:delete_Shift');


 //AttendanceMaster Controller
        Route::get('/AttendanceMaster', [AttendanceMasterController::class, 'index'])->name('AttendanceMaster')->middleware('hasPermission:AttendanceMaster_read');
         Route::get('/AttendanceMaster/create', [AttendanceMasterController::class, 'create'])->name('AttendanceMaster.create')->middleware('hasPermission:create_AttendanceMaster');
        Route::post('/AttendanceMaster/save', [AttendanceMasterController::class, 'store'])->name('AttendanceMaster.store');
        Route::get('/AttendanceMaster/edit/{id}', [AttendanceMasterController::class, 'edit'])->name('AttendanceMaster.edit')->middleware('hasPermission:edit_AttendanceMaster');
         Route::put('/AttendanceMaster/update', [AttendanceMasterController::class, 'update'])->name('AttendanceMaster.update');
       Route::delete('/AttendanceMaster/delete/{id}', [AttendanceMasterController::class, 'destroy'])->name('AttendanceMaster.destroy')
       ->middleware('hasPermission:delete_AttendanceMaster');
       Route::post('/AttendanceMaster/import', [AttendanceMasterController::class, 'import'])
    ->name('attendance.import');


       //SalaryMasterController Controller
        Route::get('/SalaryMaster', [SalaryMasterController::class, 'index'])->name('SalaryMaster')->middleware('hasPermission:SalaryMaster_read');
        Route::get('/SalaryMaster/create', [SalaryMasterController::class, 'create'])->name('SalaryMaster.create')->middleware('hasPermission:create_SalaryMaster');
        Route::post('/SalaryMaster/save', [SalaryMasterController::class, 'store'])->name('SalaryMaster.store');
        Route::get('/SalaryMaster/edit/{id}', [SalaryMasterController::class, 'edit'])->name('SalaryMaster.edit')->middleware('hasPermission:edit_SalaryMaster');
        Route::put('/SalaryMaster/update', [SalaryMasterController::class, 'update'])->name('SalaryMaster.update');
        Route::delete('/SalaryMaster/delete/{id}', [SalaryMasterController::class, 'destroy'])->name('SalaryMaster.destroy')->middleware('hasPermission:delete_SalaryMaster');
        Route::get('salary/get-details/{empId}/{month}/{year}/{id?}',[SalaryMasterController::class, 'getSalaryDetails'])->name('salary.get.details');
       Route::get('salary-slip/{id}',[SalaryMasterController::class, 'salarySlip'])->name('SalaryMaster.slip');
       
       Route::get('/UserShift', [UserShiftAssignmentController::class, 'index'])
            ->name('UserShift')
            ->middleware('hasPermission:UserShift_read');

        Route::get('/UserShift/create', [UserShiftAssignmentController::class, 'create'])
            ->name('UserShift.create')
            ->middleware('hasPermission:create_UserShift');

        Route::post('/UserShift/save', [UserShiftAssignmentController::class, 'store'])
            ->name('UserShift.store');

        Route::delete('/UserShift/delete/{id}', [UserShiftAssignmentController::class, 'destroy'])
            ->name('UserShift.delete')
            ->middleware('hasPermission:delete_UserShift');
        Route::get('/UserShift/edit/{id}', [UserShiftAssignmentController::class, 'edit'])
            ->name('UserShift.edit')
            ->middleware('hasPermission:edit_UserShift');

        Route::put('/UserShift/update', [UserShiftAssignmentController::class, 'update'])
            ->name('UserShift.update');

        // Late Mark Calculation

        Route::get('/LateMarkCalculation', [LateMarkCalculationController::class, 'index'])
        ->name('LateMarkCalculation')
        ->middleware('hasPermission:LateMarkCalculation_read');

        Route::get('/LateMarkCalculation/create', [LateMarkCalculationController::class, 'create'])
        ->name('LateMarkCalculation.create')
        ->middleware('hasPermission:create_LateMarkCalculation');

        Route::post('/LateMarkCalculation/save', [LateMarkCalculationController::class, 'store'])
        ->name('LateMarkCalculation.store');

        Route::get('/LateMarkCalculation/edit/{id}', [LateMarkCalculationController::class, 'edit'])
        ->name('LateMarkCalculation.edit')
        ->middleware('hasPermission:edit_LateMarkCalculation');

        Route::put('/LateMarkCalculation/update', [LateMarkCalculationController::class, 'update'])
        ->name('LateMarkCalculation.update');

        Route::delete('/LateMarkCalculation/delete/{id}', [LateMarkCalculationController::class, 'destroy'])
        ->name('LateMarkCalculation.delete')
        ->middleware('hasPermission:delete_LateMarkCalculation');

        //lead
        Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
        Route::get('/leads/create', [LeadController::class, 'create'])->name('leads.create');
        Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
        Route::get('/leads/{id}/edit', [LeadController::class, 'edit'])->name('leads.edit');
        Route::put('/leads/{id}', [LeadController::class, 'update'])->name('leads.update');
        Route::delete('/leads/{id}', [LeadController::class, 'destroy'])->name('leads.destroy');

         // CallOutcome Controller
        Route::get('/CallOutcome', [CallOutcomeController::class, 'index'])->name('CallOutcome')->middleware('hasPermission:CallOutcome_read');
        Route::get('/CallOutcome/create', [CallOutcomeController::class, 'create'])->name('CallOutcome.create')->middleware('hasPermission:create_CallOutcome');
        Route::post('/CallOutcome/save', [CallOutcomeController::class, 'store'])->name('CallOutcome.store');
        Route::get('/CallOutcome/edit/{id}', [CallOutcomeController::class, 'edit'])->name('CallOutcome.edit')->middleware('hasPermission:edit_CallOutcome');
        Route::put('/CallOutcome/update', [CallOutcomeController::class, 'update'])->name('CallOutcome.update');
        Route::delete('/CallOutcome/delete/{id}', [CallOutcomeController::class, 'destroy'])->name('CallOutcome.delete')->middleware('hasPermission:delete_CallOutcome');
    
         // CallStatus Controller
        Route::get('/CallStatus', [CallStatusController::class, 'index'])->name('CallStatus')->middleware('hasPermission:CallStatus_read');
        Route::get('/CallStatus/create', [CallStatusController::class, 'create'])->name('CallStatus.create')->middleware('hasPermission:create_CallStatus');
        Route::post('/CallStatus/save', [CallStatusController::class, 'store'])->name('CallStatus.store');
        Route::get('/CallStatus/edit/{id}', [CallStatusController::class, 'edit'])->name('CallStatus.edit')->middleware('hasPermission:edit_CallStatus');
        Route::put('/CallStatus/update', [CallStatusController::class, 'update'])->name('CallStatus.update');
        Route::delete('/CallStatus/delete/{id}', [CallStatusController::class, 'destroy'])->name('CallStatus.delete')->middleware('hasPermission:delete_CallStatus');
    
        // CallPurpose Controller
        Route::get('/CallPurpose', [CallPurposeController::class, 'index'])->name('CallPurpose')->middleware('hasPermission:CallPurpose_read');
        Route::get('/CallPurpose/create', [CallPurposeController::class, 'create'])->name('CallPurpose.create')->middleware('hasPermission:create_CallPurpose');
        Route::post('/CallPurpose/save', [CallPurposeController::class, 'store'])->name('CallPurpose.store');
        Route::get('/CallPurpose/edit/{id}', [CallPurposeController::class, 'edit'])->name('CallPurpose.edit')->middleware('hasPermission:edit_CallPurpose');
        Route::put('/CallPurpose/update', [CallPurposeController::class, 'update'])->name('CallPurpose.update');
        Route::delete('/CallPurpose/delete/{id}', [CallPurposeController::class, 'destroy'])->name('CallPurpose.delete')->middleware('hasPermission:delete_CallPurpose');


        // ============================================
            // LEAD CALL (the "Add Call" provision on the Lead detail page)
            // ============================================
            Route::post('/LeadCall/store', [LeadCallController::class, 'store'])->name('LeadCall.store');
            Route::delete('/LeadCall/delete/{id}', [LeadCallController::class, 'destroy'])->name('LeadCall.delete');
            Route::patch('/CreateLead/stage/{lead}', [CreateLeadController::class, 'updateStage'])->name('CreateLead.stage');
            Route::put('/LeadCall/update/{id}', [LeadCallController::class, 'update'])->name('LeadCall.update');
            Route::patch('/LeadCall/toggle-complete/{id}', [LeadCallController::class, 'toggleComplete'])->name('LeadCall.toggleComplete');

            //CreateLead
            Route::get('/CreateLead', [CreateLeadController::class, 'index'])->name('CreateLead')->middleware('hasPermission:CreateLead_read');
            Route::get('/CreateLead/create', [CreateLeadController::class, 'create'])->name('CreateLead.create')->middleware('hasPermission:create_CreateLead');
            Route::post('/CreateLead/store', [CreateLeadController::class, 'store'])->name('CreateLead.store');
            Route::get('/CreateLead/edit/{id}', [CreateLeadController::class, 'edit'])->name('CreateLead.edit')->middleware('hasPermission:edit_CreateLead');
            Route::put('/CreateLead/update', [CreateLeadController::class, 'update'])->name('CreateLead.update');
            Route::get('/CreateLead/show/{id}', [CreateLeadController::class, 'show'])->name('CreateLead.show');
            Route::delete('/CreateLead/delete/{id}', [CreateLeadController::class, 'destroy'])->name('CreateLead.delete')->middleware('hasPermission:delete_CreateLead');
            Route::delete('/CreateLead/bulk-delete', [CreateLeadController::class, 'bulkDestroy'])->name('CreateLead.bulkDelete')->middleware('hasPermission:delete_CreateLead');
       
            //LeadSource

            Route::get('/LeadSource',[LeadSourceController::class,'index'])->name('LeadSource')->middleware('hasPermission:LeadSource_read');
            Route::get('/LeadSource/create',[LeadSourceController::class,'create'])->name('LeadSource.create')->middleware('hasPermission:create_LeadSource');
            Route::post('/LeadSource/save',[LeadSourceController::class,'store'])->name('LeadSource.store');
            Route::get('/LeadSource/edit/{id}',[LeadSourceController::class,'edit'])->name('LeadSource.edit')->middleware('hasPermission:edit_LeadSource');
            Route::put('/LeadSource/update',[LeadSourceController::class,'update'])->name('LeadSource.update');
            Route::delete('/LeadSource/delete/{id}',[LeadSourceController::class,'destroy'])->name('LeadSource.delete')->middleware('hasPermission:delete_LeadSource');
       
            // ============================================
            // LEAD MEETING (the "Add Meeting" provision on the Lead detail page)
            // ============================================
            Route::post('/LeadMeeting/store', [LeadMeetingController::class, 'store'])->name('LeadMeeting.store');
            Route::put('/LeadMeeting/update/{id}', [LeadMeetingController::class, 'update'])->name('LeadMeeting.update');
            Route::delete('/LeadMeeting/delete/{id}', [LeadMeetingController::class, 'destroy'])->name('LeadMeeting.delete');

            //Quotation
            Route::get('/estimates', [EstimateController::class, 'index'])->name('Estimate');
            Route::get('/estimates/create', [EstimateController::class, 'create'])->name('Estimate.create');
            Route::post('/estimates/save', [EstimateController::class, 'store'])->name('Estimate.store');
            Route::get('/estimates/edit/{id}', [EstimateController::class, 'edit'])->name('Estimate.edit');
            Route::put('/estimates/update/{id}', [EstimateController::class, 'update'])->name('Estimate.update');
            Route::delete('/estimates/delete/{id}', [EstimateController::class, 'destroy'])->name('Estimate.delete');
            Route::get('/construction-estimates', [EstimateController::class, 'index'])->name('ConstructionEstimate.index');

            Route::get('/Quotation', [QuotationController::class, 'index'])
                ->name('Quotation')
                ->middleware('hasPermission:Quotation_read');

            Route::get('/Quotation/create', [QuotationController::class, 'create'])
                ->name('Quotation.create')
                ->middleware('hasPermission:create_Quotation');

            Route::get('/Quotation/estimate-details/{id}', [QuotationController::class, 'getEstimateDetails'])
                ->name('Quotation.estimateDetails');

            Route::post('/Quotation/store', [QuotationController::class, 'store'])
                ->name('Quotation.store');

            Route::get('/Quotation/edit/{id}', [QuotationController::class, 'edit'])
                ->name('Quotation.edit')
                ->middleware('hasPermission:edit_Quotation');

            Route::get('/Quotation/show/{id}', [QuotationController::class, 'show'])
                ->name('Quotation.show');

            Route::put('/Quotation/update', [QuotationController::class, 'update'])
                ->name('Quotation.update');

            Route::delete('/Quotation/delete/{id}', [QuotationController::class, 'destroy'])
                ->name('Quotation.delete')
                ->middleware('hasPermission:delete_Quotation');

            

               
    
//daily-work

    Route::prefix('daily-work')->group(function(){
    Route::get('/',  [DailyWorkController::class,'index'])->name('DailyWork');
    Route::get('/create',[DailyWorkController::class,'create'] )->name('DailyWork.create');
    Route::post('/store',[DailyWorkController::class,'store'])->name('DailyWork.store');
    Route::get('/edit/{id}',[DailyWorkController::class,'edit'] )->name('DailyWork.edit');
    Route::post('/update',[DailyWorkController::class,'update'])->name('DailyWork.update');
    Route::delete('/delete/{id}',[DailyWorkController::class,'destroy'])->name('DailyWork.delete');
});

            });
});