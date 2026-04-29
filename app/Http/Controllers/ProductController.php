<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Service;
use App\Models\Confection;

class ProductController extends Controller
{
     public function index()
    {
        $branchId = auth()->user()->branch_id;
        $products = Product::where('branch_id', $branchId)->orderBy('name')->get();
        $services = Service::orderBy('name')->get();
        $confections = Confection::where('branch_id', $branchId)->orderBy('name')->get();
        return view('products.index', compact('products', 'services', 'confections'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string',
            'price'           => 'required|numeric|min:0',
            'stock_quantity'  => 'required|integer|min:0',
            'alert_threshold' => 'nullable|integer|min:0',
        ]);

        Product::create([
            'name'            => $request->name,
            'description'     => $request->description,
            'price'           => $request->price,
            'stock_quantity'  => $request->stock_quantity,
            'alert_threshold' => $request->alert_threshold ?? 10,
            'branch_id'       => auth()->user()->branch_id,
        ]);

        return back()->with('success', 'Produit créé avec succès.');
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string',
            'price'           => 'required|numeric|min:0',
            'stock_quantity'  => 'required|integer|min:0',
            'alert_threshold' => 'nullable|integer|min:0',
        ]);

        $product->update([
            'name'            => $request->name,
            'description'     => $request->description,
            'price'           => $request->price,
            'stock_quantity'  => $request->stock_quantity,
            'alert_threshold' => $request->alert_threshold ?? 10,
            'branch_id'       => auth()->user()->branch_id,
        ]);

        return back()->with('success', 'Produit mis à jour avec succès.');
    }

    public function destroy(Product $product)
    {
        if ($product->branch_id !== auth()->user()->branch_id) {
            return back()->with('error', 'Vous n\'avez pas la permission de supprimer ce produit.');
        }
        $product->delete();
        return back()->with('success', 'Produit supprimé.');
    }
}
