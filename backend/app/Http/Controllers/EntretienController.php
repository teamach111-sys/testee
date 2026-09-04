<?php

namespace App\Http\Controllers;

use App\Models\Entretien;
use App\Models\Candidature;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EntretienController extends Controller
{
    /**
     * GET /api/entretiens
     */
    public function index(Request $request): JsonResponse
    {
        $query = Entretien::with(['candidature.candidat', 'candidature.offre', 'admin'])
            ->orderBy('date_entretien', 'desc');

        if ($request->filled('type'))      { $query->where('type_entretien', $request->type); }
        if ($request->filled('resultat'))  { $query->where('resultat_entretien', $request->resultat); }
        if ($request->filled('search')) {
            $query->whereHas('candidature.candidat', fn ($q) =>
                $q->where('nom_complet', 'like', "%{$request->search}%")
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Liste des entretiens.',
            'data'    => $query->paginate($request->get('per_page', 15)),
            'errors'  => [],
        ]);
    }

    /**
     * POST /api/entretiens
     * Règle : uniquement si statut_candidature = 'Présélectionné'
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date_entretien'  => 'required|date|after:now',
            'type_entretien'  => 'required|in:Téléphonique,Visio,Présentiel',
            'lien_visio'      => 'nullable|url|max:255',
            'remarque'        => 'nullable|string|max:1000',
            'id_candidature'  => 'required|exists:candidatures,id_candidature',
        ]);

        $candidature = Candidature::findOrFail($validated['id_candidature']);

        // Vérification métier
        if ($candidature->statut_candidature !== Candidature::STATUT_SHORTLISTED) {
            return response()->json([
                'success' => false,
                'message' => 'Un entretien ne peut être créé que pour une candidature présélectionnée.',
                'data'    => null,
                'errors'  => ['statut' => 'Statut invalide pour créer un entretien.'],
            ], 422);
        }

        // Vérifier qu'il n'y a pas déjà un entretien
        if ($candidature->entretien) {
            return response()->json([
                'success' => false,
                'message' => 'Cette candidature a déjà un entretien planifié.',
                'data'    => null,
                'errors'  => [],
            ], 409);
        }

        $validated['id_admin']          = $request->user()->id_admin;
        $validated['resultat_entretien'] = 'En attente';

        $entretien = Entretien::create($validated);
        $entretien->load(['candidature.candidat', 'candidature.offre']);

        return response()->json([
            'success' => true,
            'message' => 'Entretien planifié.',
            'data'    => $entretien,
            'errors'  => [],
        ], 201);
    }

    /**
     * GET /api/entretiens/:id
     */
    public function show(int $id): JsonResponse
    {
        $entretien = Entretien::with(['candidature.candidat', 'candidature.offre', 'admin'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Détail entretien.',
            'data'    => $entretien,
            'errors'  => [],
        ]);
    }

    /**
     * PUT /api/entretiens/:id
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $entretien = Entretien::findOrFail($id);

        $validated = $request->validate([
            'date_entretien' => 'sometimes|date',
            'type_entretien' => 'sometimes|in:Téléphonique,Visio,Présentiel',
            'lien_visio'     => 'nullable|url|max:255',
            'remarque'       => 'nullable|string|max:1000',
        ]);

        $entretien->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Entretien mis à jour.',
            'data'    => $entretien,
            'errors'  => [],
        ]);
    }

    /**
     * PATCH /api/entretiens/:id/resultat — Clôturer l'entretien
     */
    public function updateResultat(Request $request, int $id): JsonResponse
    {
        $entretien = Entretien::findOrFail($id);

        $request->validate([
            'resultat_entretien' => 'required|in:En attente,Réussi,Échoué',
        ]);

        $entretien->update(['resultat_entretien' => $request->resultat_entretien]);

        return response()->json([
            'success' => true,
            'message' => 'Résultat enregistré.',
            'data'    => ['resultat_entretien' => $entretien->resultat_entretien],
            'errors'  => [],
        ]);
    }

    /**
     * DELETE /api/entretiens/:id
     */
    public function destroy(int $id): JsonResponse
    {
        Entretien::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Entretien supprimé.',
            'data'    => null,
            'errors'  => [],
        ]);
    }
}
