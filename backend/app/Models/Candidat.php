<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Candidat extends Model
{
    protected $table = 'candidats';
    protected $primaryKey = 'id_candidat';

    protected $fillable = [
        'nom_complet',
        'email',
        'telephone',
        'cv',
        'lettre_motivation',
        'linkedin_url',
        'portfolio_url',
        'note',
        'id_admin',
    ];

    protected $casts = [
        'date_inscription' => 'date',
    ];

    // ── Relations ──────────────────────────────────────────────────────────
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    public function candidatures()
    {
        return $this->hasMany(Candidature::class, 'id_candidat', 'id_candidat');
    }
}
