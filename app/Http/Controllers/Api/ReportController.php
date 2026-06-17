<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\JobOrder;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\SalesTransaction;
use App\Models\ServiceRequest;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => [
                'inventory' => [
                    'total' => Vehicle::count(),
                    'available' => Vehicle::where('status', 'available')->count(),
                    'reserved' => Vehicle::where('status', 'reserved')->count(),
                    'sold' => Vehicle::where('status', 'sold')->count(),
                ],
                'sales' => [
                    'transactions' => SalesTransaction::count(),
                    'paid' => SalesTransaction::where('status', 'paid')->count(),
                    'partial' => SalesTransaction::where('status', 'partial')->count(),
                    'total_amount' => SalesTransaction::sum('total_amount'),
                    'paid_amount' => SalesTransaction::sum('paid_amount'),
                    'outstanding' => SalesTransaction::sum('balance'),
                ],
                'payments' => [
                    'records' => Payment::count(),
                    'total_collected' => Payment::sum('amount'),
                ],
                'operations' => [
                    'customers' => Customer::count(),
                    'reservations' => Reservation::count(),
                    'service_requests' => ServiceRequest::count(),
                    'job_orders' => JobOrder::count(),
                ],
            ],
        ]);
    }
}
