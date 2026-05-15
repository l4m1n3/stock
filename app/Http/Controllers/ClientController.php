<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Branch;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientController extends Controller
{
    // ── LIST ──────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Client::with('branch')
            ->when($request->search, fn($q, $s) =>
                $q->where('name', 'like', "%$s%")->orWhere('phone', 'like', "%$s%"))
            ->when($request->branch_id, fn($q, $b) =>
                $q->where('branch_id', $b))
            ->orderBy(match ($request->sort) {
                'visits' => 'total_visits',
                'name'   => 'name',
                default  => 'total_spent',
            }, $request->sort === 'name' ? 'asc' : 'desc');

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
        $client->load(['branch', 'sales' => fn($q) => $q->latest()->limit(20)]);

        // Spending by month for the last 6 months
        $spendingByMonth = $client->sales()
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(total_price) as total')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        return view('admin.clients.show', [
            'client'          => $client,
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
                'ID', 'Nom', 'Téléphone', 'Adresse',
                'Agence', 'Visites', 'Total dépensé (FCFA)',
                'Niveau', 'Notes', 'Créé le',
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
                    $client->tier,
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