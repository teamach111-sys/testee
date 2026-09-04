<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Candidature extends Model
{
    protected $table = 'candidatures';
    protected $primaryKey = 'id_candidature';

    // Statuts possibles
    const STATUT_NEW          = 'Nouveau';
    const STATUT_IN_REVIEW    = 'En révision';
    const STATUT_SHORTLISTED  = 'Présélectionné';
    const STATUT_HIRED        = 'Embauché';
    const STATUT_REJECTED     = 'Rejeté';

    protected $fillable = [
        'date_candidature',
        'statut_candidature',
        'remarque',
        'id_candidat',
        'id_offre',
        'id_admin',
    ];

    protected $casts = [
        'date_candidature' => 'date',
    ];

    // ── Relations ──────────────────────────────────────────────────────────
    public function candidat()
    {
        return $this->belongsTo(Candidat::class, 'id_candidat', 'id_candidat');
    }

    public function offre()
    {
        return $this->belongsTo(Offre::class, 'id_offre', 'id_offre');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    public function entretien()
    {
        return $this->hasOne(Entretien::class, 'id_candidature', 'id_candidature');
    }
}
