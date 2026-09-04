<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens, Notifiable, CanResetPassword;

    protected $table = 'admins';
    protected $primaryKey = 'id_admin';

    protected $fillable = [
        'nom_complet',
        'email',
        'mot_de_passe',
    ];

    protected $hidden = [
        'mot_de_passe',
    ];

    protected $casts = [
        'date_inscription' => 'date',
    ];

    // Laravel uses 'password' by default for auth — map it
    public function getAuthPassword(): string
    {
        return $this->mot_de_passe;
    }

    // ── Relations ──────────────────────────────────────────────────────────
    public function departements()
    {
        return $this->hasMany(Departement::class, 'id_admin', 'id_admin');
    }

    public function offres()
    {
        return $this->hasMany(Offre::class, 'id_admin', 'id_admin');
    }

    public function candidats()
    {
        return $this->hasMany(Candidat::class, 'id_admin', 'id_admin');
    }

    public function candidatures()
    {
        return $this->hasMany(Candidature::class, 'id_admin', 'id_admin');
    }

    public function entretiens()
    {
        return $this->hasMany(Entretien::class, 'id_admin', 'id_admin');
    }
}
