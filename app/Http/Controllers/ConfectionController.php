<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Confection;
use App\Models\Product;

class ConfectionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'making_price' => 'required|numeric|min:0',
            'products'     => 'nullable|array',
            'products.*.id'       => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        $confection = Confection::create([
            'name'         => $request->name,
            'description'  => $request->description,
            'making_price' => $request->making_price,
            'branch_id'    => auth()->user()->branch_id,
        ]);

        // Attacher les produits composants
        if ($request->products) {
            foreach ($request->products as $item) {
                $confection->products()->attach($item['id'], [
                    'quantity' => $item['quantity'],
                ]);
            }
        }
        activity_log('confection_created', "Confection créée : {$confection->name}");
        return back()->with('success', 'Confection créée avec succès.');
    }

    public function update(Request $request, Confection $confection)
    {
        // Vérification de la branche
        if ($confection->branch_id !== auth()->user()->branch_id) {
            return back()->with('error', 'Permission refusée.');
        }

        $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'making_price' => 'required|numeric|min:0',
            'products'     => 'nullable|array',
            'products.*.id'       => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        $confection->update([
            'name'         => $request->name,
            'description'  => $request->description,
            'making_price' => $request->making_price,
        ]);

        // Sync les produits composants
        $sync = [];
        if ($request->products) {
            foreach ($request->products as $item) {
                $sync[$item['id']] = ['quantity' => $item['quantity']];
            }
        }
        $confection->products()->sync($sync);
        activity_log('confection_updated', "Confection mise à jour : {$confection->name}");
        return back()->with('success', 'Confection mise à jour avec succès.');
    }

    public function destroy(Confection $confection)
    {
        if ($confection->branch_id !== auth()->user()->branch_id) {
            return back()->with('error', 'Permission refusée.');
        }

        $confection->delete();
        activity_log('confection_deleted', "Confection supprimée : {$confection->name}");
        return back()->with('success', 'Confection supprimée.');
    }
}
