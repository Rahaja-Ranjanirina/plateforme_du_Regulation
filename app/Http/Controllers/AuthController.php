<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function showLogin(): Response
    {
        return Inertia::render('auth/Login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['nullable', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        // Le visiteur peut se connecter directement avec son email sans mot de passe
        if ($user && $user->role === 'visiteur' && empty($request->password)) {
            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->route('visiteur.dashboard');
        }

        // Pour Admin, ARTEC, Opérateurs : authentification classique avec mot de passe
        if (empty($request->password) || ! Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            throw ValidationException::withMessages([
                'email' => 'Identifiants incorrects ou mot de passe requis pour ce compte.',
            ]);
        }

        $request->session()->regenerate();
        $user = $request->user();

        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'artec' => redirect()->route('artec.dashboard'),
            'operateur' => redirect()->route('operateur.dashboard'),
            'visiteur' => redirect()->route('visiteur.dashboard'),
            default => $this->logoutInvalidRole($request),
        };
    }

    public function showRegister(): Response
    {
        return Inertia::render('auth/Register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ]);

        // Nom du visiteur : partie avant @gmail.com
        $username = str_replace('@gmail.com', '', $data['email']);
        $username = explode('@', $username)[0];

        $user = User::create([
            'name' => $username,
            'email' => $data['email'],
            'password' => null,
            'role' => 'visiteur',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('visiteur.dashboard');
    }

    private function logoutInvalidRole(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
