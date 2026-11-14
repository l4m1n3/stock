<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        abort_if(!$user->branch_id, 403, 'Aucune agence associée à votre compte.');

        $suppliers = Supplier::where('branch_id', $user->branch_id)
            ->orderBy('name')
            ->get();

        activity_log(
            'suppliers_index',
            "Consultation fournisseurs : {$suppliers->count()} fournisseurs affichés"
        );

        return view('purchase.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        abort_if(!$user->branch_id, 403, 'Aucune agence associée à votre compte.');

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:30',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $supplier = Supplier::create([
            ...$validated,
            'branch_id' => $user->branch_id,
        ]);

        activity_log(
            'supplier_created',
            "Fournisseur créé : {$supplier->name}"
        );

        return back()->with('success', 'Fournisseur ajouté.');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $user = Auth::user();

        abort_if(!$user->branch_id, 403, 'Aucune agence associée à votre compte.');

        abort_unless(
            $supplier->branch_id == $user->branch_id,
            403,
            'Vous ne pouvez pas modifier ce fournisseur.'
        );

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:30',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $supplier->update($validated);

        activity_log(
            'supplier_updated',
            "Fournisseur mis à jour : {$supplier->name}"
        );

        return back()->with('success', 'Fournisseur mis à jour.');
    }

    // public function destroy(Supplier $supplier)
    // {
    //     $user = Auth::user();

    //     abort_if(!$user->branch_id, 403, 'Aucune agence associée à votre compte.');

    //     abort_unless(
    //         $supplier->branch_id == $user->branch_id,
    //         403,
    //         'Vous ne pouvez pas supprimer ce fournisseur.'
    //     );

    //     $name = $supplier->name;

    //     $supplier->delete();

    //     activity_log(
    //         'supplier_deleted',
    //         "Fournisseur supprimé : {$name}"
    //     );

    //     return back()->with('success', 'Fournisseur supprimé.');
    // }
    public function destroy(Supplier $supplier)
    {
        
        $user = Auth::user();

        abort_if(
            !$user->branch_id,
            403,
            'Aucune agence associée à votre compte.'
        );

        // Vérifier que le fournisseur appartient à l'agence
        abort_unless(
            (int) $supplier->branch_id === (int) $user->branch_id,
            403,
            'Vous ne pouvez pas supprimer ce fournisseur.'
        );

        // Empêcher la suppression si des commandes existent
        if ($supplier->purchaseOrders()->exists()) {
            return back()->with(
                'error',
                'Impossible de supprimer ce fournisseur : il possède déjà des bons de commande.'
            );
        }

        $name = $supplier->name;

        $supplier->delete();

        activity_log(
            'supplier_deleted',
            "Fournisseur supprimé : {$name}"
        );

        return back()->with(
            'success',
            'Le fournisseur a été supprimé avec succès.'
        );
    }
}
