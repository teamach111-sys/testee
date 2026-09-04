<?php

namespace App\Http\Controllers;

use App\Models\Offre;
use App\Models\Archive;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OffreController extends Controller
{
    /**
     * GET /api/offres — Liste publique des offres actives
     */
    public function index(Request $request): JsonResponse
    {
        $query = Offre::with('departement')
            ->whereDoesntHave('archive');

        // Si pas authentifié, on ne montre que les offres publiées
        if (! Auth::guard('sanctum')->check()) {
            $query->where('est_publiee', true);
        }

        // Filtres
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('titre_offre', 'like', "%{$request->search}%")
                  ->orWhere('description_offre', 'like', "%{$request->search}%")
                  ->orWhere('localisation', 'like', "%{$request->search}%");
            });
        }
        if ($request->filled('departement')) {
            $query->where('id_departement', $request->departement);
        }
        if ($request->filled('type_contrat')) {
            $query->where('type_contrat', $request->type_contrat);
        }
        if ($request->filled('statut') && Auth::guard('sanctum')->check()) {
            $query->where('est_publiee', $request->statut === 'publiee');
        }

        // Tri
        $sort = $request->get('sort', 'desc');
        $query->orderBy('date_publication', $sort === 'asc' ? 'asc' : 'desc');

        // Pagination
        $perPage = $request->get('per_page', 10);
        $offres  = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Liste des offres.',
            'data'    => $offres,
            'errors'  => [],
        ]);
    }

    /**
     * GET /api/offres/:id — Détail offre
     */
    public function show(int $id): JsonResponse
    {
        $query = Offre::with(['departement', 'admin'])->whereDoesntHave('archive');

        // Visible publiquement uniquement si publiée ; les admins peuvent tout voir
        if (! Auth::guard('sanctum')->check()) {
            $query->where('est_publiee', true);
        }

        $offre = $query->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Détail de l\'offre.',
            'data'    => $offre,
            'errors'  => [],
        ]);
    }

    /**
     * POST /api/offres — Créer une offre (admin)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'titre_offre'          => 'required|string|max:100',
            'description_offre'    => 'required|string',
            'type_contrat'         => 'required|in:CDI,CDD,Stage,Freelance,Alternance',
            'date_publication'     => 'required|date',
            'delai_candidature'    => 'nullable|date|after:date_publication',
            'competences_requises' => 'nullable|string',
            'avantages'            => 'nullable|string',
            'localisation'         => 'nullable|string|max:100',
            'est_publiee'          => 'boolean',
            'id_departement'       => 'required|exists:departements,id_departement',
        ]);

        $validated['id_admin'] = $request->user()->id_admin;

        $offre = Offre::create($validated);
        $offre->load('departement');

        return response()->json([
            'success' => true,
            'message' => 'Offre créée avec succès.',
            'data'    => $offre,
            'errors'  => [],
        ], 201);
    }

    /**
     * PUT /api/offres/:id — Modifier une offre (admin)
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $offre = Offre::findOrFail($id);

        $validated = $request->validate([
            'titre_offre'          => 'sometimes|string|max:100',
            'description_offre'    => 'sometimes|string',
            'type_contrat'         => 'sometimes|in:CDI,CDD,Stage,Freelance,Alternance',
            'date_publication'     => 'sometimes|date',
            'delai_candidature'    => 'nullable|date',
            'competences_requises' => 'nullable|string',
            'avantages'            => 'nullable|string',
            'localisation'         => 'nullable|string|max:100',
            'est_publiee'          => 'boolean',
            'id_departement'       => 'sometimes|exists:departements,id_departement',
        ]);

        $validated['date_modification'] = now();
        $offre->update($validated);
        $offre->load('departement');

        return response()->json([
            'success' => true,
            'message' => 'Offre mise à jour.',
            'data'    => $offre,
            'errors'  => [],
        ]);
    }

    /**
     * PATCH /api/offres/:id/toggle-publish — Publier/Dépublier
     */
    public function togglePublish(int $id): JsonResponse
    {
        $offre = Offre::findOrFail($id);
        $offre->update([
            'est_publiee'       => ! $offre->est_publiee,
            'date_modification' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => $offre->est_publiee ? 'Offre publiée.' : 'Offre dépubliée.',
            'data'    => ['est_publiee' => $offre->est_publiee],
            'errors'  => [],
        ]);
    }

    /**
     * PATCH /api/offres/:id/archive — Archiver une offre
     */
    public function archive(Request $request, int $id): JsonResponse
    {
        $offre = Offre::findOrFail($id);

        if ($offre->archive) {
            return response()->json([
                'success' => false,
                'message' => 'Cette offre est déjà archivée.',
                'data'    => null,
                'errors'  => [],
            ], 409);
        }

        Archive::create([
            'date_archivage'  => now()->toDateString(),
            'motif_archivage' => $request->get('motif', null),
            'id_offre'        => $offre->id_offre,
        ]);

        $offre->update(['est_publiee' => false, 'date_modification' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Offre archivée avec succès.',
            'data'    => null,
            'errors'  => [],
        ]);
    }

    /**
     * DELETE /api/offres/:id — Supprimer définitivement
     */
    public function destroy(int $id): JsonResponse
    {
        $offre = Offre::findOrFail($id);
        $offre->delete();

        return response()->json([
            'success' => true,
            'message' => 'Offre supprimée définitivement.',
            'data'    => null,
            'errors'  => [],
        ]);
    }
}
