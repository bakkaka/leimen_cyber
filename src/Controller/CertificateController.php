<?php
// src/Controller/CertificateController.php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Enrollment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CertificateController extends AbstractController
{
    /**
     * Convertir une image en base64
     */
    private function getLogoBase64(): string
    {
        // Chemin absolu du logo
        $logoPath = $this->getParameter('kernel.project_dir') . '/public_html/images/cyber-twitter.png';
        
        // Vérifier si le fichier existe
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = base64_encode($logoData);
            return 'data:image/png;base64,' . $logoBase64;
        }
        
        // Si le logo n'existe pas, essayer un autre chemin
        $altLogoPath = $this->getParameter('kernel.project_dir') . '/public/images/cyber-twitter.png';
        if (file_exists($altLogoPath)) {
            $logoData = file_get_contents($altLogoPath);
            $logoBase64 = base64_encode($logoData);
            return 'data:image/png;base64,' . $logoBase64;
        }
        
        // Logo par défaut (texte) si l'image n'existe pas
        return '';
    }

    #[Route('/generate-certificate', name: 'generate_certificate')]
    public function generateCertificate(Request $request, EntityManagerInterface $em): Response
    {
        $enrollmentId = $request->query->get('enrollment_id');
        
        if (!$enrollmentId) {
            $this->addFlash('error', 'Aucune inscription trouvée.');
            return $this->redirectToRoute('app_my_courses');
        }
        
        $enrollment = $em->getRepository(Enrollment::class)->find($enrollmentId);
        
        if (!$enrollment) {
            $this->addFlash('error', 'Inscription non trouvée.');
            return $this->redirectToRoute('app_my_courses');
        }
        
        $user = $this->getUser();
        $course = $enrollment->getCourse();
        
        if (!$user || !$course) {
            $this->addFlash('error', 'Utilisateur ou cours non trouvé.');
            return $this->redirectToRoute('app_my_courses');
        }
        
        // Calcul de la progression
        $completedLessonsCount = 0;
        $totalLessons = 0;
        
        foreach ($course->getModules() as $module) {
            foreach ($module->getLessons() as $lesson) {
                $totalLessons++;
                foreach ($user->getLessonProgresses() as $progress) {
                    if ($progress->getLesson()->getId() == $lesson->getId() && $progress->isCompleted()) {
                        $completedLessonsCount++;
                    }
                }
            }
        }
        
        $progressPercent = $totalLessons > 0 ? ($completedLessonsCount / $totalLessons * 100) : 0;
        
        if ($progressPercent < 100) {
            $this->addFlash('error', 'Vous devez terminer la formation pour obtenir le certificat.');
            return $this->redirectToRoute('app_course_show', ['slug' => $course->getSlug()]);
        }
        
        try {
            $options = new Options();
            $options->set('defaultFont', 'Arial');
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('isPhpEnabled', false);
            
            $dompdf = new Dompdf($options);
            
            $completionDate = (new \DateTime())->format('d/m/Y');
            $currentDate = (new \DateTime())->format('d/m/Y à H:i');
            
            // ✅ Récupérer le logo en base64
            $logoBase64 = $this->getLogoBase64();
            
            $html = $this->renderView('certificate/certificate.html.twig', [
                'user_name' => $user->getFullName(),
                'course_name' => $course->getTitle(),
                'completion_date' => $completionDate,
                'certificate_number' => 'CERT-' . date('Y') . '-' . str_pad($enrollmentId, 6, '0', STR_PAD_LEFT),
                'logo_base64' => $logoBase64,  // ✅ Logo en base64
                'quiz_title' => 'Certificat de Réussite',
                'date' => $currentDate
            ]);
            
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            
            return new Response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="certificat_' . $course->getSlug() . '.pdf"'
            ]);
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur: ' . $e->getMessage());
            return $this->redirectToRoute('app_course_show', ['slug' => $course->getSlug()]);
        }
    }
}