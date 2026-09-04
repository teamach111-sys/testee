<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Departement;
use App\Models\Offre;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Compte administrateur (idempotent, dans AdminSeeder)
        $this->call(AdminSeeder::class);
        $admin = Admin::where('email', 'admin@greativaconsulting.com')->firstOrFail();

        // Créer quelques départements
        $deps = [
            ['nom_departement' => 'Ressources Humaines',    'description' => 'Gestion des talents et du capital humain'],
            ['nom_departement' => 'Informatique & Digital',  'description' => 'Développement, infrastructure et transformation digitale'],
            ['nom_departement' => 'Finance & Comptabilité', 'description' => 'Gestion financière et contrôle de gestion'],
            ['nom_departement' => 'Commercial & Marketing', 'description' => 'Ventes, marketing et développement commercial'],
            ['nom_departement' => 'Juridique',              'description' => 'Affaires juridiques et conformité'],
            ['nom_departement' => 'Opérations',             'description' => 'Logistique, supply chain et opérations'],
        ];

        $departements = [];
        foreach ($deps as $dep) {
            $departements[] = Departement::create(array_merge($dep, ['id_admin' => $admin->id_admin]));
        }

        // Créer des offres d'exemple
        $offresData = [
            [
                'titre_offre'          => 'Développeur Full-Stack Laravel/Vue.js',
                'description_offre'    => '<p>Nous recherchons un développeur Full-Stack passionné pour rejoindre notre équipe technique. Vous serez en charge du développement et de la maintenance de nos applications web.</p><ul><li>Développement de nouvelles fonctionnalités</li><li>Optimisation des performances</li><li>Code review et bonnes pratiques</li></ul>',
                'type_contrat'         => 'CDI',
                'date_publication'     => now()->toDateString(),
                'delai_candidature'    => now()->addDays(30)->toDateString(),
                'competences_requises' => 'Laravel 11, Vue 3, MySQL, Git, REST API',
                'avantages'            => 'Télétravail partiel, Mutuelle, Formation continue',
                'localisation'         => 'Marrakech',
                'est_publiee'          => true,
                'id_departement'       => $departements[1]->id_departement,
            ],
            [
                'titre_offre'          => 'Chargé(e) de Recrutement Senior',
                'description_offre'    => '<p>Dans le cadre du développement de notre activité, nous recrutons un(e) Chargé(e) de Recrutement Senior pour renforcer notre équipe RH.</p>',
                'type_contrat'         => 'CDI',
                'date_publication'     => now()->toDateString(),
                'delai_candidature'    => now()->addDays(21)->toDateString(),
                'competences_requises' => 'Sourcing LinkedIn, ATS, Entretiens, GPEC',
                'avantages'            => 'Prime performance, Voiture de fonction, Tickets restaurant',
                'localisation'         => 'Casablanca',
                'est_publiee'          => true,
                'id_departement'       => $departements[0]->id_departement,
            ],
            [
                'titre_offre'          => 'Consultant Finance & Contrôle de Gestion',
                'description_offre'    => '<p>Nous recherchons un Consultant Finance pour accompagner nos clients dans leur transformation financière.</p>',
                'type_contrat'         => 'CDD',
                'date_publication'     => now()->toDateString(),
                'delai_candidature'    => now()->addDays(15)->toDateString(),
                'competences_requises' => 'Excel avancé, SAP, Reporting, Budget',
                'avantages'            => 'Mission client diversifiées, Formation certifiante',
                'localisation'         => 'Rabat',
                'est_publiee'          => true,
                'id_departement'       => $departements[2]->id_departement,
            ],
            [
                'titre_offre'          => 'Stage — Développeur Mobile React Native',
                'description_offre'    => '<p>Stage de 6 mois au sein de notre équipe digitale. Vous participerez au développement de notre application mobile.</p>',
                'type_contrat'         => 'Stage',
                'date_publication'     => now()->toDateString(),
                'delai_candidature'    => now()->addDays(10)->toDateString(),
                'competences_requises' => 'React Native, JavaScript, TypeScript',
                'avantages'            => 'Indemnité de stage, Encadrement personnalisé',
                'localisation'         => 'Marrakech',
                'est_publiee'          => true,
                'id_departement'       => $departements[1]->id_departement,
            ],
        ];

        foreach ($offresData as $offreData) {
            Offre::create(array_merge($offreData, ['id_admin' => $admin->id_admin]));
        }

        $this->command->info('✅ Seeder exécuté avec succès !');
        $this->command->info('📧 Email admin : admin@greativaconsulting.com');
        $this->command->info('🔑 Mot de passe : Admin@2024!');
    }
}
