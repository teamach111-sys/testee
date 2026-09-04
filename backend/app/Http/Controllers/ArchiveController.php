<?php

namespace App\Http\Controllers;

use App\Models\Archive;
use App\Models\Offre;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ArchiveController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $archives = Archive::with(['offre.departement'])
            ->orderBy('date_archivage', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Archives.',
            'data'    => $archives,
            'errors'  => [],
        ]);
    }

    public function restaurer(int $id): JsonResponse
    {
        $archive = Archive::findOrFail($id);
        $offre   = $archive->offre;

        $archive->delete();
        $offre->update(['est_publiee' => true, 'date_modification' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Offre restaurée et publiée.',
            'data'    => null,
            'errors'  => [],
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $archive = Archive::findOrFail($id);
        $offre   = $archive->offre;

        // Suppression définitive de l'offre (cascade supprime l'archive)
        $offre->delete();

        return response()->json([
            'success' => true,
            'message' => 'Offre supprimée définitivement.',
            'data'    => null,
            'errors'  => [],
        ]);
    }
}
