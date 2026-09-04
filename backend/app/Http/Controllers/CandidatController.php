<?php

namespace App\Http\Controllers;

use App\Models\Candidat;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class CandidatController extends Controller
{
    /**
     * GET /api/candidats
     */
    public function index(Request $request): JsonResponse
    {
        $query = Candidat::with('candidatures.offre')
            ->orderBy('date_inscription', 'desc');

        if ($request->filled('search')) {
            $query->where('nom_complet', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
        }

        $candidats = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Liste des candidats.',
            'data'    => $candidats,
            'errors'  => [],
        ]);
    }

    /**
     * GET /api/candidats/:id
     */
    public function show(int $id): JsonResponse
    {
        $candidat = Candidat::with(['candidatures.offre.departement', 'candidatures.entretien'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Détail candidat.',
            'data'    => $candidat,
            'errors'  => [],
        ]);
    }

    /**
     * DELETE /api/candidats/:id
     */
    public function destroy(int $id): JsonResponse
    {
        $candidat = Candidat::findOrFail($id);

        // Supprimer le CV du disque
        if ($candidat->cv && Storage::disk('local')->exists($candidat->cv)) {
            Storage::disk('local')->delete($candidat->cv);
        }

        $candidat->delete();

        return response()->json([
            'success' => true,
            'message' => 'Candidat supprimé.',
            'data'    => null,
            'errors'  => [],
        ]);
    }

    /**
     * PATCH /api/candidats/:id/note — Note interne sur le profil candidat
     */
    public function updateNote(Request $request, int $id): JsonResponse
    {
        $candidat = Candidat::findOrFail($id);
        $request->validate(['note' => 'nullable|string|max:2000']);

        $candidat->update(['note' => $request->note]);

        return response()->json([
            'success' => true,
            'message' => 'Note mise à jour.',
            'data'    => ['note' => $candidat->note],
            'errors'  => [],
        ]);
    }

    /**
     * GET /api/candidats/:id/cv — Télécharger le CV
     */
    public function downloadCv(int $id)
    {
        $candidat = Candidat::findOrFail($id);

        if (! $candidat->cv || ! Storage::disk('local')->exists($candidat->cv)) {
            return response()->json([
                'success' => false,
                'message' => 'CV introuvable.',
                'data'    => null,
                'errors'  => [],
            ], 404);
        }

        $filename = 'CV_' . str_replace(' ', '_', $candidat->nom_complet) . '.pdf';

        return Storage::disk('local')->download($candidat->cv, $filename);
    }
}
