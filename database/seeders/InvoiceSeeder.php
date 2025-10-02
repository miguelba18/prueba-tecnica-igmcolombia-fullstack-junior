<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::all();
        $user = User::first();

        if ($customers->isEmpty() || !$user) {
            $this->command->warn('Primero ejecuta los seeders de clientes y usuarios');
            return;
        }

        $invoices = [
            [
                'customer_id' => $customers[0]->id,
                'user_id' => $user->id,
                'invoice_number' => 'INV-2025-00001',
                'description' => 'Servicios de consultoría tecnológica',
                'notes' => 'Pago a 30 días',
                'issue_date' => Carbon::now()->subDays(10),
                'due_date' => Carbon::now()->addDays(20),
                'status' => 'pending',
                'subtotal' => 1000000,
                'tax_amount' => 190000,
                'total_amount' => 1190000,
                'items' => [
                    [
                        'name' => 'Consultoría técnica',
                        'description' => '40 horas de consultoría',
                        'quantity' => 40,
                        'unit_price' => 25000,
                        'tax_rate' => 19.00,
                    ]
                ]
            ],
            [
                'customer_id' => $customers[1]->id,
                'user_id' => $user->id,
                'invoice_number' => 'INV-2025-00002',
                'description' => 'Desarrollo de software',
                'notes' => 'Proyecto completado',
                'issue_date' => Carbon::now()->subDays(45),
                'due_date' => Carbon::now()->subDays(15),
                'status' => 'paid',
                'subtotal' => 2500000,
                'tax_amount' => 475000,
                'total_amount' => 2975000,
                'items' => [
                    [
                        'name' => 'Desarrollo backend',
                        'description' => 'API REST con Laravel',
                        'quantity' => 1,
                        'unit_price' => 1500000,
                        'tax_rate' => 19.00,
                    ],
                    [
                        'name' => 'Desarrollo frontend',
                        'description' => 'Aplicación con Vue.js',
                        'quantity' => 1,
                        'unit_price' => 1000000,
                        'tax_rate' => 19.00,
                    ]
                ]
            ],
            [
                'customer_id' => $customers[2]->id,
                'user_id' => $user->id,
                'invoice_number' => 'INV-2025-00003',
                'description' => 'Mantenimiento mensual',
                'notes' => 'Factura vencida',
                'issue_date' => Carbon::now()->subDays(60),
                'due_date' => Carbon::now()->subDays(30),
                'status' => 'overdue',
                'subtotal' => 500000,
                'tax_amount' => 95000,
                'total_amount' => 595000,
                'items' => [
                    [
                        'name' => 'Soporte técnico',
                        'description' => 'Mantenimiento preventivo y correctivo',
                        'quantity' => 1,
                        'unit_price' => 500000,
                        'tax_rate' => 19.00,
                    ]
                ]
            ],
            [
                'customer_id' => $customers[0]->id,
                'user_id' => $user->id,
                'invoice_number' => 'INV-2025-00004',
                'description' => 'Hosting y dominio anual',
                'notes' => 'Renovación anual',
                'issue_date' => Carbon::now()->subDays(5),
                'due_date' => Carbon::now()->addDays(25),
                'status' => 'pending',
                'subtotal' => 300000,
                'tax_amount' => 57000,
                'total_amount' => 357000,
                'items' => [
                    [
                        'name' => 'Hosting VPS',
                        'description' => 'Plan anual',
                        'quantity' => 1,
                        'unit_price' => 200000,
                        'tax_rate' => 19.00,
                    ],
                    [
                        'name' => 'Dominio .com',
                        'description' => 'Registro anual',
                        'quantity' => 1,
                        'unit_price' => 100000,
                        'tax_rate' => 19.00,
                    ]
                ]
            ],
        ];

        foreach ($invoices as $invoiceData) {
            $items = $invoiceData['items'];
            unset($invoiceData['items']);

            $invoice = Invoice::create($invoiceData);

            foreach ($items as $item) {
                $invoice->items()->create($item);
            }
        }

        $this->command->info('Facturas de prueba creadas exitosamente');
    }
}