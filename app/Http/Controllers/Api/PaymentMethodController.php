<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\PaymentMethod;
use Illuminate\Http\JsonResponse;

class PaymentMethodController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => collect(PaymentMethod::ALL)->map(fn (string $method) => [
                'value' => $method,
                'label' => ucfirst($method),
                'qr_url' => in_array($method, PaymentMethod::QR_BACKED, true) ? asset("images/qr/{$method}.png") : null,
            ])->values(),
        ]);
    }
}
