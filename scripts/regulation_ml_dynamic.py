#!/usr/bin/env python3
"""
Modèle TensorFlow / Keras Dynamique adapté aux données des visiteurs :
- 4 entrées X : SMS, Voix, Crédit, Internet (notes sur 10)
- 1 colonne cible y : Binaire (0 = Non satisfait, 1 = Satisfait)
"""

import numpy as np

# Données dynamiques des visiteurs (4 critères : SMS, Voix, Crédit, Internet)
# Exemple pour Yas Madagascar :
X = np.array([
    [8, 9, 7, 8],  # Visiteur 1 (rahaja.ranjanirina)
    [7, 8, 8, 9],  # Visiteur 2 (jean.luc)
    [9, 8, 9, 8],  # Visiteur 3 (soafara.andry)
    [4, 5, 5, 4],  # Visiteur 4 (toky.rakoto)
    [8, 7, 7, 8],  # Visiteur 5 (faly.rasoanaivo)
], dtype=float)

# Cibles one-hot pour Softmax (0 = Non satisfait, 1 = Satisfait)
# Visiteur 4 a choisi 0, les autres ont choisi 1
y = np.array([
    [0, 1],  # Satisfait (1)
    [0, 1],  # Satisfait (1)
    [0, 1],  # Satisfait (1)
    [1, 0],  # Non satisfait (0)
    [0, 1],  # Satisfait (1)
], dtype=float)

class_names = ["Non satisfait (0)", "Satisfait (1)"]
feature_names = ["SMS", "Voix", "Crédit", "Internet"]

print("=== ENTRÉES X (4 COLONNES SAISIES PAR LES VISITEURS) ===")
print("Colonnes :", feature_names)
print(X)

print("\n=== SORTIE y (CHOIX BINAIRE DU VISITEUR : 0 ou 1) ===")
print("One-hot [Non satisfait (0), Satisfait (1)] :")
print(y)

try:
    import tensorflow as tf
    from tensorflow.keras.models import Sequential
    from tensorflow.keras.layers import Dense
    from sklearn.metrics import confusion_matrix, classification_report

    model = Sequential([
        Dense(2, activation='softmax', input_shape=(4,))
    ])

    model.compile(
        optimizer=tf.keras.optimizers.Adam(learning_rate=0.05),
        loss='categorical_crossentropy',
        metrics=['accuracy']
    )

    history = model.fit(X, y, epochs=1500, verbose=0)
    probabilities = model.predict(X)

    print("\n=== PROBABILITES SORTIES PAR SOFTMAX (Somme = 1) ===")
    for i, prob in enumerate(probabilities):
        print(f"Visiteur {i+1} : Non satisfait(0)={prob[0]:.3f} | Satisfait(1)={prob[1]:.3f} (Total = {np.sum(prob):.1f})")

    y_true_indices = np.argmax(y, axis=1)
    y_pred_indices = np.argmax(probabilities, axis=1)

    print("\n=== DECISION FINALE (Classe retenue) ===")
    for i, pred_idx in enumerate(y_pred_indices):
        print(f"Visiteur {i+1} : Prédit = {class_names[pred_idx]} | Réel = {class_names[y_true_indices[i]]}")

    poids, biais = model.layers[0].get_weights()
    print("\n=== MATRICE DES POIDS (4 entrées x 2 sorties) ===")
    print(poids)
    print("\n=== BIAIS (2 neurones) ===")
    print(biais)

    print("\n=== MATRICE DE CONFUSION ===")
    print(confusion_matrix(y_true_indices, y_pred_indices))

    print("\n=== RAPPORT DE CLASSIFICATION ===")
    print(classification_report(y_true_indices, y_pred_indices, target_names=class_names))

except ImportError:
    # Mode standalone pur NumPy
    print("\n(Mode calcul matriciel direct NumPy)")
    mean = np.mean(X, axis=0)
    std = np.std(X, axis=0)
    std[std == 0] = 1.0
    X_scaled = (X - mean) / std

    W = np.array([
        [-0.72,  0.70],
        [-0.75,  0.88],
        [-0.47,  0.50],
        [-0.80,  0.91]
    ])
    b = np.array([-0.3, 0.3])

    logits = np.dot(X_scaled, W) + b
    exp_logits = np.exp(logits - np.max(logits, axis=1, keepdims=True))
    probs = exp_logits / np.sum(exp_logits, axis=1, keepdims=True)

    print("\n=== PROBABILITES SORTIES PAR SOFTMAX (Somme = 1) ===")
    for i, prob in enumerate(probs):
        print(f"Visiteur {i+1} : Non satisfait(0)={prob[0]:.3f} | Satisfait(1)={prob[1]:.3f} (Total = {np.sum(prob):.1f})")

    y_true_indices = np.argmax(y, axis=1)
    y_pred_indices = np.argmax(probs, axis=1)

    print("\n=== DECISION FINALE (Classe retenue) ===")
    for i, pred_idx in enumerate(y_pred_indices):
        print(f"Visiteur {i+1} : Prédit = {class_names[pred_idx]} | Réel = {class_names[y_true_indices[i]]}")

    # Décision ARTEC : 0 vs 1
    count_0 = np.sum(y_true_indices == 0)
    count_1 = np.sum(y_true_indices == 1)
    print(f"\n=== BILAN ARTEC : {count_1} Satisfaits (1) vs {count_0} Non satisfaits (0) ===")
    if count_0 > count_1:
        print("RESULTAT ROUGE : Nombre de 0 supérieur au nombre de 1 -> Avertissement requis !")
    else:
        print("RESULTAT VERT : Nombre de 1 supérieur ou égal au nombre de 0 -> Qualité conforme.")
