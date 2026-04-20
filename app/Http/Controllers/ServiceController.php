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
            'type'  => 'required|in:simple,semi_prive,prive',
            'price' => 'required|numeric|min:0',
        ]);

        Service::create($request->only('name', 'type', 'price'));

        return back()->with('success', 'Service créé avec succès.');
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'type'  => 'required|in:simple,semi_prive,prive',
            'price' => 'required|numeric|min:0',
        ]);

        $service->update($request->only('name', 'type', 'price'));

        return back()->with('success', 'Service mis à jour avec succès.');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return back()->with('success', 'Service supprimé.');
    }
}
