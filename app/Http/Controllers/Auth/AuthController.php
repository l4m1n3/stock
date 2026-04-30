<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Afficher le formulaire de login
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Traitement du login
     */
 public function login(Request $request)
{
    $credentials = $request->validate([
        'email'    => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {

        $request->session()->regenerate();

        $user = Auth::user();

        // ✅ LOG AVANT RETURN
        activity_log('login_success', "Connexion réussie : {$user->email}");

        if ($user->role === 'admin') {
            return redirect()->route('admin.index')
                ->with('success', 'Bienvenue Admin 👋');
        }

        return redirect()->route('dashboard')
            ->with('success', 'Connexion réussie 👋');
    }

    // ❌ LOG ÉCHEC
    activity_log('login_failed', "Échec connexion : {$request->email}");

    return back()->withErrors([
        'email' => 'Email ou mot de passe incorrect',
    ])->onlyInput('email');
}

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Déconnecté avec succès');
    }
}
