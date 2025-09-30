<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'first_name' => 'Juan',
                'last_name' => 'Pérez',
                'document_type' => 'CC',
                'document_number' => '1234567890',
                'email' => 'juan.perez@example.com',
                'phone' => '3001234567',
                'address' => 'Calle 123 #45-67',
            ],
            [
                'first_name' => 'María',
                'last_name' => 'González',
                'document_type' => 'CC',
                'document_number' => '9876543210',
                'email' => 'maria.gonzalez@example.com',
                'phone' => '3109876543',
                'address' => 'Carrera 45 #12-34',
            ],
            [
                'first_name' => 'Empresa',
                'last_name' => 'Tech SAS',
                'document_type' => 'NIT',
                'document_number' => '900123456-1',
                'email' => 'contacto@empresatech.com',
                'phone' => '6012345678',
                'address' => 'Av. Empresarial #100-50',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}