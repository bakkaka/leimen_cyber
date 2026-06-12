<?php
// src/Controller/ErrorController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ErrorController extends AbstractController
{
    #[Route('/error/{statusCode}', name: 'app_error_show')]
    public function show(Request $request, int $statusCode = 404): Response
    {
        // ✅ Journaliser l'erreur pour analyse SEO
        $this->logError($request, $statusCode);
        
        // Afficher la page d'erreur correspondante
        return $this->render("bundles/TwigBundle/Exception/error{$statusCode}.html.twig", [
            'status_code' => $statusCode,
            'status_text' => $this->getStatusText($statusCode),
        ]);
    }
    
    private function logError(Request $request, int $statusCode): void
    {
        // Enregistrer les erreurs 404 pour analyser les liens cassés
        if ($statusCode === 404) {
            $logFile = __DIR__ . '/../../var/log/404_errors.log';
            $referer = $request->headers->get('referer', 'Direct');
            $url = $request->getRequestUri();
            
            $log = sprintf("[%s] 404 - %s (Ref: %s)\n", 
                date('Y-m-d H:i:s'), 
                $url, 
                $referer
            );
            
            file_put_contents($logFile, $log, FILE_APPEND);
        }
    }
    
    private function getStatusText(int $code): string
    {
        $statuses = [
            404 => 'Page non trouvée',
            403 => 'Accès interdit',
            500 => 'Erreur serveur',
        ];
        
        return $statuses[$code] ?? 'Erreur';
    }
}