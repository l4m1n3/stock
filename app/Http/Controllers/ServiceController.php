<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;


class ServiceController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            // 'type'  => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $service = Service::create($request->only('name', 'price'));
        activity_log('service_created', "Service créé : {$service->name}");

        return back()->with('success', 'Service créé avec succès.');
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            // 'type'  => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $service->update($request->only('name', 'price'));
        activity_log('service_updated', "Service mis à jour : {$service->name}");

        return back()->with('success', 'Service mis à jour avec succès.');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        activity_log('service_deleted', "Service supprimé : {$service->name}");
        return back()->with('success', 'Service supprimé.');
    }
}
