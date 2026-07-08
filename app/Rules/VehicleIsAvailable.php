<?php

namespace App\Rules;

use App\Models\Vehicle;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class VehicleIsAvailable implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $vehicle = Vehicle::find($value);

        if ($vehicle && $vehicle->status !== 'available') {
            $fail('This vehicle is not available for reservation.');
        }
    }
}
