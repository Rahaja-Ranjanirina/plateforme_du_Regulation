#!/usr/bin/env python3
"""
Script dynamique de classification pour le sondage ARTEC.

Cinq points essentiels :
- 4 entrées : SMS, Voix, Internet, Crédit
- sortie binaire : 1 si satisfait, 0 sinon
- les données peuvent venir d'une liste, d'un CSV ou d'un JSON
- le modèle utilise Softmax sur 2 classes
- la règle ARTEC est : 1 >= 0 => VERT, 0 > 1 => ROUGE
"""

import argparse
import csv
import json
from pathlib import Path

import numpy as np

FEATURE_NAMES = ["SMS", "Voix", "Internet", "Crédit"]
CLASS_NAMES = ["Non satisfait (0)", "Satisfait (1)"]


def load_rows(file_path: str | None = None, json_text: str | None = None):
    if file_path:
        path = Path(file_path)
        if path.suffix.lower() == ".csv":
            with path.open("r", encoding="utf-8", newline="") as handle:
                reader = csv.DictReader(handle)
                rows = []
                for row in reader:
                    rows.append([
                        float(row.get("sms", 0)),
                        float(row.get("voix", 0)),
                        float(row.get("internet", 0)),
                        float(row.get("credit", 0)),
                        int(row.get("satisfait", 0)),
                    ])
            return rows

        if path.suffix.lower() in {".json", ".txt"}:
            with path.open("r", encoding="utf-8") as handle:
                payload = json.load(handle)
            return normalize_payload(payload)

    if json_text:
        payload = json.loads(json_text)
        return normalize_payload(payload)

    return DEFAULT_DATA


def normalize_payload(payload):
    if isinstance(payload, dict):
        if "records" in payload:
            payload = payload["records"]
        elif "data" in payload:
            payload = payload["data"]
        else:
            payload = [payload]

    if not isinstance(payload, list):
        raise ValueError("Le JSON doit contenir une liste de réponses ou une clé data/records.")

    rows = []
    for item in payload:
        if isinstance(item, dict):
            rows.append([
                float(item.get("sms", item.get("SMS", 0))),
                float(item.get("voix", item.get("Voix", 0))),
                float(item.get("internet", item.get("Internet", 0))),
                float(item.get("credit", item.get("Credit", 0))),
                int(item.get("satisfait", item.get("Satisfait", 0))),
            ])
        elif isinstance(item, (list, tuple)) and len(item) >= 5:
            rows.append([
                float(item[0]),
                float(item[1]),
                float(item[2]),
                float(item[3]),
                int(item[4]),
            ])

    if not rows:
        raise ValueError("Aucune donnée de visiteur n'a été fournie. Les réponses doivent venir de la plateforme ou d'un fichier CSV/JSON.")

    return rows


def prepare_dataset(rows):
    X = np.array([
        [float(r[0]), float(r[1]), float(r[2]), float(r[3])]
        for r in rows
    ], dtype=float)

    y = np.array([
        [0.0, 1.0] if int(r[4]) == 1 else [1.0, 0.0]
        for r in rows
    ], dtype=float)

    return X, y


def softmax(z):
    exp_z = np.exp(z - np.max(z, axis=1, keepdims=True))
    return exp_z / np.sum(exp_z, axis=1, keepdims=True)


def compute_binary_decision(probabilities):
    count_0 = 0
    count_1 = 0
    for prob in probabilities:
        pred_idx = int(np.argmax(prob))
        if pred_idx == 0:
            count_0 += 1
        else:
            count_1 += 1
    return count_0, count_1


