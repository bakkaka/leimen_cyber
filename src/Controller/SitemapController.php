<?php

namespace App\Controller;

use App\Entity\Course;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SitemapController extends AbstractController
{
    #[Route('/sitemap.xml', name: 'app_sitemap')]
    public function index(EntityManagerInterface $em): Response
    {
        $urls = [];

        // Page d'accueil
        $urls[] = $this->generateUrl('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL);

        // Liste des formations
        $urls[] = $this->generateUrl('app_course_index', [], UrlGeneratorInterface::ABSOLUTE_URL);

        // Connexion / Inscription (optionnel)
        $urls[] = $this->generateUrl('app_login', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $urls[] = $this->generateUrl('app_register', [], UrlGeneratorInterface::ABSOLUTE_URL);

        // Formations publiées
        $courses = $em->getRepository(Course::class)->findBy(['isPublished' => true]);
        foreach ($courses as $course) {
            $urls[] = $this->generateUrl('app_course_show', ['slug' => $course->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        // Génération du XML
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $url) {
            $xml .= '<url>';
            $xml .= '<loc>' . htmlspecialchars($url) . '</loc>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        return new Response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}