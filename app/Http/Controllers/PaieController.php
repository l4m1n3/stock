<?php

namespace App\Http\Controllers;

use App\Models\Paie;
use App\Models\User;
use Illuminate\Http\Request;
use PDF;
use Illuminate\Support\Facades\Log;

class PaieController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user_id;
        $year   = $request->year ?? now()->year;

        $paies = Paie::with('user')
            ->when($userId, function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->whereYear('created_at', $year)
            ->latest()
            ->paginate(10);

        $users = User::all();
        $years = Paie::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
        $totalSalaries = Paie::when($userId, function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->whereYear('created_at', $year)
            ->sum('salaire_net');
        $months = Paie::selectRaw('
        MONTH(created_at) as month,
        SUM(salaire_net) as net
    ')
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => \Carbon\Carbon::create()->month($item->month)->format('M'),
                    'net'   => (float) $item->net,
                ];
            });
        return view('admin.finances.paie', compact(
            'paies',
            'users',
            'years',
            'userId',
            'year',
            'totalSalaries',
            'months'
        ));
    }

    /* =========================
        STORE
    ========================== */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'         => 'required|exists:users,id',
            'periode_start'    => 'required|date',
            'periode_end'      => 'required|date|after_or_equal:periode_start',
            'salaire_brut'     => 'required|numeric|min:0',
            'total_primes'     => 'nullable|numeric|min:0',
            'total_retenues'   => 'nullable|numeric|min:0',
        ]);

        $paie = new Paie();
        $paie->fill($data);

        $paie->total_primes   = $data['total_primes'] ?? 0;
        $paie->total_retenues = $data['total_retenues'] ?? 0;

        $paie->salaire_net = $this->calculateNet(
            $paie->salaire_brut,
            $paie->total_primes,
            $paie->total_retenues
        );

        $paie->save();

        return back()->with('success', 'Paie créée avec succès');
    }

    /* =========================
        UPDATE
    ========================== */
    public function update(Request $request, Paie $paie)
    {
        $data = $request->validate([
            'salaire_brut'     => 'required|numeric|min:0',
            'total_primes'     => 'nullable|numeric|min:0',
            'total_retenues'   => 'nullable|numeric|min:0',
        ]);

        $paie->salaire_brut   = $data['salaire_brut'];
        $paie->total_primes   = $data['total_primes'] ?? 0;
        $paie->total_retenues = $data['total_retenues'] ?? 0;

        $paie->salaire_net = $this->calculateNet(
            $paie->salaire_brut,
            $paie->total_primes,
            $paie->total_retenues
        );

        $paie->save();

        return back()->with('success', 'Paie mise à jour avec succès');
    }

    /* =========================
        DELETE
    ========================== */
    public function destroy(Paie $paie)
    {
        $paie->delete();

        return back()->with('success', 'Paie supprimée');
    }

    /* =========================
        LOGIQUE MÉTIER CENTRALISÉE
    ========================== */
    private function calculateNet($brut, $primes = 0, $retenues = 0)
    {
        return ($brut + $primes) - $retenues;
    }
    public function pdf(Request $request)
    {
        $query = Paie::with('user');

        // 🔹 Filtre employé
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // 🔹 Gestion période
        if ($request->filled('periode_start') && $request->filled('periode_end')) {

            $periode_start = \Carbon\Carbon::parse($request->periode_start);
            $periode_end   = \Carbon\Carbon::parse($request->periode_end);

            $query->whereBetween('periode_start', [
                $periode_start,
                $periode_end
            ]);
        } else {

            $periode_start = now()->startOfMonth();
            $periode_end   = now()->endOfMonth();
        }

        $paies = $query->get();

        $total_net = $paies->sum('salaire_net');

        $entreprise = [
            'nom' => 'ilyken services',
            'adresse' => 'Niamey - Niger',
            'contact' => '+227 XX XX XX XX'
        ];

        $date_emission = now()->format('d/m/Y');

        $pdf = PDF::loadView('admin.finances.PaiePdf', compact(
            'paies',
            'periode_start',
            'periode_end',
            'total_net',
            'entreprise',
            'date_emission'
        ));

        return $pdf->setPaper('a4', 'landscape')
            ->download('etat_paie.pdf');
    }
     public function generateFichePaiement($id)
    {
        try {
            $employee = User::with([
                'paies' => function ($q) {
                    $q->orderBy('periode_start', 'desc');
                }
            ])->findOrFail($id);

            if ($employee->paies->isEmpty()) {
                abort(404, 'Aucune fiche de paie trouvée');
            }

            $data = [
                'paies' => $employee->paies,
                'employee' => $employee,
                // 'country' => $employee->country, // 👈 pays ici
                'entreprise' => [
                    'nom' => "Ilyken Services",
                    'adresse' => "Quartier Lossagoungou, Niamey Niger",
                    'contact' => "(+227) XX XX XX XX"
                ],
                'date_emission' => now()->format('d/m/Y')
            ];
            $logo = base64_encode(file_get_contents(public_path('logo.jpeg')));
            
            $pdf =PDF::loadView('admin.finances.fiche_paiement', $data, compact('logo'))
                ->setPaper('A4', 'portrait');

            return $pdf->download("fiche_paiement_{$employee->name}.pdf");
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return back()->with('error', 'Erreur génération fiche de paiement');
        }
    }
}
