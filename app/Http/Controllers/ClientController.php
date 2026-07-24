<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Branch;
use App\Models\SaleItem;
use App\Models\Sale;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientController extends Controller
{
    // ── LIST ──────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Client::with('branch')
            ->withSum('sales as total_spent', 'total_amount')
            ->withCount('sales as total_visits')

            ->when($request->search, function ($q, $s) {
                $q->where(function ($q) use ($s) {
                    $q->where('name', 'like', "%$s%")
                        ->orWhere('phone', 'like', "%$s%");
                });
            })

            ->when(
                $request->branch_id,
                fn($q, $b) =>
                $q->where('branch_id', $b)
            )

            ->orderBy(
                match ($request->sort) {
                    'visits' => 'total_visits',
                    'name'   => 'name',
                    default  => 'total_spent',
                },
                $request->sort === 'name' ? 'asc' : 'desc'
            );

        $clients = $query->paginate(15)->withQueryString();

        return view('admin.clients.index', [
            'clients'      => $clients,
            'branches'     => Branch::orderBy('name')->get(),
            'totalClients' => Client::count(),
            'totalSpent'   => Client::sum('total_spent'),
            'totalVisits'  => Client::sum('total_visits'),
            'maxSpent'     => Client::max('total_spent') ?? 1,
        ]);
    }

    // ── CREATE ────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'phone'     => 'nullable|string|max:30',
            'address'   => 'nullable|string|max:500',
            'branch_id' => 'required|exists:branches,id',
            'notes'     => 'nullable|string|max:1000',
        ]);

        Client::create($data);

        return redirect()
            ->route('admin.clients.index')
            ->with('success', 'Client ajouté avec succès.');
    }

    // ── SHOW ──────────────────────────────────────────────────────────────────
    public function show(Client $client)
    {
        $client->load(['branch']);
        $totalSpent = Sale::where('client_id', $client->id)
            ->sum('total_amount');

        $totalVisits = Sale::where('client_id', $client->id)->count();

        $lastVisit = Sale::where('client_id', $client->id)
            ->latest('created_at')
            ->first();

        $avgTicket = $totalVisits > 0 ? $totalSpent / $totalVisits : 0;

        $sales = Sale::where('client_id', $client->id)
            ->latest()
            ->limit(20)
            ->get();

        $spendingByMonth = Sale::where('client_id', $client->id)
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month')
            ->selectRaw('SUM(total_amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        return view('admin.clients.show', [
            'client'          => $client,
            'totalSpent'      => $totalSpent,
            'totalVisits'     => $totalVisits,
            'avgTicket'       => $avgTicket,
            'lastVisit'       => $lastVisit,
            'sales'           => $sales,
            'spendingByMonth' => $spendingByMonth,
        ]);
    }
    // ── UPDATE ────────────────────────────────────────────────────────────────
    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'phone'     => 'nullable|string|max:30',
            'address'   => 'nullable|string|max:500',
            'branch_id' => 'required|exists:branches,id',
            'notes'     => 'nullable|string|max:1000',
        ]);

        $client->update($data);

        return redirect()
            ->route('admin.clients.index')
            ->with('success', 'Client modifié avec succès.');
    }

    // ── DELETE ────────────────────────────────────────────────────────────────
    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()
            ->route('admin.clients.index')
            ->with('success', 'Client supprimé.');
    }

    // ── EXPORT CSV ────────────────────────────────────────────────────────────
    public function export(Request $request): StreamedResponse
    {
        $clients = Client::with('branch')
            ->when($request->branch_id, fn($q, $b) => $q->where('branch_id', $b))
            ->orderByDesc('total_spent')
            ->get();

        $filename = 'clients_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($clients) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel
            fwrite($handle, "\xEF\xBB\xBF");

            // Header row
            fputcsv($handle, [
                'ID',
                'Nom',
                'Téléphone',
                'Adresse',
                'Agence',
                'Visites',
                'Total dépensé (FCFA)',
                // 'Niveau',
                'Notes',
                'Créé le',
            ], ';');

            foreach ($clients as $client) {
                fputcsv($handle, [
                    $client->id,
                    $client->name,
                    $client->phone ?? '',
                    $client->address ?? '',
                    $client->branch->name,
                    $client->total_visits,
                    number_format($client->total_spent, 2, ',', ' '),
                    // $client->tier,
                    $client->notes ?? '',
                    $client->created_at->format('d/m/Y'),
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
