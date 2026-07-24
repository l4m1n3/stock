<?php

namespace App\Http\Controllers;

use App\Models\ServiceSchedule;
use App\Models\Service;
use App\Models\Sale;
use Illuminate\Http\Request;

class ServiceScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceSchedule::with(['service', 'sale'])
            ->orderBy('scheduled_date', 'desc')
            ->orderBy('scheduled_time', 'asc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('scheduled_date', $request->date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('client_name', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhereHas('service', fn($s) => $s->where('name', 'like', "%{$search}%"));
            });
        }

        $schedules = $query->paginate(15)->withQueryString();

        $stats = [
            'total'     => ServiceSchedule::count(),
            'pending'   => ServiceSchedule::where('status', 'pending')->count(),
            'confirmed' => ServiceSchedule::where('status', 'confirmed')->count(),
            'today'     => ServiceSchedule::whereDate('scheduled_date', today())->count(),
        ];

        return view('schedules.index', compact('schedules', 'stats'));
    }

    public function create()
    {
        $services = Service::whereNot('name', 'Livraison')
            ->orderBy('name')
            ->get();
        $sales    = Sale::with('client')->latest()->take(50)->get();
        return view('schedules.create', compact('services', 'sales'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id'     => 'required|exists:services,id',
            'sale_id'        => 'nullable|exists:sales,id',
            'client_name'    => 'nullable|string|max:255',
            'contact_number' => 'required|string|max:30',
            'address'        => 'required|string|max:1000',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'nullable|date_format:H:i',
            'status'         => 'required|in:pending,confirmed,completed,cancelled',
            'notes'          => 'nullable|string|max:2000',
        ]);

        ServiceSchedule::create($validated);

        return redirect()->route('service-schedules.index')
            ->with('success', 'Prestation planifiée avec succès.');
    }

    public function show(ServiceSchedule $serviceSchedule)
    {
        $serviceSchedule->load(['service', 'sale']);
        return view('schedules.show', compact('serviceSchedule'));
    }

    public function edit(ServiceSchedule $serviceSchedule)
    {
        $services = Service::whereNot('name', 'Livraison')
            ->orderBy('name')
            ->get();
        $sales    = Sale::with('client')->latest()->take(50)->get();
        return view('schedules.edit', compact('serviceSchedule', 'services', 'sales'));
    }

    public function update(Request $request, ServiceSchedule $serviceSchedule)
    {
        $validated = $request->validate([
            'service_id'     => 'required|exists:services,id',
            'sale_id'        => 'nullable|exists:sales,id',
            'client_name'    => 'nullable|string|max:255',
            'contact_number' => 'required|string|max:30',
            'address'        => 'required|string|max:1000',
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'nullable|date_format:H:i',
            'status'         => 'required|in:pending,confirmed,completed,cancelled',
            'notes'          => 'nullable|string|max:2000',
        ]);

        $serviceSchedule->update($validated);

        return redirect()->route('service-schedules.index')
            ->with('success', 'Prestation mise à jour avec succès.');
    }

    public function destroy(ServiceSchedule $serviceSchedule)
    {
        $serviceSchedule->delete();
        return redirect()->route('service-schedules.index')
            ->with('success', 'Prestation supprimée.');
    }

    public function updateStatus(Request $request, ServiceSchedule $serviceSchedule)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
        ]);

        $serviceSchedule->update(['status' => $request->status]);

        return back()->with('success', 'Statut mis à jour.');
    }
    public function print(Request $request)
    {
        $logo = base64_encode(file_get_contents(public_path('logo.jpeg')));
        $query = ServiceSchedule::with(['service', 'sale'])
            ->orderBy('scheduled_date', 'asc')
            ->orderBy('scheduled_time', 'asc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('scheduled_date', $request->date);
        }

        $schedules = $query->get();

        return view('schedules.print', compact('schedules', 'logo'));
    }

    public function printSingle(ServiceSchedule $serviceSchedule)
    {
        $logo = base64_encode(file_get_contents(public_path('logo.jpeg')));
        $serviceSchedule->load(['service', 'sale']);

        return view('schedules.print', compact('serviceSchedule', 'logo'));
    }
}
