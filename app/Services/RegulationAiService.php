<?php

namespace App\Services;

use App\Models\Reponse;

class RegulationAiService
{
    /**
     * Les 2 classes correspondant au choix binaire du visiteur :
     * 0 = Non satisfait, 1 = Satisfait
     */
    public array $classNames = ['Non satisfait (0)', 'Satisfait (1)'];

    /**
     * Noms des 4 caractéristiques issues du sondage
     */
    public array $featureNames = ['SMS', 'Voix', 'Crédit', 'Internet'];

    /**
     * Analyse et entraîne dynamiquement le réseau de neurones Keras / Softmax
     * sur les données réelles saisies par les visiteurs pour un opérateur donné
     *
     * Entrées X (4 colonnes) : [SMS, Voix, Crédit, Internet] (notes sur 10)
     * Sortie y (binaire 0 ou 1) : [1, 0] si 0, [0, 1] si 1
     */
    public function trainAndPredictDynamic(int $operateurId, int $epochs = 1800): array
    {
        // 1. Récupération dynamique des réponses des visiteurs pour cet opérateur
        $reponses = Reponse::with('visiteur')
            ->whereHas('sondage', fn ($q) => $q->where('operateur_id', $operateurId))
            ->get();

        $dossiers = [];
        $X = [];
        $y = [];

        foreach ($reponses as $idx => $r) {
            $email = $r->visiteur?->email ?? '';
            $visiteurNom = str_replace('@gmail.com', '', $email);
            $visiteurNom = explode('@', $visiteurNom)[0] ?: ($r->visiteur?->name ?? "Visiteur #" . ($idx + 1));

            // 4 colonnes X : SMS, Voix, Crédit, Internet (notes sur 10)
            $rowX = [
                (float) $r->sms,
                (float) $r->voix,
                (float) $r->credit,
                (float) $r->internet,
            ];

            // Choix binaire du visiteur : 0 (Non satisfait) ou 1 (Satisfait)
            $isSatisfait = (int) $r->satisfait === 1 ? 1 : 0;
            $rowY = $isSatisfait === 1 ? [0.0, 1.0] : [1.0, 0.0];

            $X[] = $rowX;
            $y[] = $rowY;

            $dossiers[] = [
                'id' => $idx + 1,
                'visiteur_nom' => $visiteurNom,
                'visiteur_email' => $email,
                'sms' => (int) $r->sms,
                'voix' => (int) $r->voix,
                'credit' => (int) $r->credit,
                'internet' => (int) $r->internet,
                'satisfait' => $isSatisfait,
                'true_class' => $this->classNames[$isSatisfait],
            ];
        }

        // Si aucune réponse n'est encore saisie pour cet opérateur, initialisation de 5 réponses de calibrage
        if (count($X) === 0) {
            $X = [
                [7.0, 8.0, 7.0, 8.0],
                [3.0, 4.0, 3.0, 2.0],
                [8.0, 7.0, 8.0, 9.0],
                [4.0, 3.0, 4.0, 3.0],
            ];
            $y = [
                [0.0, 1.0], // 1
                [1.0, 0.0], // 0
                [0.0, 1.0], // 1
                [1.0, 0.0], // 0
            ];
            $dossiers = [
                ['id' => 1, 'visiteur_nom' => 'Exemple_1', 'visiteur_email' => 'exemple1@gmail.com', 'sms' => 7, 'voix' => 8, 'credit' => 7, 'internet' => 8, 'satisfait' => 1, 'true_class' => $this->classNames[1]],
                ['id' => 2, 'visiteur_nom' => 'Exemple_2', 'visiteur_email' => 'exemple2@gmail.com', 'sms' => 3, 'voix' => 4, 'credit' => 3, 'internet' => 2, 'satisfait' => 0, 'true_class' => $this->classNames[0]],
                ['id' => 3, 'visiteur_nom' => 'Exemple_3', 'visiteur_email' => 'exemple3@gmail.com', 'sms' => 8, 'voix' => 7, 'credit' => 8, 'internet' => 9, 'satisfait' => 1, 'true_class' => $this->classNames[1]],
                ['id' => 4, 'visiteur_nom' => 'Exemple_4', 'visiteur_email' => 'exemple4@gmail.com', 'sms' => 4, 'voix' => 3, 'credit' => 4, 'internet' => 3, 'satisfait' => 0, 'true_class' => $this->classNames[0]],
            ];
        }

        $numSamples = count($X);
        $numFeatures = 4; // SMS, Voix, Crédit, Internet
        $numClasses = 2;  // 0 (Non satisfait), 1 (Satisfait)

        // 2. Normalisation (StandardScaler : moyenne et écart-type de chaque colonne)
        $mean = array_fill(0, $numFeatures, 0.0);
        $std = array_fill(0, $numFeatures, 0.0);

        for ($j = 0; $j < $numFeatures; $j++) {
            $sum = 0.0;
            for ($i = 0; $i < $numSamples; $i++) {
                $sum += $X[$i][$j];
            }
            $mean[$j] = $sum / $numSamples;

            $sqDiff = 0.0;
            for ($i = 0; $i < $numSamples; $i++) {
                $sqDiff += pow($X[$i][$j] - $mean[$j], 2);
            }
            $std[$j] = sqrt($sqDiff / $numSamples) ?: 1.0;
        }

        $X_scaled = [];
        for ($i = 0; $i < $numSamples; $i++) {
            $X_scaled[$i] = [];
            for ($j = 0; $j < $numFeatures; $j++) {
                $X_scaled[$i][$j] = ($X[$i][$j] - $mean[$j]) / $std[$j];
            }
        }

        // 3. Modèle Keras : Dense(2, activation='softmax', input_shape=(4,))
        // Initialisation des poids (4 x 2) et des biais (2)
        $weights = [];
        for ($j = 0; $j < $numFeatures; $j++) {
            $weights[$j] = [];
            for ($k = 0; $k < $numClasses; $k++) {
                $weights[$j][$k] = ((mt_rand(0, 10000) / 10000.0) - 0.5) * 0.2;
            }
        }
        $bias = array_fill(0, $numClasses, 0.0);

        // 4. Entraînement : descente de gradient + categorical crossentropy
        $epochs = max(50, $epochs);
        $learningRate = 0.05;
        $lossHistory = [];
        $accHistory = [];

        for ($epoch = 0; $epoch < $epochs; $epoch++) {
            $totalLoss = 0.0;
            $correct = 0;
            $probs = [];

            // Forward
            for ($i = 0; $i < $numSamples; $i++) {
                $z = array_fill(0, $numClasses, 0.0);
                for ($k = 0; $k < $numClasses; $k++) {
                    $sum = $bias[$k];
                    for ($j = 0; $j < $numFeatures; $j++) {
                        $sum += $X_scaled[$i][$j] * $weights[$j][$k];
                    }
                    $z[$k] = $sum;
                }

                $p = $this->softmax($z);
                $probs[$i] = $p;

                // Loss
                for ($k = 0; $k < $numClasses; $k++) {
                    if ($y[$i][$k] > 0) {
                        $totalLoss -= log(max($p[$k], 1e-12));
                    }
                }

                if ($this->argmax($p) === $this->argmax($y[$i])) {
                    $correct++;
                }
            }

            // Gradients
            $gradW = array_fill(0, $numFeatures, array_fill(0, $numClasses, 0.0));
            $gradB = array_fill(0, $numClasses, 0.0);

            for ($i = 0; $i < $numSamples; $i++) {
                for ($k = 0; $k < $numClasses; $k++) {
                    $diff = ($probs[$i][$k] - $y[$i][$k]) / $numSamples;
                    $gradB[$k] += $diff;
                    for ($j = 0; $j < $numFeatures; $j++) {
                        $gradW[$j][$k] += $X_scaled[$i][$j] * $diff;
                    }
                }
            }

            // Update
            for ($k = 0; $k < $numClasses; $k++) {
                $bias[$k] -= $learningRate * $gradB[$k];
                for ($j = 0; $j < $numFeatures; $j++) {
                    $weights[$j][$k] -= $learningRate * $gradW[$j][$k];
                }
            }

            // Échantillonnage courbe
            if ($epoch % 30 === 0 || $epoch === $epochs - 1) {
                $lossHistory[] = round($totalLoss / $numSamples, 4);
                $accHistory[] = round(($correct / $numSamples) * 100, 1);
            }
        }

        // 5. Probabilités Softmax et Prédictions par dossier
        $predictions = [];
        $y_true = [];
        $y_pred = [];

        for ($i = 0; $i < $numSamples; $i++) {
            $z = array_fill(0, $numClasses, 0.0);
            for ($k = 0; $k < $numClasses; $k++) {
                $sum = $bias[$k];
                for ($j = 0; $j < $numFeatures; $j++) {
                    $sum += $X_scaled[$i][$j] * $weights[$j][$k];
                }
                $z[$k] = $sum;
            }

            $p = $this->softmax($z);
            $trueIdx = $this->argmax($y[$i]);
            $predIdx = $this->argmax($p);

            $y_true[] = $trueIdx;
            $y_pred[] = $predIdx;

            $predictions[] = [
                'dossier_id' => $dossiers[$i]['id'],
                'visiteur_nom' => $dossiers[$i]['visiteur_nom'],
                'visiteur_email' => $dossiers[$i]['visiteur_email'],
                'features' => $X[$i], // [SMS, Voix, Crédit, Internet]
                'prob_0_non_satisfait' => round($p[0], 4),
                'prob_1_satisfait' => round($p[1], 4),
                'total_prob' => round($p[0] + $p[1], 1), // Somme = 1.0
                'y_true' => $trueIdx,
                'y_pred' => $predIdx,
                'true_class' => $this->classNames[$trueIdx],
                'pred_class' => $this->classNames[$predIdx],
                'is_correct' => $trueIdx === $predIdx,
            ];
        }

        // 6. Matrice de confusion (2 x 2)
        // [ [TN, FP], [FN, TP] ]
        $confusionMatrix = [
            [0, 0],
            [0, 0],
        ];

        for ($i = 0; $i < $numSamples; $i++) {
            $confusionMatrix[$y_true[$i]][$y_pred[$i]]++;
        }

        // 7. Matrice des poids (4 entrées x 2 sorties)
        $weightsMatrix = [];
        foreach ($this->featureNames as $j => $fName) {
            $weightsMatrix[] = [
                'feature' => $fName,
                'poids_0_non_satisfait' => round($weights[$j][0], 4),
                'poids_1_satisfait' => round($weights[$j][1], 4),
            ];
        }

        $formattedBiases = [
            'biais_0_non_satisfait' => round($bias[0], 4),
            'biais_1_satisfait' => round($bias[1], 4),
        ];

        // 8. Rapport de classification (Précision, Rappel, F1)
        $classificationReport = $this->computeClassificationReport($confusionMatrix, $numSamples);

        // 9. Bilan global pour ARTEC : Compte des 0 vs Compte des 1
        $count0 = count(array_filter($y_true, fn ($val) => $val === 0));
        $count1 = count(array_filter($y_true, fn ($val) => $val === 1));
        $isCritical = $count0 > $count1;
        $color = $isCritical ? 'red' : 'green';

        return [
            'num_samples' => $numSamples,
            'features_columns' => $this->featureNames,
            'classes' => $this->classNames,
            'predictions' => $predictions,
            'weights_matrix' => $weightsMatrix,
            'biases' => $formattedBiases,
            'confusion_matrix' => $confusionMatrix,
            'classification_report' => $classificationReport,
            'history' => [
                'loss' => $lossHistory,
                'accuracy' => $accHistory,
            ],
            'ratio' => [
                'count_0_non_satisfait' => $count0,
                'count_1_satisfait' => $count1,
                'total' => $numSamples,
                'color' => $color,
                'is_critical' => $isCritical,
                'message' => $isCritical
                    ? "Le nombre de 0 ({$count0}) est supérieur au nombre de 1 ({$count1}) : résultat en ROUGE ➔ envoyer un avertissement officiel."
                    : "Le nombre de 1 ({$count1}) dépasse ou égale le nombre de 0 ({$count0}) : résultat en VERT ➔ qualité de service conforme.",
            ],
        ];
    }

