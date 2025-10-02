<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\InvoiceService;

class ListInvoicesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:list 
                            {--status= : Filtrar por estado (pending, paid, overdue)}
                            {--customer= : Filtrar por ID de cliente}
                            {--limit=10 : Número de facturas a mostrar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listar facturas del sistema con filtros opcionales';

    protected $invoiceService;

    /**
     * Create a new command instance.
     */
    public function __construct(InvoiceService $invoiceService)
    {
        parent::__construct();
        $this->invoiceService = $invoiceService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Sistema de Facturación ===');
        $this->newLine();

        // Obtener opciones
        $filters = [
            'status' => $this->option('status'),
            'customer_id' => $this->option('customer'),
            'per_page' => $this->option('limit'),
        ];

        try {
            // Usar el servicio para obtener las facturas
            $invoices = $this->invoiceService->list($filters);

            if ($invoices->isEmpty()) {
                $this->warn('No se encontraron facturas con los filtros aplicados.');
                return Command::SUCCESS;
            }

            // Mostrar información de filtros aplicados
            if ($filters['status']) {
                $this->line("Filtro de estado: <fg=cyan>{$filters['status']}</>");
            }
            if ($filters['customer_id']) {
                $this->line("Filtro de cliente ID: <fg=cyan>{$filters['customer_id']}</>");
            }

            $this->newLine();
            $this->info("Total de facturas encontradas: {$invoices->total()}");
            $this->info("Mostrando: {$invoices->count()} facturas");
            $this->newLine();

            // Crear tabla con los datos
            $headers = ['ID', 'Número', 'Cliente', 'Fecha Emisión', 'Vencimiento', 'Total', 'Estado'];
            $rows = [];

            foreach ($invoices as $invoice) {
                $statusColor = match($invoice->status) {
                    'paid' => 'green',
                    'pending' => 'yellow',
                    'overdue' => 'red',
                    default => 'white'
                };

                $rows[] = [
                    $invoice->id,
                    $invoice->invoice_number,
                    $invoice->customer->full_name,
                    $invoice->issue_date->format('Y-m-d'),
                    $invoice->due_date->format('Y-m-d'),
                    '$' . number_format($invoice->total_amount, 2),
                    "<fg={$statusColor}>" . strtoupper($invoice->status) . "</>",
                ];
            }

            $this->table($headers, $rows);

            // Estadísticas
            $this->newLine();
            $this->info('=== Estadísticas ===');
            $totalAmount = $invoices->sum('total_amount');
            $this->line("Monto total: <fg=green>$" . number_format($totalAmount, 2) . "</>");
            
            $pendingCount = $invoices->where('status', 'pending')->count();
            $paidCount = $invoices->where('status', 'paid')->count();
            $overdueCount = $invoices->where('status', 'overdue')->count();

            $this->line("Pendientes: <fg=yellow>{$pendingCount}</>");
            $this->line("Pagadas: <fg=green>{$paidCount}</>");
            $this->line("Vencidas: <fg=red>{$overdueCount}</>");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error al listar facturas: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}