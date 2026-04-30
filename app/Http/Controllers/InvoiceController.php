<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Sale;
use App\Models\Confection;
use Carbon\Carbon;
use PDF;

class InvoiceController extends Controller
{
    public function index()
    {
        $branchId = auth()->user()->branch_id;

        $invoices = Invoice::with([
            'sale.user',
            'sale.saleItems.product',
            'sale.saleServices.service',
            'sale.saleConfections.confection',   // ← ajout
        ])
            ->where('branch_id', $branchId)
            ->latest('issued_at')
            ->paginate(20);

        $monthStart   = Carbon::now()->startOfMonth();
        $monthCount   = Invoice::where('branch_id', $branchId)->where('issued_at', '>=', $monthStart)->count();
        $monthRevenue = Invoice::where('branch_id', $branchId)->where('issued_at', '>=', $monthStart)->sum('total_amount');

        $invoicedSaleIds = Invoice::where('branch_id', $branchId)->pluck('sale_id');
        $uninvoicedSales = Sale::where('branch_id', $branchId)
            ->whereNotIn('id', $invoicedSaleIds)
            ->latest('sold_at')->get();

        // Données JSON pour JS (aperçu)
        $invoicesData = Invoice::with([
            'sale.saleItems.product',
            'sale.saleServices.service',
            'sale.saleConfections.confection',   // ← ajout
            'sale',
        ])
            ->where('branch_id', $branchId)
            ->get()
            ->keyBy('id')
            ->map(function ($inv) {

                $items = [];

                foreach ($inv->sale->saleItems ?? [] as $si) {
                    $items[] = [
                        'name'       => $si->product->name ?? '—',
                        'qty'        => $si->quantity,
                        'unit_price' => (float) $si->unit_price,
                        'total'      => (float) $si->total_price,
                        'type'       => 'produit',
                    ];
                }

                foreach ($inv->sale->saleServices ?? [] as $ss) {
                    $items[] = [
                        'name'       => $ss->service->name ?? '—',
                        'qty'        => 1,
                        'unit_price' => (float) $ss->price,
                        'total'      => (float) $ss->price,
                        'type'       => 'service',
                    ];
                }

                foreach ($inv->sale->saleConfections ?? [] as $sc) {  // ← ajout
                    $items[] = [
                        'name'       => '🎀 ' . ($sc->confection->name ?? '—'),
                        'qty'        => $sc->quantity,
                        'unit_price' => (float) $sc->unit_price,
                        'total'      => (float) $sc->total_price,
                        'type'       => 'confection',
                    ];
                }

                return [
                    'id'             => $inv->id,
                    'invoice_number' => $inv->invoice_number,
                    'issued_at'      => Carbon::parse($inv->issued_at)->format('d/m/Y H:i'),
                    'total_amount'   => (float) $inv->total_amount,
                    'payment_method' => $inv->sale->payment_method ?? 'cash',
                    'items'          => $items,
                ];
            });
        activity_log('invoices_viewed', "Consultation factures : {$invoices->total()} factures, CA du mois $monthRevenue");
        return view('facturation.index', compact(
            'invoices',
            'monthCount',
            'monthRevenue',
            'uninvoicedSales',
            'invoicesData'
        ));
    }

    public function pdf(Invoice $invoice)
    {
        $invoice->load([
            'sale.saleItems.product',
            'sale.saleServices.service',
            'sale.saleConfections.confection',   // ← ajout
            'sale.user',
        ]);

        $pdf = PDF::loadView('facturation.pdf', compact('invoice'))
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
            ]);
        activity_log('invoice_pdf_generated', "PDF facture générée : {$invoice->invoice_number}");
        return $pdf->download($invoice->invoice_number . '.pdf');
    }
}
