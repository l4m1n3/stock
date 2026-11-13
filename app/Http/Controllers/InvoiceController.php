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
    // public function index()
    // {
    //     $branchId = auth()->user()->branch_id;

    //     $invoices = Invoice::with([
    //         'sale.user',
    //         'sale.saleItems.product',
    //         'sale.saleServices.service',
    //         'sale.saleConfections.confection',   // ← ajout
    //     ])
    //         ->where('branch_id', $branchId)
    //         ->latest('issued_at')
    //         ->paginate(20);

    //     $monthStart   = Carbon::now()->startOfMonth();
    //     $monthCount   = Invoice::where('branch_id', $branchId)->where('issued_at', '>=', $monthStart)->count();
    //     $monthRevenue = Invoice::where('branch_id', $branchId)->where('issued_at', '>=', $monthStart)->sum('total_amount');

    //     $invoicedSaleIds = Invoice::where('branch_id', $branchId)->pluck('sale_id');
    //     $uninvoicedSales = Sale::where('branch_id', $branchId)
    //         ->whereNotIn('id', $invoicedSaleIds)
    //         ->latest('sold_at')->get();

    //     // Données JSON pour JS (aperçu)
    //     $invoicesData = Invoice::with([
    //         'sale.saleItems.product',
    //         'sale.saleServices.service',
    //         'sale.saleConfections.confection',   // ← ajout
    //         'sale',
    //     ])
    //         ->where('branch_id', $branchId)
    //         ->get()
    //         ->keyBy('id')
    //         ->map(function ($inv) {

    //             $items = [];

    //             foreach ($inv->sale->saleItems ?? [] as $si) {
    //                 $items[] = [
    //                     'name'       => $si->product->name ?? '—',
    //                     'qty'        => $si->quantity,
    //                     'unit_price' => (float) $si->unit_price,
    //                     'total'      => (float) $si->total_price,
    //                     'type'       => 'produit',
    //                 ];
    //             }

    //             foreach ($inv->sale->saleServices ?? [] as $ss) {
    //                 $items[] = [
    //                     'name'       => $ss->service->name ?? '—',
    //                     'qty'        => 1,
    //                     'unit_price' => (float) $ss->price,
    //                     'total'      => (float) $ss->price,
    //                     'type'       => 'service',
    //                 ];
    //             }

    //             foreach ($inv->sale->saleConfections ?? [] as $sc) {  // ← ajout
    //                 $items[] = [
    //                     'name'       => '🎀 ' . ($sc->confection->name ?? '—'),
    //                     'qty'        => $sc->quantity,
    //                     'unit_price' => (float) $sc->unit_price,
    //                     'total'      => (float) $sc->total_price,
    //                     'type'       => 'confection',
    //                 ];
    //             }

    //             return [
    //                 'id'             => $inv->id,
    //                 'invoice_number' => $inv->invoice_number,
    //                 'issued_at'      => Carbon::parse($inv->issued_at)->format('d/m/Y H:i'),
    //                 'total_amount'   => (float) $inv->total_amount,
    //                 'payment_method' => $inv->sale->payment_method ?? 'cash',
    //                 'items'          => $items,
    //             ];
    //         });
    //     activity_log('invoices_viewed', "Consultation factures : {$invoices->total()} factures, CA du mois $monthRevenue");
    //     return view('facturation.index', compact(
    //         'invoices',
    //         'monthCount',
    //         'monthRevenue',
    //         'uninvoicedSales',
    //         'invoicesData'
    //     ));
    // }
    // public function index()
    // {
    //     $branchId = auth()->user()->branch_id;
    //     $today    = Carbon::today();

    //     // ─── Factures paginées ────────────────────────────────────────────────
    //     $invoices = Invoice::with([
    //         'sale.user',
    //         'sale.saleItems.product',
    //         'sale.saleServices.service',
    //         'sale.saleConfections.confection',
    //     ])
    //         ->where('branch_id', $branchId)
    //         ->latest('issued_at')
    //         ->paginate(20);

    //     // ─── Stats du JOUR ────────────────────────────────────────────────────
    //     $dayCount   = Invoice::where('branch_id', $branchId)->whereDate('issued_at', $today)->count();
    //     $dayRevenue = Invoice::where('branch_id', $branchId)->whereDate('issued_at', $today)->sum('total_amount');

    //     $dayByPayment = Invoice::where('invoices.branch_id', $branchId)
    //         ->whereDate('invoices.issued_at', $today)
    //         ->join('sales', 'invoices.sale_id', '=', 'sales.id')
    //         ->selectRaw('sales.payment_method, SUM(invoices.total_amount) as total, COUNT(invoices.id) as count')
    //         ->groupBy('sales.payment_method')
    //         ->get()
    //         ->keyBy('payment_method');

    //     // ─── Stats du MOIS (pour les cards existantes) ────────────────────────
    //     $monthStart   = Carbon::now()->startOfMonth();
    //     $monthCount   = Invoice::where('invoices.branch_id', $branchId)->where('invoices.issued_at', '>=', $monthStart)->count();
    //     $monthRevenue = Invoice::where('invoices.branch_id', $branchId)->where('invoices.issued_at', '>=', $monthStart)->sum('invoices.total_amount');

    //     // ─── Ventes non facturées ─────────────────────────────────────────────
    //     $invoicedSaleIds = Invoice::where('branch_id', $branchId)->pluck('sale_id');
    //     $uninvoicedSales = Sale::where('branch_id', $branchId)
    //         ->whereNotIn('id', $invoicedSaleIds)
    //         ->latest('sold_at')->get();

    //     // ─── Données JSON pour aperçu JS ──────────────────────────────────────
    //     $invoicesData = Invoice::with([
    //         'sale.saleItems.product',
    //         'sale.saleServices.service',
    //         'sale.saleConfections.confection',
    //         'sale',
    //     ])
    //         ->where('branch_id', $branchId)
    //         ->get()
    //         ->keyBy('id')
    //         ->map(function ($inv) {
    //             $items = [];

    //             foreach ($inv->sale->saleItems ?? [] as $si) {
    //                 $items[] = [
    //                     'name'       => $si->product->name ?? '—',
    //                     'qty'        => $si->quantity,
    //                     'unit_price' => (float) $si->unit_price,
    //                     'total'      => (float) $si->total_price,
    //                     'type'       => 'produit',
    //                 ];
    //             }

    //             foreach ($inv->sale->saleServices ?? [] as $ss) {
    //                 $items[] = [
    //                     'name'       => $ss->service->name ?? '—',
    //                     'qty'        => 1,
    //                     'unit_price' => (float) $ss->price,
    //                     'total'      => (float) $ss->price,
    //                     'type'       => 'service',
    //                 ];
    //             }

    //             foreach ($inv->sale->saleConfections ?? [] as $sc) {
    //                 $items[] = [
    //                     'name'       => '🎀 ' . ($sc->confection->name ?? '—'),
    //                     'qty'        => $sc->quantity,
    //                     'unit_price' => (float) $sc->unit_price,
    //                     'total'      => (float) $sc->total_price,
    //                     'type'       => 'confection',
    //                 ];
    //             }

    //             return [
    //                 'id'             => $inv->id,
    //                 'invoice_number' => $inv->invoice_number,
    //                 'issued_at'      => Carbon::parse($inv->issued_at)->format('d/m/Y H:i'),
    //                 'total_amount'   => (float) $inv->total_amount,
    //                 'payment_method' => $inv->sale->payment_method ?? 'cash',
    //                 'items'          => $items,
    //             ];
    //         });

    //     activity_log('invoices_viewed', "Consultation factures : {$invoices->total()} factures, CA du mois $monthRevenue");

    //     return view('facturation.index', compact(
    //         'invoices',
    //         'invoicesData',
    //         'uninvoicedSales',
    //         'dayCount',
    //         'dayRevenue',
    //         'dayByPayment',
    //         'monthCount',
    //         'monthRevenue',
    //     ));
    // }
    public function index()
    {
        $branchId = auth()->user()->branch_id;
        $isStaff  = auth()->user()->role === 'staff';
        $today    = Carbon::today();
        $userId= auth()->user()->id;

        // ─── Factures paginées ────────────────────────────────────────────────
        $invoicesQuery = Invoice::with([
            'sale.user',
            'sale.saleItems.product',
            'sale.saleServices.service',
            'sale.saleConfections.confection',
        ])

            // ->when($isStaff, fn($q) => $q->where('user_id', $userId)) // 🔒 staff = ses dépenses uniquement, manager = tout
            ->where('branch_id', $branchId);

        if ($isStaff) {
            $invoicesQuery->whereDate('issued_at', $today);
        }

        $invoices = $invoicesQuery->latest('issued_at')->paginate(20);

        // ─── Stats du JOUR (pour tous) ────────────────────────────────────────
        $dayCount   = Invoice::where('branch_id', $branchId)->whereDate('issued_at', $today)->count();
        $dayRevenue = Invoice::where('branch_id', $branchId)->whereDate('issued_at', $today)->sum('total_amount');

        $dayByPayment = Invoice::where('invoices.branch_id', $branchId)
            ->whereDate('invoices.issued_at', $today)
            ->join('sales', 'invoices.sale_id', '=', 'sales.id')
            ->selectRaw('sales.payment_method, SUM(invoices.total_amount) as total, COUNT(invoices.id) as count')
            ->groupBy('sales.payment_method')
            ->get()
            ->keyBy('payment_method');

        // ─── Stats du MOIS (admin/manager uniquement) ─────────────────────────
        $monthCount     = null;
        $monthRevenue   = null;
        $monthByPayment = collect();

        if (!$isStaff) {
            $monthStart     = Carbon::now()->startOfMonth();
            $monthCount     = Invoice::where('branch_id', $branchId)->where('issued_at', '>=', $monthStart)->count();
            $monthRevenue   = Invoice::where('branch_id', $branchId)->where('issued_at', '>=', $monthStart)->sum('total_amount');
            $monthByPayment = Invoice::where('invoices.branch_id', $branchId)
                ->where('invoices.issued_at', '>=', $monthStart)
                ->join('sales', 'invoices.sale_id', '=', 'sales.id')
                ->selectRaw('sales.payment_method, SUM(invoices.total_amount) as total, COUNT(invoices.id) as count')
                ->groupBy('sales.payment_method')
                ->get()
                ->keyBy('payment_method');
        }

        // ─── Ventes non facturées ─────────────────────────────────────────────
        $invoicedSaleIds = Invoice::where('branch_id', $branchId)->pluck('sale_id');
        $uninvoicedSales = Sale::where('branch_id', $branchId)
            ->whereNotIn('id', $invoicedSaleIds)
            ->latest('sold_at')->get();

        // ─── Données JSON pour aperçu JS ──────────────────────────────────────
        $invoicesDataQuery = Invoice::with([
            'sale.saleItems.product',
            'sale.saleServices.service',
            'sale.saleConfections.confection',
            'sale',
        ])->where('branch_id', $branchId);

        if ($isStaff) {
            $invoicesDataQuery->whereDate('issued_at', $today);
        }

        $invoicesData = $invoicesDataQuery
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

                foreach ($inv->sale->saleConfections ?? [] as $sc) {
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

        activity_log('invoices_viewed', "Consultation factures : {$invoices->total()} factures, CA du jour $dayRevenue");

        return view('facturation.index', compact(
            'invoices',
            'invoicesData',
            'uninvoicedSales',
            'isStaff',
            'dayCount',
            'dayRevenue',
            'dayByPayment',
            'monthCount',
            'monthRevenue',
            'monthByPayment',
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
        $logo = base64_encode(file_get_contents(public_path('logo.jpeg')));

        $pdf = PDF::loadView('facturation.pdf', compact('invoice', 'logo'))
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
            ]);

        activity_log('invoice_pdf_generated', "PDF facture générée : {$invoice->invoice_number}");
        return $pdf->download($invoice->invoice_number . '.pdf');
    }
}
