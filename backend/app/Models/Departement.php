<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departement extends Model
{
    protected $table = 'departements';
    protected $primaryKey = 'id_departement';
    public $timestamps = false;

    protected $fillable = [
        'nom_departement',
        'description',
        'id_admin',
    ];

    // ── Relations ──────────────────────────────────────────────────────────
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    public function offres()
    {
        return $this->hasMany(Offre::class, 'id_departement', 'id_departement');
    }
}
