<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Crée (ou met à jour) le compte administrateur par défaut.
     * Idempotent : peut être exécuté plusieurs fois sans doublon.
     */
    public function run(): void
    {
        $email       = env('GREATIVA_ADMIN_EMAIL', 'admin@greativaconsulting.com');
        $motDePasse  = env('GREATIVA_ADMIN_PASSWORD', 'Admin@2024!');
        $nomComplet  = env('GREATIVA_ADMIN_NAME', 'Admin Greativa');

        Admin::updateOrCreate(
            ['email' => $email],
            [
                'nom_complet'      => $nomComplet,
                'mot_de_passe'     => Hash::make($motDePasse),
                'date_inscription' => now()->toDateString(),
            ]
        );

        $this->command->info("✅ Administrateur prêt : {$email}");
    }
}