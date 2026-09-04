<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Archive extends Model
{
    protected $table = 'archives';
    protected $primaryKey = 'id_archive';
    public $timestamps = false;

    protected $fillable = [
        'date_archivage',
        'motif_archivage',
        'id_offre',
    ];

    protected $casts = [
        'date_archivage' => 'date',
    ];

    // ── Relations ──────────────────────────────────────────────────────────
    public function offre()
    {
        return $this->belongsTo(Offre::class, 'id_offre', 'id_offre');
    }
}
