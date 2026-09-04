<?php

namespace App\Http\Controllers;

use App\Models\Candidature;
use App\Models\Offre;
use App\Models\Candidat;
use App\Models\Entretien;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    public function overview(): JsonResponse
    {
        $stats = [
            'candidatures' => [
                'total'          => Candidature::count(),
                'nouveau'        => Candidature::where('statut_candidature', 'Nouveau')->count(),
                'en_revision'    => Candidature::where('statut_candidature', 'En révision')->count(),
                'preselectionne' => Candidature::where('statut_candidature', 'Présélectionné')->count(),
                'embauche'       => Candidature::where('statut_candidature', 'Embauché')->count(),
                'rejete'         => Candidature::where('statut_candidature', 'Rejeté')->count(),
                'ce_mois'        => Candidature::whereMonth('date_candidature', now()->month)
                    ->whereYear('date_candidature', now()->year)->count(),
            ],
            'offres' => [
                'total'    => Offre::count(),
                'publiees' => Offre::where('est_publiee', true)->whereDoesntHave('archive')->count(),
                'archivees'=> Offre::whereHas('archive')->count(),
                'inactives'=> Offre::where('est_publiee', false)->whereDoesntHave('archive')->count(),
            ],
            'candidats' => [
                'total'    => Candidat::count(),
                'ce_mois'  => Candidat::whereMonth('date_inscription', now()->month)
                ->whereYear('date_inscription', now()->year)->count(),
            ],
            'entretiens' => [
                'total'      => Entretien::count(),
                'planifies'  => Entretien::where('resultat_entretien', 'En attente')->count(),
                'reussis'    => Entretien::where('resultat_entretien', 'Réussi')->count(),
                'echoues'    => Entretien::where('resultat_entretien', 'Échoué')->count(),
                'cette_semaine' => Entretien::whereBetween('date_entretien', [
                    now()->startOfWeek(), now()->endOfWeek()
                ])->count(),
            ],
        ];

        // Dernières candidatures
        $dernieresCandidatures = Candidature::with(['candidat', 'offre'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Prochains entretiens
        $prochainsEntretiens = Entretien::with(['candidature.candidat', 'candidature.offre'])
            ->where('date_entretien', '>=', now())
            ->where('resultat_entretien', 'En attente')
            ->orderBy('date_entretien')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Statistiques du tableau de bord.',
            'data'    => [
                'stats'                  => $stats,
                'dernieres_candidatures' => $dernieresCandidatures,
                'prochains_entretiens'   => $prochainsEntretiens,
            ],
            'errors' => [],
        ]);
    }
}
