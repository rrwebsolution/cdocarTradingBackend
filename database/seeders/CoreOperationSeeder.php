<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\JobOrder;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\SalesTransaction;
use App\Models\ServiceRequest;
use App\Models\Staff;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class CoreOperationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicles = collect([
            ['name' => 'Toyota Hilux 2021', 'brand' => 'Toyota', 'model' => 'Hilux', 'year' => 2021, 'color' => 'White', 'engine_number' => '2GD-884120', 'chassis_number' => 'MROHB8CD9001842', 'plate_number' => 'KAA 1842', 'mileage' => 42000, 'purchase_price' => 980000, 'selling_price' => 1180000, 'location' => 'Showroom A', 'condition' => 'Good', 'status' => 'available'],
            ['name' => 'Honda Civic 2020', 'brand' => 'Honda', 'model' => 'Civic', 'year' => 2020, 'color' => 'Gray', 'engine_number' => 'L15B7-6621', 'chassis_number' => 'PMHFC1650L006621', 'plate_number' => 'GTR 6621', 'mileage' => 33000, 'purchase_price' => 760000, 'selling_price' => 895000, 'location' => 'Reserved Bay', 'condition' => 'Good', 'status' => 'reserved'],
            ['name' => 'Ford Everest 2019', 'brand' => 'Ford', 'model' => 'Everest', 'year' => 2019, 'color' => 'Black', 'engine_number' => 'P4AT-3910', 'chassis_number' => 'MNAAXXMAWAK03910', 'plate_number' => 'CDO 3910', 'mileage' => 58000, 'purchase_price' => 900000, 'selling_price' => 1050000, 'location' => 'Service Bay 2', 'condition' => 'For repair', 'status' => 'for repair'],
            ['name' => 'Toyota Vios 2022', 'brand' => 'Toyota', 'model' => 'Vios', 'year' => 2022, 'color' => 'Red', 'engine_number' => '2NR-8120', 'chassis_number' => 'MR2B29F33008120', 'plate_number' => 'NMB 8120', 'mileage' => 18000, 'purchase_price' => 610000, 'selling_price' => 720000, 'location' => 'Showroom B', 'condition' => 'Excellent', 'status' => 'available'],
            ['name' => 'Suzuki Ertiga 2023', 'brand' => 'Suzuki', 'model' => 'Ertiga', 'year' => 2023, 'color' => 'Silver', 'engine_number' => 'K15B-4421', 'chassis_number' => 'MBHCW41S004421', 'plate_number' => 'JPA 4421', 'mileage' => 12500, 'purchase_price' => 710000, 'selling_price' => 820000, 'location' => 'Release Area', 'condition' => 'Excellent', 'status' => 'released'],
            ['name' => 'Ford Ranger 2022', 'brand' => 'Ford', 'model' => 'Ranger', 'year' => 2022, 'color' => 'Blue', 'engine_number' => 'P4AT-2202', 'chassis_number' => 'MNABXXMAWCN02202', 'plate_number' => 'RNG 2202', 'mileage' => 24000, 'purchase_price' => 1050000, 'selling_price' => 1230000, 'location' => 'Archive', 'condition' => 'Sold unit', 'status' => 'sold'],
        ])->mapWithKeys(fn (array $vehicle) => [
            $vehicle['name'] => Vehicle::updateOrCreate(
                ['plate_number' => $vehicle['plate_number']],
                $vehicle,
            ),
        ]);

        $customers = collect([
            ['name' => 'Christian Uy', 'email' => 'christian@email.com', 'contact' => '0917 431 2208', 'status' => 'active'],
            ['name' => 'Joanne Tan', 'email' => 'joanne@email.com', 'contact' => '0998 771 4832', 'status' => 'active'],
            ['name' => 'Miguel Reyes', 'email' => 'miguel@email.com', 'contact' => '0920 115 9300', 'status' => 'pending'],
            ['name' => 'Alex Yu', 'email' => 'alex@email.com', 'contact' => '0916 284 2201', 'status' => 'active'],
            ['name' => 'Mica Cruz', 'email' => 'mica@email.com', 'contact' => '0995 112 7210', 'status' => 'active'],
            ['name' => 'Daniel Go', 'email' => 'daniel@email.com', 'contact' => '0927 551 9801', 'status' => 'active'],
        ])->mapWithKeys(fn (array $customer) => [
            $customer['name'] => Customer::updateOrCreate(
                ['email' => $customer['email']],
                $customer,
            ),
        ]);

        $staff = collect([
            ['name' => 'Ramil Cruz', 'email' => 'ramil@autocdo.com', 'position' => 'Senior Mechanic', 'schedule' => '8:00 AM - 5:00 PM', 'activity' => 'Engine inspection', 'status' => 'active'],
            ['name' => 'Leah Fernandez', 'email' => 'leah@autocdo.com', 'position' => 'Secretary', 'schedule' => '9:00 AM - 6:00 PM', 'activity' => 'Payment encoding', 'status' => 'active'],
            ['name' => 'Joel Ramos', 'email' => 'joel@autocdo.com', 'position' => 'Mechanic', 'schedule' => '8:00 AM - 5:00 PM', 'activity' => 'Brake repair', 'status' => 'active'],
            ['name' => 'Aaron Cruz', 'email' => 'aaron@autocdo.com', 'position' => 'Carwasher', 'schedule' => '7:00 AM - 4:00 PM', 'activity' => 'Interior cleaning', 'status' => 'active'],
        ])->mapWithKeys(fn (array $member) => [
            $member['name'] => Staff::updateOrCreate(
                ['email' => $member['email']],
                $member,
            ),
        ]);

        Reservation::updateOrCreate(['reference' => 'RSV-2041'], ['customer_id' => $customers['Joanne Tan']->id, 'vehicle_id' => $vehicles['Honda Civic 2020']->id, 'amount' => 20000, 'reserved_at' => '2026-06-07', 'status' => 'for approval']);
        Reservation::updateOrCreate(['reference' => 'RSV-2042'], ['customer_id' => $customers['Alex Yu']->id, 'vehicle_id' => $vehicles['Toyota Vios 2022']->id, 'amount' => 25000, 'reserved_at' => '2026-05-30', 'status' => 'approved']);

        $sale = SalesTransaction::updateOrCreate(
            ['reference' => 'SALE-4410'],
            ['customer_id' => $customers['Christian Uy']->id, 'vehicle_id' => $vehicles['Toyota Hilux 2021']->id, 'payment_method' => 'cash', 'total_amount' => 1180000, 'paid_amount' => 300000, 'balance' => 880000, 'status' => 'partial', 'sold_at' => '2026-06-10'],
        );
        Payment::updateOrCreate(['receipt_number' => 'OR-10291'], ['sales_transaction_id' => $sale->id, 'customer_id' => $customers['Christian Uy']->id, 'amount' => 300000, 'method' => 'cash', 'status' => 'paid', 'paid_at' => '2026-06-14']);

        $request = ServiceRequest::updateOrCreate(
            ['reference' => 'SR-3002'],
            ['customer_id' => $customers['Alex Yu']->id, 'vehicle_id' => $vehicles['Toyota Vios 2022']->id, 'service_type' => 'Maintenance', 'issue' => 'Scheduled oil and filter replacement', 'progress' => 'Approved for service', 'status' => 'approved'],
        );
        JobOrder::updateOrCreate(
            ['reference' => 'JO-2026-030'],
            ['service_request_id' => $request->id, 'vehicle_id' => $vehicles['Toyota Vios 2022']->id, 'assigned_staff_id' => $staff['Joel Ramos']->id, 'activity' => 'Maintenance', 'repair_status' => 'Approved', 'washing_status' => 'N/A', 'maintenance_record' => 'Oil and filter replacement', 'scheduled_at' => '2026-06-18', 'status' => 'approved'],
        );
    }
}