    private function softmax(array $z): array
    {
        $max = max($z);
        $exp = array_map(fn ($val) => exp($val - $max), $z);
        $sum = array_sum($exp) ?: 1.0;
        return array_map(fn ($val) => $val / $sum, $exp);
    }

    private function argmax(array $arr): int
    {
        $maxVal = -INF;
        $maxIdx = 0;
        foreach ($arr as $idx => $val) {
            if ($val > $maxVal) {
                $maxVal = $val;
                $maxIdx = $idx;
            }
        }
        return $maxIdx;
    }

    private function computeClassificationReport(array $cm, int $total): array
    {
        $report = [];
        foreach ($this->classNames as $idx => $name) {
            $tp = $cm[$idx][$idx];
            $fp = $cm[1 - $idx][$idx];
            $fn = $cm[$idx][1 - $idx];

            $precision = ($tp + $fp) > 0 ? round(($tp / ($tp + $fp)) * 100, 1) : 0.0;
            $recall = ($tp + $fn) > 0 ? round(($tp / ($tp + $fn)) * 100, 1) : 0.0;
            $f1 = ($precision + $recall) > 0 ? round((2 * $precision * $recall) / ($precision + $recall), 1) : 0.0;
            $support = $tp + $fn;

            $report[] = [
                'classe' => $name,
                'precision' => $precision,
                'recall' => $recall,
                'f1_score' => $f1,
                'support' => $support,
            ];
        }

        $correct = $cm[0][0] + $cm[1][1];
        $accuracy = $total > 0 ? round(($correct / $total) * 100, 1) : 0.0;

        return [
            'classes' => $report,
            'accuracy' => $accuracy,
        ];
    }
}
