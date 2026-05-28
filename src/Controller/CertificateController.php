<?php

namespace App\Controller;

use App\Entity\Enrollment;
use App\Service\CertificateService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/certificate')]
class CertificateController extends AbstractController
{
    #[Route('/download/{id}', name: 'app_certificate_download')]
    #[IsGranted('ROLE_USER')]
    public function download(Enrollment $enrollment, CertificateService $certificateService): Response
    {
        $user = $this->getUser();
        if ($enrollment->getStudent() !== $user) {
            throw $this->createAccessDeniedException('Vous n’êtes pas autorisé à voir ce certificat.');
        }

        if ($enrollment->getProgress() < 100) {
            $this->addFlash('warning', 'Vous devez terminer la formation avant de télécharger le certificat.');
            return $this->redirectToRoute('app_course_show', ['slug' => $enrollment->getCourse()->getSlug()]);
        }

        $pdfContent = $certificateService->generateCertificate($enrollment);

        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="certificat_' . $enrollment->getCourse()->getSlug() . '.pdf"'
        ]);
    }
}