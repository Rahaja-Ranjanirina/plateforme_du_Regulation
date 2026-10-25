<?php

namespace Database\Seeders;

use App\Models\Actualite;
use App\Models\Message;
use App\Models\Offre;
use App\Models\Operateur;
use App\Models\Reponse;
use App\Models\Sondage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Administrateur',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // 2. ARTEC
        $artec = User::updateOrCreate(
            ['email' => 'artec@gmail.com'],
            [
                'name' => 'ARTEC Régulation',
                'password' => Hash::make('password'),
                'role' => 'artec',
            ]
        );

        // 3. Opérateurs (Yas, Airtel, Orange)
        $operateurs = [];
        foreach (['Yas', 'Airtel', 'Orange'] as $nom) {
            $email = strtolower($nom) . '@gmail.com';
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $nom,
                    'password' => Hash::make('password'),
                    'role' => 'operateur',
                ]
            );

            $op = Operateur::updateOrCreate(
                ['nom' => $nom],
                ['user_id' => $user->id]
            );
            $operateurs[$nom] = $op;
        }

        // 4. Visiteurs
        $visiteursData = [
            'rahaja.ranjanirina@gmail.com',
            'jean.luc@gmail.com',
            'soafara.andry@gmail.com',
            'toky.rakoto@gmail.com',
            'faly.rasoanaivo@gmail.com',
            'mialy.randria@gmail.com',
        ];

        $visiteurs = [];
        foreach ($visiteursData as $email) {
            $name = str_replace('@gmail.com', '', $email);
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => null,
                    'role' => 'visiteur',
                ]
            );
            $visiteurs[] = $user;
        }

        // 5. Sondages ARTEC & Opérateurs
        $sondageYas = Sondage::updateOrCreate(
            ['titre' => 'Évaluation de la qualité de service — Yas Madagascar'],
            [
                'operateur_id' => $operateurs['Yas']->id,
                'user_id' => $artec->id,
                'actif' => true,
            ]
        );

        $sondageAirtel = Sondage::updateOrCreate(
            ['titre' => 'Évaluation de la qualité de service — Airtel Madagascar'],
            [
                'operateur_id' => $operateurs['Airtel']->id,
                'user_id' => $artec->id,
                'actif' => true,
            ]
        );

        $sondageOrange = Sondage::updateOrCreate(
            ['titre' => 'Évaluation de la qualité de service — Orange Madagascar'],
            [
                'operateur_id' => $operateurs['Orange']->id,
                'user_id' => $artec->id,
                'actif' => true,
            ]
        );

        // 6. Réponses des visiteurs :
        // Pour Yas : Majorité satisfaite (1 > 0 => VERT)
        $yasResponses = [
            ['user_idx' => 0, 'sms' => 8, 'voix' => 9, 'internet' => 8, 'credit' => 7, 'satisfait' => 1],
            ['user_idx' => 1, 'sms' => 7, 'voix' => 8, 'internet' => 9, 'credit' => 8, 'satisfait' => 1],
            ['user_idx' => 2, 'sms' => 9, 'voix' => 8, 'internet' => 8, 'credit' => 9, 'satisfait' => 1],
            ['user_idx' => 3, 'sms' => 4, 'voix' => 5, 'internet' => 4, 'credit' => 5, 'satisfait' => 0],
            ['user_idx' => 4, 'sms' => 8, 'voix' => 7, 'internet' => 8, 'credit' => 7, 'satisfait' => 1],
        ];

        foreach ($yasResponses as $r) {
            Reponse::updateOrCreate(
                ['sondage_id' => $sondageYas->id, 'user_id' => $visiteurs[$r['user_idx']]->id],
                [
                    'sms' => $r['sms'],
                    'voix' => $r['voix'],
                    'internet' => $r['internet'],
                    'credit' => $r['credit'],
                    'satisfait' => $r['satisfait'],
                ]
            );
        }

        // Pour Airtel : Majorité insatisfaite (0 > 1 => ROUGE, Alerte avertissement !)
        $airtelResponses = [
            ['user_idx' => 0, 'sms' => 3, 'voix' => 4, 'internet' => 2, 'credit' => 3, 'satisfait' => 0],
            ['user_idx' => 1, 'sms' => 4, 'voix' => 3, 'internet' => 3, 'credit' => 4, 'satisfait' => 0],
            ['user_idx' => 2, 'sms' => 7, 'voix' => 6, 'internet' => 7, 'credit' => 6, 'satisfait' => 1],
            ['user_idx' => 3, 'sms' => 2, 'voix' => 3, 'internet' => 2, 'credit' => 3, 'satisfait' => 0],
            ['user_idx' => 4, 'sms' => 3, 'voix' => 4, 'internet' => 3, 'credit' => 4, 'satisfait' => 0],
        ];

        foreach ($airtelResponses as $r) {
            Reponse::updateOrCreate(
                ['sondage_id' => $sondageAirtel->id, 'user_id' => $visiteurs[$r['user_idx']]->id],
                [
                    'sms' => $r['sms'],
                    'voix' => $r['voix'],
                    'internet' => $r['internet'],
                    'credit' => $r['credit'],
                    'satisfait' => $r['satisfait'],
                ]
            );
        }

        // Pour Orange : Majorité satisfaite (1 > 0 => VERT)
        $orangeResponses = [
            ['user_idx' => 0, 'sms' => 7, 'voix' => 8, 'internet' => 8, 'credit' => 6, 'satisfait' => 1],
            ['user_idx' => 1, 'sms' => 8, 'voix' => 7, 'internet' => 8, 'credit' => 7, 'satisfait' => 1],
            ['user_idx' => 2, 'sms' => 4, 'voix' => 5, 'internet' => 5, 'credit' => 4, 'satisfait' => 0],
            ['user_idx' => 3, 'sms' => 8, 'voix' => 8, 'internet' => 9, 'credit' => 7, 'satisfait' => 1],
        ];

        foreach ($orangeResponses as $r) {
            Reponse::updateOrCreate(
                ['sondage_id' => $sondageOrange->id, 'user_id' => $visiteurs[$r['user_idx']]->id],
                [
                    'sms' => $r['sms'],
                    'voix' => $r['voix'],
                    'internet' => $r['internet'],
                    'credit' => $r['credit'],
                    'satisfait' => $r['satisfait'],
                ]
            );
        }

        // 7. Offres publiées par les opérateurs
        Offre::updateOrCreate(
            ['titre' => 'Forfait Yas Be 25Go', 'operateur_id' => $operateurs['Yas']->id],
            ['description' => '25 Go d’Internet haut débit + 100 SMS offerts valables 30 jours partout à Madagascar.']
        );
        Offre::updateOrCreate(
            ['titre' => 'Pass Nuit Illimité Yas', 'operateur_id' => $operateurs['Yas']->id],
            ['description' => 'Connexion illimitée de minuit à 6h du matin pour seulement 2 000 Ar.']
        );

        Offre::updateOrCreate(
            ['titre' => 'Airtel MegaNet 40Go', 'operateur_id' => $operateurs['Airtel']->id],
            ['description' => 'Volume data généreux pour streaming et télétravail avec bonus Airtel Money inclus.']
        );
        Offre::updateOrCreate(
            ['titre' => 'Pack Appel & SMS Illimités Airtel', 'operateur_id' => $operateurs['Airtel']->id],
            ['description' => 'Restez connecté avec vos proches sans compter vers tous les numéros Airtel.']
        );

        Offre::updateOrCreate(
            ['titre' => 'Orange Fibre Optique Pro', 'operateur_id' => $operateurs['Orange']->id],
            ['description' => 'Vitesse jusqu’à 100 Mbps avec installation gratuite et routeur Wi-Fi 6 inclus.']
        );
        Offre::updateOrCreate(
            ['titre' => 'Pass Orange Konekta 15Go', 'operateur_id' => $operateurs['Orange']->id],
            ['description' => '15 Go valables 15 jours sur le réseau 4G+ numéro 1 à Madagascar.']
        );

        // 8. Actualités publiées par les opérateurs (avec lien web)
        Actualite::updateOrCreate(
            ['titre' => 'Yas Madagascar étend sa couverture 4G dans les régions côtières', 'operateur_id' => $operateurs['Yas']->id],
            ['lien' => 'https://www.yas.mg/actualites/extension-reseau-cotiere']
        );

        Actualite::updateOrCreate(
            ['titre' => 'Airtel lance son programme de modernisation d’antennes relais', 'operateur_id' => $operateurs['Airtel']->id],
            ['lien' => 'https://www.airtel.mg/actualites/modernisation-antennes']
        );

        Actualite::updateOrCreate(
            ['titre' => 'Orange Madagascar inaugure son nouveau datacenter certifié Tier III', 'operateur_id' => $operateurs['Orange']->id],
            ['lien' => 'https://www.orange.mg/presse/inauguration-datacenter']
        );

        // 9. Messages de l'ARTEC vers les opérateurs (notamment avertissement Airtel)
        Message::updateOrCreate(
            ['sujet' => 'Avertissement officiel : Qualité des services dégradée (SMS & Voix)', 'operateur_id' => $operateurs['Airtel']->id],
            [
                'expediteur_id' => $artec->id,
                'contenu' => "Madame, Monsieur,\n\nSuite aux récents sondages conduits auprès des usagers, l'ARTEC constate que plus de 80% des avis exprimés sont négatifs (note d'insatisfaction dominante). Vous disposez d'un délai réglementaire de 15 jours pour transmettre un plan d'actions correctives.\n\nLe Directeur Général de l'ARTEC.",
                'lu' => false,
            ]
        );

        Message::updateOrCreate(
            ['sujet' => 'Félicitations pour le respect des seuils de qualité de service', 'operateur_id' => $operateurs['Yas']->id],
            [
                'expediteur_id' => $artec->id,
                'contenu' => "Madame, Monsieur,\n\nL'ARTEC note avec satisfaction les retours positifs des abonnés sur vos services SMS et Internet. Merci de poursuivre ces efforts de maintien de la qualité.",
                'lu' => true,
            ]
        );
    }
}