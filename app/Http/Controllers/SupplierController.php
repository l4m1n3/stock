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
        activity_log('suppliers_index', "Consultation fournisseurs : $suppliers->count() fournisseurs affichés");
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

        $supplier = Supplier::create([...$request->only('name', 'phone', 'email', 'address'),
            'branch_id' => auth()->user()->branch_id,
        ]);
        activity_log('supplier_created', "Fournisseur créé : {$supplier->name}");
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
        activity_log('supplier_updated', "Fournisseur mis à jour : {$supplier->name}");
        return back()->with('success', 'Fournisseur mis à jour.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        activity_log('supplier_deleted', "Fournisseur supprimé : {$supplier->name}");
        return back()->with('success', 'Fournisseur supprimé.');
    }
}