<?php

namespace App\Http\Controllers;

use App\Models\Actualite;
use App\Models\Offre;
use App\Models\Operateur;
use App\Models\Reponse;
use App\Models\Sondage;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function index(): Response
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $stats = [
            'total_users' => User::count(),
            'total_artec' => User::where('role', 'artec')->count(),
            'total_operateurs' => User::where('role', 'operateur')->count(),
            'total_visiteurs' => User::where('role', 'visiteur')->count(),
            'total_sondages' => Sondage::count(),
            'total_reponses' => Reponse::count(),
            'total_offres' => Offre::count(),
            'total_actualites' => Actualite::count(),
        ];

        $artecUsers = User::where('role', 'artec')
            ->latest()
            ->get(['id', 'name', 'email', 'role', 'created_at']);

        $operateurUsers = User::where('role', 'operateur')
            ->with('operateur')
            ->latest()
            ->get(['id', 'name', 'email', 'role', 'created_at']);

        $visiteurs = User::where('role', 'visiteur')
            ->latest()
            ->take(20)
            ->get(['id', 'name', 'email', 'role', 'created_at']);

        $operateurs = Operateur::all(['id', 'nom', 'user_id']);

        return Inertia::render('admin/Dashboard', [
            'stats' => $stats,
            'artecUsers' => $artecUsers,
            'operateurUsers' => $operateurUsers,
            'visiteurs' => $visiteurs,
            'operateurs' => $operateurs,
        ]);
    }

    public function storeUser(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:artec,operateur'],
            'operateur_id' => ['nullable', 'exists:operateurs,id'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        if ($validated['role'] === 'operateur' && ! empty($validated['operateur_id'])) {
            Operateur::where('id', $validated['operateur_id'])->update(['user_id' => $user->id]);
        }

        return redirect()->back()->with('success', "Le compte {$user->name} a été créé avec succès.");
    }

    public function destroyUser(User $user): RedirectResponse
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        if ($user->role === 'visiteur') {
            return redirect()->back()->with('error', 'Seul le visiteur peut gérer son compte personnel.');
        }

        // Dissocier l'opérateur si nécessaire
        Operateur::where('user_id', $user->id)->update(['user_id' => null]);

        $userName = $user->name;
        $user->delete();

        return redirect()->back()->with('success', "Le compte {$userName} a été supprimé.");
    }
}
