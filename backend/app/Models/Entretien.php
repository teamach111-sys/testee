<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entretien extends Model
{
    protected $table = 'entretiens';
    protected $primaryKey = 'id_entretien';

    protected $fillable = [
        'date_entretien',
        'type_entretien',
        'resultat_entretien',
        'lien_visio',
        'remarque',
        'id_candidature',
        'id_admin',
    ];

    protected $casts = [
        'date_entretien' => 'datetime',
    ];

    // ── Relations ──────────────────────────────────────────────────────────
    public function candidature()
    {
        return $this->belongsTo(Candidature::class, 'id_candidature', 'id_candidature');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }
}
