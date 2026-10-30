<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantCustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $customersData = [
                [
                    'email' => 'sarah.jenkins@example.com',
                    'first_name' => 'Sarah',
                    'last_name' => 'Jenkins',
                    'phone' => '+8801711223344',
                    'status' => 'active',
                    'addresses' => [
                        [
                            'title' => 'Home',
                            'first_name' => 'Sarah',
                            'last_name' => 'Jenkins',
                            'phone' => '+8801711223344',
                            'address_1' => 'Apartment 4B, Green Lake Residence',
                            'address_2' => 'Road 11, Block D',
                            'state_district' => 'Banani, Dhaka',
                            'zip' => '1213',
                            'country' => 'Bangladesh',
                            'is_default' => true,
                        ],
                        [
                            'title' => 'Office',
                            'first_name' => 'Sarah',
                            'last_name' => 'Jenkins',
                            'phone' => '+8801711223344',
                            'address_1' => 'Level 8, Crystal Tower',
                            'address_2' => 'Gulshan Avenue',
                            'state_district' => 'Gulshan-2, Dhaka',
                            'zip' => '1212',
                            'country' => 'Bangladesh',
                            'is_default' => false,
                        ],
                    ],
                ],
                [
                    'email' => 'michael.chen@example.com',
                    'first_name' => 'Michael',
                    'last_name' => 'Chen',
                    'phone' => '+8801822334455',
                    'status' => 'active',
                    'addresses' => [
                        [
                            'title' => 'Home',
                            'first_name' => 'Michael',
                            'last_name' => 'Chen',
                            'phone' => '+8801822334455',
                            'address_1' => 'House 42, Road 7/A',
                            'address_2' => 'Sector 3',
                            'state_district' => 'Uttara, Dhaka',
                            'zip' => '1230',
                            'country' => 'Bangladesh',
                            'is_default' => true,
                        ],
                    ],
                ],
                [
                    'email' => 'aisha.rahman@example.com',
                    'first_name' => 'Aisha',
                    'last_name' => 'Rahman',
                    'phone' => '+8801933445566',
                    'status' => 'active',
                    'addresses' => [
                        [
                            'title' => 'Home',
                            'first_name' => 'Aisha',
                            'last_name' => 'Rahman',
                            'phone' => '+8801933445566',
                            'address_1' => 'Plot 18, Block B',
                            'address_2' => 'Bashundhara R/A',
                            'state_district' => 'Dhaka',
                            'zip' => '1229',
                            'country' => 'Bangladesh',
                            'is_default' => true,
                        ],
                    ],
                ],
            ];

            foreach ($customersData as $cData) {
                $addresses = $cData['addresses'] ?? [];
                unset($cData['addresses']);

                $customer = Customer::firstOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'email' => $cData['email'],
                    ],
                    array_merge($cData, [
                        'password' => Hash::make('password'),
                    ])
                );

                foreach ($addresses as $addr) {
                    CustomerAddress::firstOrCreate(
                        [
                            'tenant_id' => $tenant->id,
                            'customer_id' => $customer->id,
                            'title' => $addr['title'],
                            'address_1' => $addr['address_1'],
                        ],
                        $addr
                    );
                }
            }
        }
    }
}
