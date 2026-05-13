<?php
// src/Controller/HomeController.php

namespace App\Controller;

use App\Repository\CourseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(CourseRepository $courseRepository): Response
    {
        // Récupérer les dernières formations publiées (limité à 3)
        $latestCourses = $courseRepository->findBy(
            ['isPublished' => true], 
            ['createdAt' => 'DESC'], 
            3
        );
        
        // Récupérer les formations par niveau
        $beginnerCourses = $courseRepository->findBy(['level' => 'beginner', 'isPublished' => true]);
        $intermediateCourses = $courseRepository->findBy(['level' => 'intermediate', 'isPublished' => true]);
        $advancedCourses = $courseRepository->findBy(['level' => 'advanced', 'isPublished' => true]);
        
        // Compter le nombre total d'étudiants (pour la statistique)
        $totalStudents = 0;
        foreach ($courseRepository->findAll() as $course) {
            $totalStudents += $course->getEnrollments()->count();
        }
        
        return $this->render('home/index.html.twig', [
            'latestCourses' => $latestCourses,
            'beginnerCourses' => $beginnerCourses,
            'intermediateCourses' => $intermediateCourses,
            'advancedCourses' => $advancedCourses,
            'totalCourses' => $courseRepository->count(['isPublished' => true]),
            'totalStudents' => $totalStudents,
        ]);
    }
}