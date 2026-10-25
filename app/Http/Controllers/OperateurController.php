<?php

namespace App\Http\Controllers;

use App\Models\Actualite;
use App\Models\Message;
use App\Models\Offre;
use App\Models\Operateur;
use App\Models\Reponse;
use App\Models\Sondage;
use App\Services\RegulationAiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OperateurController extends Controller
{
    public function index(Request $request, RegulationAiService $aiService): Response
    {
        abort_unless(auth()->user()?->role === 'operateur', 403);

        $user = auth()->user();
        $operateur = $user->operateur
            ?? Operateur::where('user_id', $user->id)->first()
            ?? Operateur::where('nom', 'like', '%' . $user->name . '%')->first();

        // Si l'opérateur n'existe pas encore pour ce compte, on le crée
        if (! $operateur) {
            $operateur = Operateur::create([
                'nom' => $user->name ?: 'Opérateur',
                'user_id' => $user->id,
            ]);
        }

        // Automatisation demandée :
        // "tout son automatisé, dès que les opérateurs accède sur leur compte le sondage sera envoyé automatiquement vers artec"
        // On s'assure qu'un sondage actif existe pour cet opérateur et est synchronisé vers ARTEC et les visiteurs
        $existingSondage = Sondage::where('operateur_id', $operateur->id)
            ->where('actif', true)
            ->first();

        if (! $existingSondage) {
            $existingSondage = Sondage::create([
                'operateur_id' => $operateur->id,
                'user_id' => $user->id,
                'titre' => "Sondage de satisfaction — {$operateur->nom} Madagascar",
                'actif' => true,
            ]);
        }

        // Réponses collectées pour cet opérateur (Tableau à 6 colonnes)
        $rawReponses = Reponse::with('visiteur')
            ->whereHas('sondage', fn ($q) => $q->where('operateur_id', $operateur->id))
            ->latest()
            ->get();

        $formattedReponses = $rawReponses->map(function ($r) {
            $email = $r->visiteur?->email ?? '';
            $visiteurNom = str_replace('@gmail.com', '', $email);
            $visiteurNom = explode('@', $visiteurNom)[0] ?: ($r->visiteur?->name ?? 'Anonyme');

            return [
                'id' => $r->id,
                'visiteur_nom' => $visiteurNom,
                'visiteur_email' => $email,
                'sms' => (int) $r->sms,
                'voix' => (int) $r->voix,
                'internet' => (int) $r->internet,
                'credit' => (int) $r->credit,
                'satisfait' => (int) $r->satisfait, // 0 ou 1
                'created_at' => $r->created_at?->format('d/m/Y H:i'),
            ];
        });

        // Calcul 0 vs 1
        $countSatisfait = $formattedReponses->where('satisfait', 1)->count();
        $countInsatisfait = $formattedReponses->where('satisfait', 0)->count();
        $totalAvis = $formattedReponses->count();
        $isCritical = $totalAvis > 0 && ($countInsatisfait > $countSatisfait);
        $resultColor = $isCritical ? 'red' : 'green';

        // Messages reçus via l'ARTEC
        $messagesArtec = Message::with('expediteur')
            ->where('operateur_id', $operateur->id)
            ->latest()
            ->get();

        // Actualités publiées par l'opérateur
        $actualites = Actualite::where('operateur_id', $operateur->id)
            ->latest()
            ->get();

        // Offres publiées par l'opérateur
        $offres = Offre::where('operateur_id', $operateur->id)
            ->latest()
            ->get();

        // Analyse IA / Machine Learning Dynamique (Dense(2, Softmax) sur SMS, Voix, Crédit, Internet -> 0/1)
        $aiAnalysis = $aiService->trainAndPredictDynamic($operateur->id, 200);

        return Inertia::render('operateur/Dashboard', [
            'operateur' => $operateur,
            'activeSondage' => $existingSondage,
            'reponses' => $formattedReponses,
            'summary' => [
                'total' => $totalAvis,
                'satisfait_1' => $countSatisfait,
                'insatisfait_0' => $countInsatisfait,
                'is_critical' => $isCritical,
                'color' => $resultColor,
                'satisfaction_rate' => $totalAvis > 0 ? round(($countSatisfait / $totalAvis) * 100, 1) : 0,
            ],
            'messagesArtec' => $messagesArtec,
            'actualites' => $actualites,
            'offres' => $offres,
            'aiAnalysis' => $aiAnalysis,
        ]);
    }

    public function storeSondage(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->role === 'operateur', 403);

        $validated = $request->validate([
            'titre' => ['required', 'string', 'max:255'],
        ]);

        $user = auth()->user();
        $operateur = $user->operateur
            ?? Operateur::where('user_id', $user->id)->first()
            ?? Operateur::where('nom', 'like', '%' . $user->name . '%')->first();

        if ($operateur) {
            Sondage::create([
                'operateur_id' => $operateur->id,
                'user_id' => $user->id,
                'titre' => $validated['titre'],
                'actif' => true,
            ]);
        }

        return redirect()->back()->with('success', 'Votre sondage a été publié avec succès vers les visiteurs et transmis à l’ARTEC.');
    }

    public function storeActualite(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->role === 'operateur', 403);

        $validated = $request->validate([
            'titre' => ['required', 'string', 'max:255'],
            'lien' => ['required', 'url', 'max:255'],
        ]);

        $user = auth()->user();
        $operateur = $user->operateur
            ?? Operateur::where('user_id', $user->id)->first()
            ?? Operateur::where('nom', 'like', '%' . $user->name . '%')->first();

        if ($operateur) {
            Actualite::create([
                'operateur_id' => $operateur->id,
                'titre' => $validated['titre'],
                'lien' => $validated['lien'],
            ]);
        }

        return redirect()->back()->with('success', 'L’actualité a été publiée et est désormais visible par tous les visiteurs.');
    }

    public function storeOffre(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->role === 'operateur', 403);

        $validated = $request->validate([
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ]);

        $user = auth()->user();
        $operateur = $user->operateur
            ?? Operateur::where('user_id', $user->id)->first()
            ?? Operateur::where('nom', 'like', '%' . $user->name . '%')->first();

        if ($operateur) {
            Offre::create([
                'operateur_id' => $operateur->id,
                'titre' => $validated['titre'],
                'description' => $validated['description'],
            ]);
        }

        return redirect()->back()->with('success', 'L’offre commerciale a été ajoutée.');
    }

    public function markMessageRead(Message $message): RedirectResponse
    {
        abort_unless(auth()->user()?->role === 'operateur', 403);

        $message->update(['lu' => true]);

        return redirect()->back()->with('success', 'Le message a été marqué comme lu.');
    }
}
