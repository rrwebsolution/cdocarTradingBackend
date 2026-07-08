<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\JobOrder;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\SalesTransaction;
use App\Models\ServiceRequest;
use App\Models\SystemDocument;
use App\Models\Vehicle;
use App\Support\PdfLogo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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

    public function salesPdf(Request $request): Response
    {
        $rows = $this->applyFilters(
            SalesTransaction::query()->with(['customer', 'vehicle']),
            $request,
            'sold_at',
        )->latest('id')->get();

        return $this->renderReport('sales', 'sales-report', $rows, $request);
    }

    public function reservationsPdf(Request $request): Response
    {
        $rows = $this->applyFilters(
            Reservation::query()->with(['customer', 'vehicle']),
            $request,
            'reserved_at',
        )->latest('id')->get();

        return $this->renderReport('reservations', 'reservation-report', $rows, $request);
    }

    public function paymentsPdf(Request $request): Response
    {
        $rows = $this->applyFilters(
            Payment::query()->with(['customer', 'salesTransaction.vehicle']),
            $request,
            'paid_at',
        )->latest('id')->get();

        return $this->renderReport('payments', 'payment-report', $rows, $request);
    }

    public function vehiclesPdf(Request $request): Response
    {
        $rows = $this->applyFilters(
            Vehicle::query(),
            $request,
            'created_at',
        )->latest('id')->get();

        return $this->renderReport('vehicles', 'vehicle-inventory-report', $rows, $request);
    }

    public function customersPdf(Request $request): Response
    {
        $rows = $this->applyFilters(
            Customer::query()->withCount(['salesTransactions', 'reservations']),
            $request,
            'created_at',
            withStatus: false,
        )->latest('id')->get();

        return $this->renderReport('customers', 'customer-report', $rows, $request);
    }

    public function documentsPdf(Request $request): Response
    {
        $rows = $this->applyFilters(
            SystemDocument::query()->with(['customer', 'documentable']),
            $request,
            'uploaded_at',
        )->latest('id')->get();

        return $this->renderReport('documents', 'documents-report', $rows, $request);
    }

    public function jobOrdersPdf(Request $request): Response
    {
        $rows = $this->applyFilters(
            JobOrder::query()->with(['serviceRequest.customer', 'vehicle', 'assignedStaff']),
            $request,
            'scheduled_at',
        )->latest('id')->get();

        return $this->renderReport('job-orders', 'job-order-report', $rows, $request);
    }

    public function serviceRequestsPdf(Request $request): Response
    {
        $rows = $this->applyFilters(
            ServiceRequest::query()->with(['customer', 'vehicle', 'jobOrder']),
            $request,
            'preferred_service_date',
        )->latest('id')->get();

        return $this->renderReport('service-requests', 'service-request-report', $rows, $request);
    }

    private function applyFilters(Builder $query, Request $request, string $dateColumn, bool $withStatus = true): Builder
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $status = $withStatus ? $request->query('status') : null;

        if ($from) {
            $query->whereDate($dateColumn, '>=', $from);
        }

        if ($to) {
            $query->whereDate($dateColumn, '<=', $to);
        }

        if ($status) {
            $query->where('status', $status);
        }

        return $query;
    }

    private function renderReport(string $view, string $filename, iterable $rows, Request $request): Response
    {
        $pdf = Pdf::loadView("pdf.reports.{$view}", [
            'from' => $request->query('from'),
            'rows' => $rows,
            'status' => $request->query('status'),
            'to' => $request->query('to'),
            'logoBase64' => PdfLogo::base64(),
        ])->setPaper('legal', 'landscape');

        return $pdf->stream("{$filename}.pdf");
    }
}
