<?php

namespace App\Exports;

use App\Models\Candidature;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CandidaturesExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(private array $filters = []) {}

    public function query()
    {
        $query = Candidature::with(['candidat', 'offre.departement'])
            ->orderBy('date_candidature', 'desc');

        if (! empty($this->filters['offre'])) {
            $query->where('id_offre', $this->filters['offre']);
        }
        if (! empty($this->filters['date_from'])) {
            $query->where('date_candidature', '>=', $this->filters['date_from']);
        }
        if (! empty($this->filters['date_to'])) {
            $query->where('date_candidature', '<=', $this->filters['date_to']);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nom complet',
            'Email',
            'Téléphone',
            'Offre',
            'Département',
            'Statut',
            'Date candidature',
            'LinkedIn',
            'Portfolio',
        ];
    }

    public function map($candidature): array
    {
        return [
            $candidature->id_candidature,
            $candidature->candidat->nom_complet,
            $candidature->candidat->email,
            $candidature->candidat->telephone,
            $candidature->offre->titre_offre,
            $candidature->offre->departement->nom_departement ?? '',
            $candidature->statut_candidature,
            $candidature->date_candidature->format('d/m/Y'),
            $candidature->candidat->linkedin_url ?? '',
            $candidature->candidat->portfolio_url ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F05728'],
                ],
            ],
        ];
    }
}
