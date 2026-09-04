<?php

namespace App\Mail;

use App\Models\Candidat;
use App\Models\Offre;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CandidatureAcceptee extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Candidat $candidat,
        public readonly Offre $offre
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 Félicitations ! Votre candidature a été retenue — ' . $this->offre->titre_offre,
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
<title>Candidature retenue</title>
</head>
<body style="margin:0;padding:0;background:#F7F7F7;font-family:Inter,Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#F7F7F7;padding:40px 20px;">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 20px rgba(0,0,0,0.08);">
        <!-- Header -->
        <tr>
          <td style="background:linear-gradient(135deg,#F05728,#D9431A);padding:40px;text-align:center;">
            <p style="font-size:48px;margin:0;">🎉</p>
            <h1 style="color:#fff;margin:12px 0 0;font-size:26px;font-weight:800;">Félicitations !</h1>
          </td>
        </tr>
        <!-- Body -->
        <tr>
          <td style="padding:40px;">
            <p style="color:#333;font-size:16px;line-height:1.7;margin:0 0 20px;">
              Bonjour <strong>' . e($this->candidat->nom_complet) . '</strong>,
            </p>
            <p style="color:#333;font-size:16px;line-height:1.7;margin:0 0 20px;">
              Nous avons le plaisir de vous informer que votre candidature pour le poste de 
              <strong style="color:#F05728;">' . e($this->offre->titre_offre) . '</strong> a été retenue.
            </p>
            <div style="background:#FEF6F3;border:2px solid #F05728;padding:20px;border-radius:10px;margin-bottom:28px;text-align:center;">
              <p style="margin:0;font-size:14px;color:#666;">Poste</p>
              <p style="margin:6px 0 0;font-size:20px;font-weight:700;color:#000;">' . e($this->offre->titre_offre) . '</p>
              <p style="margin:4px 0 0;font-size:13px;color:#666;">' . e($this->offre->localisation ?? '') . '</p>
            </div>
            <p style="color:#333;font-size:15px;line-height:1.7;margin:0 0 28px;">
              Notre équipe vous contactera très prochainement pour vous communiquer les prochaines étapes et les modalités d\'intégration.
            </p>
            <p style="color:#333;font-size:15px;line-height:1.7;margin:0 0 8px;">
              Bienvenue dans l\'équipe Greativa Consulting Group !
            </p>
          </td>
        </tr>
        <!-- Footer -->
        <tr>
          <td style="background:#F7F7F7;padding:24px;text-align:center;border-top:1px solid #DADADA;">
            <p style="margin:0;font-size:12px;color:#999;">
              Greativa Consulting Group · <a href="mailto:projet@greativaconsulting.com" style="color:#F05728;text-decoration:none;">projet@greativaconsulting.com</a>
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
