<?php

namespace App\Http\Controllers;

use App\Models\Candidat;
use App\Models\Candidature;
use App\Models\Offre;
use App\Mail\CandidatureConfirmation;
use App\Mail\CandidatureAcceptee;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CandidaturesExport;
use App\Services\RecaptchaService;

class CandidatureController extends Controller
{
    /**
     * POST /api/candidatures — Soumission publique (sans compte)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nom_complet'       => 'required|string|min:3|max:100',
            'email'             => 'required|email|max:100',
            'telephone'         => 'required|string|max:20',
            'cv'                => 'required|file|mimes:pdf|max:5120',
            'linkedin_url'      => 'nullable|url|max:255',
            'portfolio_url'     => 'nullable|url|max:255',
            'lettre_motivation' => 'nullable|string|max:1000',
            'id_offre'          => 'required|exists:offres,id_offre',
            'g-recaptcha-response' => 'nullable|string',
        ]);

        // Google reCAPTCHA v3 — anti-spam
        $recaptcha = app(RecaptchaService::class);
        if (! $recaptcha->verify($validated['g-recaptcha-response'] ?? null, $request->ip())) {
            return response()->json([
                'success' => false,
                'message' => 'La vérification anti-spam a échoué. Veuillez réessayer.',
                'data'    => null,
                'errors'  => ['recaptcha' => 'Vérification anti-spam invalide.'],
            ], 422);
        }

        // Vérifier que l'offre est active
        $offre = Offre::where('id_offre', $validated['id_offre'])
            ->where('est_publiee', true)
            ->whereDoesntHave('archive')
            ->firstOrFail();

        // Vérifier si candidature déjà soumise pour cette offre
        $existingCandidat = Candidat::where('email', $validated['email'])->first();
        if ($existingCandidat) {
            $alreadyApplied = Candidature::where('id_candidat', $existingCandidat->id_candidat)
                ->where('id_offre', $validated['id_offre'])
                ->exists();
            if ($alreadyApplied) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous avez déjà postulé pour cette offre.',
                    'data'    => null,
                    'errors'  => ['email' => 'Candidature déjà soumise pour cette offre.'],
                ], 409);
            }
        }

        // Upload CV
        $cvPath = $request->file('cv')->store('cvs', 'local');

        // Trouver ou créer le candidat.
        // IMPORTANT : si le même email postule à une AUTRE offre, on ne réécrit
        // PAS son profil (nom, téléphone, CV…) : chaque candidature conserve la
        // référence via les lignes de candidature, et on ne remplace le CV du
        // candidat que lors de sa toute première candidature.
        $existingCandidat = Candidat::where('email', $validated['email'])->first();

        if ($existingCandidat) {
            $candidat = $existingCandidat;
        } else {
            $candidat = Candidat::create([
                'email'             => $validated['email'],
                'nom_complet'       => $validated['nom_complet'],
                'telephone'         => $validated['telephone'],
                'cv'                => $cvPath,
                'lettre_motivation' => $validated['lettre_motivation'] ?? null,
                'linkedin_url'      => $validated['linkedin_url'] ?? null,
                'portfolio_url'     => $validated['portfolio_url'] ?? null,
            ]);
        }

        // Créer la candidature
        $candidature = Candidature::create([
            'date_candidature'   => now()->toDateString(),
            'statut_candidature' => Candidature::STATUT_NEW,
            'id_candidat'        => $candidat->id_candidat,
            'id_offre'           => $offre->id_offre,
        ]);

        // Email de confirmation
        try {
            Mail::to($candidat->email)->send(new CandidatureConfirmation($candidat, $offre));
        } catch (\Exception $e) {
            \Log::warning('Email confirmation failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Candidature soumise avec succès. Un email de confirmation vous a été envoyé.',
            'data'    => ['id_candidature' => $candidature->id_candidature],
            'errors'  => [],
        ], 201);
    }

    /**
     * GET /api/candidatures — Liste admin avec filtres
     */
    public function index(Request $request): JsonResponse
    {
        $query = Candidature::with(['candidat', 'offre.departement', 'entretien'])
            ->orderBy('date_candidature', 'desc');

        if ($request->filled('offre'))     { $query->where('id_offre', $request->offre); }
        if ($request->filled('statut'))    { $query->where('statut_candidature', $request->statut); }
        if ($request->filled('date_from')) { $query->where('date_candidature', '>=', $request->date_from); }
        if ($request->filled('date_to'))   { $query->where('date_candidature', '<=', $request->date_to); }
        if ($request->filled('search')) {
            $query->whereHas('candidat', fn ($q) =>
                $q->where('nom_complet', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
            );
        }

        $candidatures = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Liste des candidatures.',
            'data'    => $candidatures,
            'errors'  => [],
        ]);
    }

    /**
     * GET /api/candidatures/:id — Détail
     */
    public function show(int $id): JsonResponse
    {
        $candidature = Candidature::with(['candidat', 'offre.departement', 'entretien', 'admin'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Détail candidature.',
            'data'    => $candidature,
            'errors'  => [],
        ]);
    }

    /**
     * PATCH /api/candidatures/:id/statut
     */
    public function updateStatut(Request $request, int $id): JsonResponse
    {
        $candidature = Candidature::with('candidat', 'offre')->findOrFail($id);

        $request->validate([
            'statut_candidature' => 'required|in:Nouveau,En révision,Présélectionné,Embauché,Rejeté',
        ]);

        $oldStatut = $candidature->statut_candidature;
        $candidature->update([
            'statut_candidature' => $request->statut_candidature,
            'id_admin'           => $request->user()->id_admin,
        ]);

        // Email d'acceptation si passage à Embauché
        if ($request->statut_candidature === Candidature::STATUT_HIRED && $oldStatut !== Candidature::STATUT_HIRED) {
            try {
                Mail::to($candidature->candidat->email)
                    ->send(new CandidatureAcceptee($candidature->candidat, $candidature->offre));
            } catch (\Exception $e) {
                \Log::warning('Email acceptation failed: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour.',
            'data'    => ['statut_candidature' => $candidature->statut_candidature],
            'errors'  => [],
        ]);
    }

    /**
     * PATCH /api/candidatures/:id/note — Note interne
     */
    public function updateNote(Request $request, int $id): JsonResponse
    {
        $candidature = Candidature::findOrFail($id);
        $request->validate(['remarque' => 'nullable|string|max:1000']);

        $candidature->update(['remarque' => $request->remarque]);

        return response()->json([
            'success' => true,
            'message' => 'Remarque mise à jour.',
            'data'    => null,
            'errors'  => [],
        ]);
    }

    /**
     * GET /api/candidatures/export — Export Excel
     */
    public function export(Request $request)
    {
        $filters = $request->only(['offre', 'date_from', 'date_to']);
        return Excel::download(new CandidaturesExport($filters), 'candidatures_' . now()->format('Y-m-d') . '.xlsx');
    }

    /**
     * DELETE /api/candidatures/:id
     */
    public function destroy(int $id): JsonResponse
    {
        $candidature = Candidature::findOrFail($id);
        $candidature->delete();

        return response()->json([
            'success' => true,
            'message' => 'Candidature supprimée.',
            'data'    => null,
            'errors'  => [],
        ]);
    }
}
