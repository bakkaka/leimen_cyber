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
    #[Route('/sitemap.xml', name: 'app_sitemap_xml')]
    public function xml(EntityManagerInterface $em): Response
    {
        $urls = [];

        // Page d'accueil
        $urls[] = [
            'loc' => $this->generateUrl('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'priority' => '1.0',
            'changefreq' => 'daily',
        ];

        // Liste des formations
        $urls[] = [
            'loc' => $this->generateUrl('app_course_index', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'priority' => '0.9',
            'changefreq' => 'daily',
        ];

        // Contact
        $urls[] = [
            'loc' => $this->generateUrl('app_contact', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'priority' => '0.6',
            'changefreq' => 'monthly',
        ];

        // Formations publiées
        $courses = $em->getRepository(Course::class)->findBy(['isPublished' => true]);
        foreach ($courses as $course) {
            $urls[] = [
                'loc' => $this->generateUrl('app_course_show', ['slug' => $course->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL),
                'priority' => '0.8',
                'changefreq' => 'weekly',
            ];
        }

        // Génération du XML
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $url) {
            $xml .= '<url>';
            $xml .= '<loc>' . htmlspecialchars($url['loc']) . '</loc>';
            $xml .= '<priority>' . $url['priority'] . '</priority>';
            $xml .= '<changefreq>' . $url['changefreq'] . '</changefreq>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        return new Response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    #[Route('/plan-du-site', name: 'app_sitemap')]
    public function index(EntityManagerInterface $em): Response
    {
        $courses = $em->getRepository(Course::class)->findBy(['isPublished' => true]);

        return $this->render('sitemap/index.html.twig', [
            'courses' => $courses,
        ]);
    }
}