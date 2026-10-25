<?php

namespace App\Http\Controllers;

use App\Models\Actualite;
use App\Models\Offre;
use App\Models\Reponse;
use App\Models\Sondage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VisiteurController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless(auth()->user()?->role === 'visiteur', 403);

        $user = auth()->user();

        // 1. Tous les sondages actifs (initiés par l'ARTEC ou les opérateurs)
        $sondages = Sondage::with(['operateur', 'auteur'])
            ->where('actif', true)
            ->latest()
            ->get()
            ->map(function ($s) use ($user) {
                // Vérifier si ce visiteur a déjà répondu à ce sondage
                $maReponse = Reponse::where('sondage_id', $s->id)
                    ->where('user_id', $user->id)
                    ->first();

                return [
                    'id' => $s->id,
                    'titre' => $s->titre,
                    'operateur_id' => $s->operateur_id,
                    'operateur_nom' => $s->operateur?->nom ?? 'Opérateur',
                    'auteur_nom' => $s->auteur?->name ?? 'ARTEC',
                    'auteur_role' => $s->auteur?->role ?? 'artec',
                    'a_repondu' => (bool) $maReponse,
                    'ma_reponse' => $maReponse ? [
                        'sms' => (int) $maReponse->sms,
                        'voix' => (int) $maReponse->voix,
                        'internet' => (int) $maReponse->internet,
                        'credit' => (int) $maReponse->credit,
                        'satisfait' => (int) $maReponse->satisfait,
                        'created_at' => $maReponse->created_at?->format('d/m/Y H:i'),
                    ] : null,
                ];
            });

        // 2. Toutes les offres publiées par les 3 opérateurs (Yas, Airtel, Orange)
        $offres = Offre::with('operateur')
            ->latest()
            ->get()
            ->map(fn ($o) => [
                'id' => $o->id,
                'titre' => $o->titre,
                'description' => $o->description,
                'operateur_nom' => $o->operateur?->nom ?? 'Opérateur',
                'created_at' => $o->created_at?->format('d/m/Y'),
            ]);

        // 3. Toutes les actualités publiées (avec le lien web cliquable)
        $actualites = Actualite::with('operateur')
            ->latest()
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'titre' => $a->titre,
                'lien' => $a->lien,
                'operateur_nom' => $a->operateur?->nom ?? 'Opérateur',
                'created_at' => $a->created_at?->format('d/m/Y'),
            ]);

        return Inertia::render('visiteur/Dashboard', [
            'sondages' => $sondages,
            'offres' => $offres,
            'actualites' => $actualites,
            'visiteurName' => str_replace('@gmail.com', '', $user->email),
        ]);
    }

    public function storeReponse(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->role === 'visiteur', 403);

        $validated = $request->validate([
            'sondage_id' => ['required', 'exists:sondages,id'],
            'sms' => ['required', 'integer', 'min:0', 'max:10'],
            'voix' => ['required', 'integer', 'min:0', 'max:10'],
            'internet' => ['required', 'integer', 'min:0', 'max:10'],
            'credit' => ['required', 'integer', 'min:0', 'max:10'],
            'satisfait' => ['required', 'in:0,1'],
        ]);

        $user = auth()->user();

        // Enregistre ou met à jour l'avis (une seule réponse par sondage par visiteur)
        Reponse::updateOrCreate(
            [
                'sondage_id' => $validated['sondage_id'],
                'user_id' => $user->id,
            ],
            [
                'sms' => $validated['sms'],
                'voix' => $validated['voix'],
                'internet' => $validated['internet'],
                'credit' => $validated['credit'],
                'satisfait' => (int) $validated['satisfait'],
            ]
        );

        return redirect()->back()->with('success', 'Votre avis a été enregistré avec succès et transmis au régulateur.');
    }
}
