<?php

namespace App\Http\Controllers;

use App\Models\Departement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DepartementController extends Controller
{
    public function index(): JsonResponse
    {
        $departements = Departement::with('admin')
            ->withCount('offres')
            ->orderBy('nom_departement')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Liste des départements.',
            'data'    => $departements,
            'errors'  => [],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nom_departement' => 'required|string|max:80|unique:departements',
            'description'     => 'nullable|string|max:500',
        ]);

        $validated['id_admin'] = $request->user()->id_admin;
        $departement = Departement::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Département créé.',
            'data'    => $departement,
            'errors'  => [],
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $departement = Departement::findOrFail($id);

        $validated = $request->validate([
            'nom_departement' => "sometimes|string|max:80|unique:departements,nom_departement,{$id},id_departement",
            'description'     => 'nullable|string|max:500',
        ]);

        $departement->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Département mis à jour.',
            'data'    => $departement,
            'errors'  => [],
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $departement = Departement::findOrFail($id);

        if ($departement->offres()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer un département contenant des offres.',
                'data'    => null,
                'errors'  => ['offres' => 'Ce département contient des offres actives.'],
            ], 409);
        }

        $departement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Département supprimé.',
            'data'    => null,
            'errors'  => [],
        ]);
    }
}
