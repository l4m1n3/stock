<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Sale;
use App\Models\Expense;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with(['sale.user', 'sale.saleItems.product', 'sale.saleServices.service'])
            ->latest('issued_at')
            ->paginate(20);
 
        // KPIs mois en cours
        $monthStart   = Carbon::now()->startOfMonth();
        $monthCount   = Invoice::where('issued_at', '>=', $monthStart)->count();
        $monthRevenue = Invoice::where('issued_at', '>=', $monthStart)->sum('total_amount');
 
        // Ventes sans facture (pour la modal manuelle)
        $invoicedSaleIds  = Invoice::pluck('sale_id');
        $uninvoicedSales  = Sale::whereNotIn('id', $invoicedSaleIds)->latest('sold_at')->get();
 
        // Données JSON pour l'aperçu JS
        $invoicesData = Invoice::with([
            'sale.saleItems.product',
            'sale.saleServices.service',
            'sale'
        ])->get()->keyBy('id')->map(function ($inv) {
            $items = [];
 
            foreach ($inv->sale->saleItems ?? [] as $si) {
                $items[] = [
                    'name'       => $si->product->name ?? '—',
                    'qty'        => $si->quantity,
                    'unit_price' => (float) $si->unit_price,
                    'total'      => (float) $si->total_price,
                ];
            }
 
            foreach ($inv->sale->saleServices ?? [] as $ss) {
                $items[] = [
                    'name'       => $ss->service->name ?? '—',
                    'qty'        => 1,
                    'unit_price' => (float) $ss->price,
                    'total'      => (float) $ss->price,
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
            'sale.user'
        ]);
 
        // Si vous utilisez barryvdh/laravel-dompdf :
        // $pdf = \PDF::loadView('invoices.pdf', compact('invoice'));
        // return $pdf->download($invoice->invoice_number . '.pdf');
 
        // Sinon, retourner la vue imprimable :
        return view('facturation.pdf', compact('invoice'));
    }
}
