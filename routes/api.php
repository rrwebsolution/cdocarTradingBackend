<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DeedOfSaleController;
use App\Http\Controllers\Api\JobOrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SalesTransactionController;
use App\Http\Controllers\Api\ServiceRequestController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VehicleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
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
    Route::apiResource('staff', StaffController::class)->except(['show']);
    Route::apiResource('reservations', ReservationController::class)->except(['show']);
    Route::apiResource('sales-transactions', SalesTransactionController::class)->except(['show']);
    Route::apiResource('payments', PaymentController::class)->except(['show']);
    Route::apiResource('service-requests', ServiceRequestController::class)->except(['show']);
    Route::apiResource('job-orders', JobOrderController::class)->except(['show']);
    Route::get('/sales-transactions/{salesTransaction}/deed-of-sale', [DeedOfSaleController::class, 'show']);
    Route::get('/sales-transactions/{salesTransaction}/deed-of-sale/pdf', [DeedOfSaleController::class, 'pdf']);
    Route::get('/reports/summary', [ReportController::class, 'index']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