def run_tensorflow(X, y):
    import tensorflow as tf
    from tensorflow.keras.layers import Dense
    from tensorflow.keras.models import Sequential
    from sklearn.metrics import classification_report, confusion_matrix

    model = Sequential([
        Dense(2, activation="softmax", input_shape=(4,))
    ])

    model.compile(
        optimizer=tf.keras.optimizers.Adam(learning_rate=0.05),
        loss="categorical_crossentropy",
        metrics=["accuracy"],
    )

    history = model.fit(X, y, epochs=1200, verbose=0)
    probs = model.predict(X, verbose=0)

    y_true_idx = np.argmax(y, axis=1)
    y_pred_idx = np.argmax(probs, axis=1)

    print("\n=== PROBABILITES SOFTMAX (2 classes) ===")
    for i, prob in enumerate(probs):
        print(
            f"Visiteur {i + 1} : "
            f"Non satisfait(0)={prob[0]:.3f} | Satisfait(1)={prob[1]:.3f} | "
            f"Total = {np.sum(prob):.1f}"
        )

    print("\n=== DECISION FINALE ===")
    for i, pred_idx in enumerate(y_pred_idx):
        print(
            f"Visiteur {i + 1} : Prédit = {CLASS_NAMES[pred_idx]} | "
            f"Réel = {CLASS_NAMES[y_true_idx[i]]}"
        )

    print("\n=== MATRICE DE CONFUSION ===")
    print(confusion_matrix(y_true_idx, y_pred_idx))

    print("\n=== RAPPORT DE CLASSIFICATION ===")
    print(classification_report(y_true_idx, y_pred_idx, target_names=CLASS_NAMES))

    print("\n=== HISTORIQUE D'APPRENTISSAGE ===")
    print(f"loss={history.history['loss'][-1]:.6f} | accuracy={history.history['accuracy'][-1]:.4f}")

    return probs, history


def run_numpy_fallback(X, y):
    rng = np.random.default_rng(42)
    n_features = X.shape[1]
    n_classes = y.shape[1]

    W = rng.normal(0.0, 0.1, size=(n_features, n_classes))
    b = np.zeros(n_classes, dtype=float)
    learning_rate = 0.05

    for _ in range(1500):
        logits = X @ W + b
        probs = softmax(logits)
        grad_logits = (probs - y) / len(X)
        grad_W = X.T @ grad_logits
        grad_b = grad_logits.sum(axis=0)
        W -= learning_rate * grad_W
        b -= learning_rate * grad_b

    logits = X @ W + b
    probs = softmax(logits)

    print("\n=== PROBABILITES SOFTMAX (2 classes) ===")
    for i, prob in enumerate(probs):
        print(
            f"Visiteur {i + 1} : "
            f"Non satisfait(0)={prob[0]:.3f} | Satisfait(1)={prob[1]:.3f} | "
            f"Total = {np.sum(prob):.1f}"
        )

    y_true_idx = np.argmax(y, axis=1)
    y_pred_idx = np.argmax(probs, axis=1)

    print("\n=== DECISION FINALE ===")
    for i, pred_idx in enumerate(y_pred_idx):
        print(
            f"Visiteur {i + 1} : Prédit = {CLASS_NAMES[pred_idx]} | "
            f"Réel = {CLASS_NAMES[y_true_idx[i]]}"
        )

    return probs


def main():
    parser = argparse.ArgumentParser(description="Modèle IA pour le sondage ARTEC : SMS, Voix, Internet, Crédit -> 0/1")
    parser.add_argument("--file", type=str, help="Chemin vers un fichier CSV ou JSON avec les réponses")
    parser.add_argument("--json", type=str, help="Réponses sous forme JSON brute")
    args = parser.parse_args()

    if not args.file and not args.json:
        raise SystemExit("Aucune donnée détectée. Fournis un fichier CSV/JSON ou un JSON via --json. Les réponses ne doivent pas être codées en dur dans le script.")

    rows = load_rows(args.file, args.json)
    X, y = prepare_dataset(rows)

    print("=== DONNEES D'ENTREE ===")
    print("Colonnes :", FEATURE_NAMES)
    for idx, row in enumerate(rows, start=1):
        print(f"Visiteur {idx}: {row}")

    try:
        probabilities, _ = run_tensorflow(X, y)
    except Exception:
        probabilities = run_numpy_fallback(X, y)

    count_0, count_1 = compute_binary_decision(probabilities)
    print("\n=== BILAN ARTEC ===")
    print(f"Non satisfait (0) = {count_0}")
    print(f"Satisfait (1) = {count_1}")

    if count_1 >= count_0:
        print("RESULTAT VERT : Le nombre de 1 est supérieur ou égal au nombre de 0 -> Conformité OK.")
    else:
        print("RESULTAT ROUGE : Le nombre de 0 dépasse celui de 1 -> Alerte ARTEC requise.")


if __name__ == "__main__":
    main()
