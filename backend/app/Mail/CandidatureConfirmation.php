<?php

namespace App\Mail;

use App\Models\Candidat;
use App\Models\Offre;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CandidatureConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Candidat $candidat,
        public readonly Offre $offre
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmation de votre candidature — ' . $this->offre->titre_offre,
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->buildHtml(),
        );
    }

    private function buildHtml(): string
    {
        return '<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Confirmation de candidature</title>
</head>
<body style="margin:0;padding:0;background:#F7F7F7;font-family:Inter,Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#F7F7F7;padding:40px 20px;">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 20px rgba(0,0,0,0.08);">
        <!-- Header -->
        <tr>
          <td style="background:linear-gradient(135deg,#F05728,#D9431A);padding:40px;text-align:center;">
            <h1 style="color:#fff;margin:0;font-size:28px;font-weight:800;letter-spacing:-0.5px;">GREATIVA</h1>
            <p style="color:rgba(255,255,255,0.85);margin:6px 0 0;font-size:13px;">Consulting Group</p>
          </td>
        </tr>
        <!-- Body -->
        <tr>
          <td style="padding:40px;">
            <h2 style="color:#000;font-size:22px;margin:0 0 8px;">Candidature reçue ✓</h2>
            <p style="color:#333;font-size:15px;line-height:1.6;margin:0 0 24px;">
              Bonjour <strong>' . e($this->candidat->nom_complet) . '</strong>,
            </p>
            <p style="color:#333;font-size:15px;line-height:1.6;margin:0 0 24px;">
              Nous avons bien reçu votre candidature pour le poste de :
            </p>
            <div style="background:#FEF6F3;border-left:4px solid #F05728;padding:16px 20px;border-radius:0 8px 8px 0;margin-bottom:24px;">
              <p style="margin:0;font-size:18px;font-weight:700;color:#000;">' . e($this->offre->titre_offre) . '</p>
              <p style="margin:4px 0 0;font-size:13px;color:#666;">' . ($this->offre->departement->nom_departement ?? '') . ' · ' . e($this->offre->localisation ?? '') . '</p>
            </div>
            <p style="color:#333;font-size:15px;line-height:1.6;margin:0 0 32px;">
              Notre équipe RH examinera votre dossier dans les meilleurs délais. 
              Si votre profil correspond à nos critères, nous vous contacterons pour la suite du processus de recrutement.
            </p>
            <div style="text-align:center;margin-bottom:32px;">
              <a href="' . config('app.url') . '/offres" style="background:#F05728;color:#fff;text-decoration:none;padding:14px 32px;border-radius:8px;font-size:15px;font-weight:600;display:inline-block;">
                Voir toutes nos offres
              </a>
            </div>
          </td>
        </tr>
        <!-- Footer -->
        <tr>
          <td style="background:#F7F7F7;padding:24px;text-align:center;border-top:1px solid #DADADA;">
            <p style="margin:0;font-size:12px;color:#999;">
              Greativa Consulting Group · Burj Malak, A9, Rte de Safi, Marrakech 40000<br>
              <a href="mailto:projet@greativaconsulting.com" style="color:#F05728;text-decoration:none;">projet@greativaconsulting.com</a>
            </p>
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>';
    }
}
