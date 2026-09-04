<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdminAuthController extends Controller
{
    /**
     * POST /api/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        $key = 'login:' . $request->ip();

        // Blocage après 5 tentatives
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => "Trop de tentatives. Réessayez dans {$seconds} secondes.",
                'data'    => null,
                'errors'  => ['rate_limit' => "Réessayez dans {$seconds} secondes."],
            ], 429);
        }

        $admin = Admin::where('email', $request->email)->first();

        if (! $admin || ! Hash::check($request->password, $admin->mot_de_passe)) {
            RateLimiter::hit($key, 300); // 5 min
            return response()->json([
                'success' => false,
                'message' => 'Identifiants incorrects.',
                'data'    => null,
                'errors'  => ['credentials' => 'Email ou mot de passe invalide.'],
            ], 401);
        }

        RateLimiter::clear($key);

        // Supprimer les anciens tokens
        $admin->tokens()->delete();

        $token = $admin->createToken('admin-session', ['*'], now()->addMinutes(config('session.lifetime', 120)));

        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie.',
            'data'    => [
                'admin' => [
                    'id'          => $admin->id_admin,
                    'nom_complet' => $admin->nom_complet,
                    'email'       => $admin->email,
                ],
                'token'      => $token->plainTextToken,
                'expires_at' => $token->accessToken->expires_at,
            ],
            'errors' => [],
        ]);
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie.',
            'data'    => null,
            'errors'  => [],
        ]);
    }

    /**
     * GET /api/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Profil admin.',
            'data'    => [
                'id'               => $request->user()->id_admin,
                'nom_complet'      => $request->user()->nom_complet,
                'email'            => $request->user()->email,
                'date_inscription' => $request->user()->date_inscription,
            ],
            'errors' => [],
        ]);
    }

    /**
     * POST /api/auth/forgot-password
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $admin = Admin::where('email', $request->email)->first();

        if (! $admin) {
            // On ne révèle pas si l'email existe
            return response()->json([
                'success' => true,
                'message' => 'Si cet email existe, un lien de réinitialisation a été envoyé.',
                'data'    => null,
                'errors'  => [],
            ]);
        }

        $status = Password::broker('admins')->sendResetLink(['email' => $request->email]);

        return response()->json([
            'success' => true,
            'message' => 'Si cet email existe, un lien de réinitialisation a été envoyé.',
            'data'    => null,
            'errors'  => [],
        ]);
    }

    /**
     * POST /api/auth/reset-password
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::broker('admins')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($admin, $password) {
                $admin->forceFill([
                    'mot_de_passe' => Hash::make($password),
                ])->save();
                $admin->tokens()->delete();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'message' => 'Mot de passe réinitialisé avec succès.',
                'data'    => null,
                'errors'  => [],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Le lien est invalide ou expiré.',
            'data'    => null,
            'errors'  => ['token' => __($status)],
        ], 422);
    }
}
