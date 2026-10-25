<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Operateur;
use App\Models\Reponse;
use App\Models\Sondage;
use App\Services\RegulationAiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ArtecController extends Controller
{
    public function index(Request $request, RegulationAiService $aiService): Response
    {
        abort_unless(auth()->user()?->role === 'artec', 403);

        $operateurs = Operateur::all();

        // Opérateur sélectionné (par défaut le premier : Yas ou par paramètre ?operateur_id=...)
        $selectedOperateurId = $request->query('operateur_id');
        $selectedOperateur = $selectedOperateurId
            ? $operateurs->firstWhere('id', (int) $selectedOperateurId)
            : $operateurs->first();

        if (! $selectedOperateur && $operateurs->isNotEmpty()) {
            $selectedOperateur = $operateurs->first();
        }

        // Réponses pour l'opérateur sélectionné (ou pour tous)
        $reponsesQuery = Reponse::with(['sondage.operateur', 'visiteur'])
            ->latest();

        if ($selectedOperateur) {
            $reponsesQuery->whereHas('sondage', function ($q) use ($selectedOperateur) {
                $q->where('operateur_id', $selectedOperateur->id);
            });
        }

        $rawReponses = $reponsesQuery->get();

        // Formatage des 6 colonnes demandées :
        // 1. Nom du visiteur (email sans @gmail.com)
        // 2. SMS (note / 10)
        // 3. Voix (note / 10)
        // 4. Internet (note / 10)
        // 5. Crédit (note / 10)
        // 6. Satisfait (0 ou 1)
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
                'operateur_nom' => $r->sondage?->operateur?->nom ?? '—',
                'sondage_titre' => $r->sondage?->titre ?? '—',
                'created_at' => $r->created_at?->format('d/m/Y H:i'),
            ];
        });

        // Décompte binaire des 0 vs 1
        $countSatisfait = $formattedReponses->where('satisfait', 1)->count();
        $countInsatisfait = $formattedReponses->where('satisfait', 0)->count();
        $totalAvis = $formattedReponses->count();

        // Règle demandée :
        // Si count(0) > count(1) => ROUGE (alerte et envoi de message d'avertissement requis)
        // Si count(1) >= count(0) => VERT
        $isCritical = $totalAvis > 0 && ($countInsatisfait > $countSatisfait);
        $resultColor = $isCritical ? 'red' : 'green';

        // Historique des messages envoyés à cet opérateur
        $messagesEnvoyes = Message::with('operateur')
            ->where('expediteur_id', auth()->id())
            ->when($selectedOperateur, fn ($q) => $q->where('operateur_id', $selectedOperateur->id))
            ->latest()
            ->take(10)
            ->get();

        // Analyse IA / Machine Learning Dynamique (Dense(2, Softmax) sur SMS, Voix, Crédit, Internet -> 0/1)
        $aiAnalysis = $selectedOperateur
            ? $aiService->trainAndPredictDynamic($selectedOperateur->id)
            : null;

        // Statistiques globales par opérateur
        $operateursStats = $operateurs->map(function ($op) {
            $rep = Reponse::whereHas('sondage', fn ($q) => $q->where('operateur_id', $op->id))->get();
            $sat = $rep->where('satisfait', 1)->count();
            $insat = $rep->where('satisfait', 0)->count();

            return [
                'id' => $op->id,
                'nom' => $op->nom,
                'total_reponses' => $rep->count(),
                'satisfait_1' => $sat,
                'insatisfait_0' => $insat,
                'is_red' => $rep->count() > 0 && ($insat > $sat),
            ];
        });

        return Inertia::render('artec/Dashboard', [
            'operateurs' => $operateurs,
            'selectedOperateur' => $selectedOperateur,
            'reponses' => $formattedReponses,
            'summary' => [
                'total' => $totalAvis,
                'satisfait_1' => $countSatisfait,
                'insatisfait_0' => $countInsatisfait,
                'is_critical' => $isCritical,
                'color' => $resultColor,
                'satisfaction_rate' => $totalAvis > 0 ? round(($countSatisfait / $totalAvis) * 100, 1) : 0,
            ],
            'messagesEnvoyes' => $messagesEnvoyes,
            'aiAnalysis' => $aiAnalysis,
            'operateursStats' => $operateursStats,
        ]);
    }

    public function storeSondage(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->role === 'artec', 403);

        $validated = $request->validate([
            'operateur_id' => ['required', 'exists:operateurs,id'],
            'titre' => ['required', 'string', 'max:255'],
        ]);

        Sondage::create([
            'operateur_id' => $validated['operateur_id'],
            'user_id' => auth()->id(),
            'titre' => $validated['titre'],
            'actif' => true,
        ]);

        return redirect()->back()->with('success', 'Le sondage a été publié avec succès vers les visiteurs.');
    }

    public function sendMessage(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->role === 'artec', 403);

        $validated = $request->validate([
            'operateur_id' => ['required', 'exists:operateurs,id'],
            'sujet' => ['required', 'string', 'max:255'],
            'contenu' => ['required', 'string'],
        ]);

        Message::create([
            'expediteur_id' => auth()->id(),
            'operateur_id' => $validated['operateur_id'],
            'sujet' => $validated['sujet'],
            'contenu' => $validated['contenu'],
            'lu' => false,
        ]);

        return redirect()->back()->with('success', "Le message d'avertissement a été envoyé à l'opérateur.");
    }
}
