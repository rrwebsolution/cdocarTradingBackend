<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect([
            'Admin' => [
                'description' => 'Full system access for managing users, roles, inventory, sales, payments, job orders, reports, and operational records.',
                'permissions' => [
                    'User Management',
                    'Role Management',
                    'Staff Management',
                    'Vehicle Inventory',
                    'Reservations',
                    'Sales & Payments',
                    'Job Orders & Maintenance',
                    'Customer Records',
                    'Documents',
                    'Financing',
                    'Reports',
                    'Activity Logs',
                    'Vehicle Release',
                ],
            ],
            'Secretary' => [
                'description' => 'Front office access for encoding records, assisting customers, processing reservations, recording payments, and preparing reports.',
                'permissions' => [
                    'Vehicle Updates',
                    'Customer Records',
                    'Reservations',
                    'Sales & Payments',
                    'Job Orders',
                    'Documents',
                    'Financing',
                    'Vehicle Release',
                    'Reports',
                ],
            ],
            'Mechanic' => [
                'description' => 'Workshop access for assigned repairs, inspections, maintenance tracking, and vehicle condition updates.',
                'permissions' => [
                    'Assigned Job Orders',
                    'Repair Progress',
                    'Maintenance Records',
                    'Vehicle Status Updates',
                ],
            ],
            'Carwasher' => [
                'description' => 'Cleaning team access for vehicle washing, detailing, preparation, and cleaning status updates.',
                'permissions' => [
                    'Assigned Cleaning Job Orders',
                    'Washing Status Updates',
                    'Vehicle Preparation',
                ],
            ],
            'Customer' => [
                'description' => 'Customer portal access for browsing vehicles, reservations, payments, purchase history, and service requests.',
                'permissions' => [
                    'View Vehicles',
                    'Reservations',
                    'Payments',
                    'Transaction History',
                    'Service Requests',
                    'Documents',
                ],
            ],
        ])->each(fn (array $role, string $name) => Role::updateOrCreate(
            ['name' => $name],
            [
                'description' => $role['description'],
                'status' => 'active',
                'permissions' => $role['permissions'],
            ],
        ));
    }
}
