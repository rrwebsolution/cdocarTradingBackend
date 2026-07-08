<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DeedOfSaleController;
use App\Http\Controllers\Api\FinancingRecordController;
use App\Http\Controllers\Api\IdScanController;
use App\Http\Controllers\Api\JobOrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\PreSaleRepairController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SalesInvoiceController;
use App\Http\Controllers\Api\SalesTransactionController;
use App\Http\Controllers\Api\ServiceRequestController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\SystemDocumentController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\VehicleReleaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/register/scan-id', [IdScanController::class, 'scan']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::patch('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
    Route::get('/roles', [RoleController::class, 'index']);
    Route::post('/roles', [RoleController::class, 'store']);
    Route::patch('/roles/{role}', [RoleController::class, 'update']);
    Route::delete('/roles/{role}', [RoleController::class, 'destroy']);
    Route::apiResource('vehicles', VehicleController::class)->except(['show']);
    Route::apiResource('customers', CustomerController::class)->except(['show']);
    Route::get('/customers/{customer}/summary', [CustomerController::class, 'summary']);
    Route::apiResource('staff', StaffController::class)->except(['show']);
    Route::get('/payment-methods', [PaymentMethodController::class, 'index']);
    Route::apiResource('reservations', ReservationController::class)->except(['show']);
    Route::apiResource('sales-transactions', SalesTransactionController::class)->except(['show']);
    Route::apiResource('payments', PaymentController::class)->except(['show']);
    Route::apiResource('service-requests', ServiceRequestController::class)->except(['show']);
    Route::apiResource('job-orders', JobOrderController::class)->except(['show']);
    Route::apiResource('financing-records', FinancingRecordController::class);
    Route::apiResource('system-documents', SystemDocumentController::class);
    Route::apiResource('vehicle-releases', VehicleReleaseController::class);
    Route::apiResource('pre-sale-repairs', PreSaleRepairController::class);
    Route::apiResource('activity-logs', ActivityLogController::class)->only(['index', 'show', 'store']);
    Route::get('/sales-transactions/{salesTransaction}/deed-of-sale', [DeedOfSaleController::class, 'show']);
    Route::get('/sales-transactions/{salesTransaction}/deed-of-sale/pdf', [DeedOfSaleController::class, 'pdf']);
    Route::get('/sales-transactions/{salesTransaction}/invoice', [SalesInvoiceController::class, 'show']);
    Route::get('/sales-transactions/{salesTransaction}/invoice/pdf', [SalesInvoiceController::class, 'pdf']);
    Route::get('/reports/summary', [ReportController::class, 'index']);
    Route::get('/reports/sales/pdf', [ReportController::class, 'salesPdf']);
    Route::get('/reports/reservations/pdf', [ReportController::class, 'reservationsPdf']);
    Route::get('/reports/payments/pdf', [ReportController::class, 'paymentsPdf']);
    Route::get('/reports/vehicles/pdf', [ReportController::class, 'vehiclesPdf']);
    Route::get('/reports/customers/pdf', [ReportController::class, 'customersPdf']);
    Route::get('/reports/documents/pdf', [ReportController::class, 'documentsPdf']);
    Route::get('/reports/job-orders/pdf', [ReportController::class, 'jobOrdersPdf']);
    Route::get('/reports/service-requests/pdf', [ReportController::class, 'serviceRequestsPdf']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
