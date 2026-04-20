<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Service;

class ProductController extends Controller
{
     public function index()
    {
        $products = Product::orderBy('name')->get();
        $services = Service::orderBy('name')->get();

        return view('products.index', compact('products', 'services'));
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
        ]);

        return back()->with('success', 'Produit mis à jour avec succès.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return back()->with('success', 'Produit supprimé.');
    }
}
