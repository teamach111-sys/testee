<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offre extends Model
{
    protected $table = 'offres';
    protected $primaryKey = 'id_offre';

    protected $fillable = [
        'titre_offre',
        'description_offre',
        'type_contrat',
        'date_publication',
        'delai_candidature',
        'competences_requises',
        'avantages',
        'localisation',
        'est_publiee',
        'id_departement',
        'id_admin',
    ];

    protected $casts = [
        'date_publication'  => 'date',
        'delai_candidature' => 'date',
        'date_modification' => 'datetime',
        'est_publiee'       => 'boolean',
    ];

    // ── Relations ──────────────────────────────────────────────────────────
    public function departement()
    {
        return $this->belongsTo(Departement::class, 'id_departement', 'id_departement');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    public function candidatures()
    {
        return $this->hasMany(Candidature::class, 'id_offre', 'id_offre');
    }

    public function archive()
    {
        return $this->hasOne(Archive::class, 'id_offre', 'id_offre');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────
    public function scopePubliee($query)
    {
        return $query->where('est_publiee', true)->whereDoesntHave('archive');
    }
}
