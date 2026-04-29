<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $branchId  = auth()->user()->branch_id;
        $suppliers = Supplier::where('branch_id', $branchId)->orderBy('name')->get();
        return view('suppliers.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:30',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        Supplier::create([...$request->only('name', 'phone', 'email', 'address'),
            'branch_id' => auth()->user()->branch_id,
        ]);

        return back()->with('success', 'Fournisseur ajouté.');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:30',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $supplier->update($request->only('name', 'phone', 'email', 'address'));
        return back()->with('success', 'Fournisseur mis à jour.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return back()->with('success', 'Fournisseur supprimé.');
    }
}