<?php

namespace App\Service;

use App\Entity\Enrollment;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

class CertificateService
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function generateCertificate(Enrollment $enrollment): string
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);

        // Chemin absolu du logo (dossier public/images/)
        $logoPath = __DIR__ . '/../../public/images/leimen_cyber.jpg';
        $logoBase64 = '';

        if (file_exists($logoPath)) {
            $imageData = base64_encode(file_get_contents($logoPath));
            $logoBase64 = 'data:image/jpeg;base64,' . $imageData;
        } else {
            // Optionnel : log d'erreur si le fichier n'existe pas
            error_log('Logo non trouvé : ' . $logoPath);
        }

        $html = $this->twig->render('certificate/certificate.html.twig', [
            'user' => $enrollment->getStudent(),
            'course' => $enrollment->getCourse(),
            'date' => new \DateTime(),
            'certificateNumber' => uniqid('CERT-'),
            'logo_base64' => $logoBase64
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        return $dompdf->output();
    }
}