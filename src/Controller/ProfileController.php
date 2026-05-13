<?php
// src/Controller/ProfileController.php

namespace App\Controller;

use App\Entity\Enrollment;
use App\Entity\User;
use App\Entity\UserLessonProgress;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profile')]
#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'app_profile')]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $enrollments = $em->getRepository(Enrollment::class)->findBy(['student' => $user], ['enrolledAt' => 'DESC']);
        
        // Calculer la progression réelle pour chaque inscription
        foreach ($enrollments as $enrollment) {
            $realProgress = $this->calculateCourseProgress($user, $enrollment->getCourse(), $em);
            if ($enrollment->getProgress() != $realProgress) {
                $enrollment->setProgress($realProgress);
                if ($realProgress >= 100) {
                    $enrollment->setIsCompleted(true);
                }
            }
        }
        $em->flush();

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'enrollments' => $enrollments
        ]);
    }

    #[Route('/edit', name: 'app_profile_edit')]
    public function edit(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        
        if ($request->isMethod('POST')) {
            $fullName = $request->request->get('fullName');
            $phone = $request->request->get('phone');
            $newPassword = $request->request->get('newPassword');
            
            if ($fullName) {
                $user->setFullName($fullName);
            }
            
            if ($phone) {
                $user->setPhone($phone);
            }
            
            if ($newPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);
                $this->addFlash('success', 'Votre mot de passe a été modifié.');
            }
            
            $em->flush();
            $this->addFlash('success', 'Profil mis à jour avec succès.');
            return $this->redirectToRoute('app_profile');
        }
        
        return $this->render('profile/edit.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/mes-formations', name: 'app_my_courses')]
    public function myCourses(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $enrollments = $em->getRepository(Enrollment::class)->findBy(['student' => $user], ['enrolledAt' => 'DESC']);
        
        $completedCourses = 0;
        $totalProgress = 0;
        
        foreach ($enrollments as $enrollment) {
            $realProgress = $this->calculateCourseProgress($user, $enrollment->getCourse(), $em);
            $totalProgress += $realProgress;
            
            if ($enrollment->getProgress() != $realProgress) {
                $enrollment->setProgress($realProgress);
                if ($realProgress >= 100) {
                    $enrollment->setIsCompleted(true);
                }
                $em->flush();
            }
            
            if ($realProgress >= 100) {
                $completedCourses++;
            }
        }
        
        $averageProgress = count($enrollments) > 0 ? round($totalProgress / count($enrollments)) : 0;
        
        return $this->render('profile/my_courses.html.twig', [
            'enrollments' => $enrollments,
            'averageProgress' => $averageProgress,
            'totalCourses' => count($enrollments),
            'completedCourses' => $completedCourses,
        ]);
    }

    #[Route('/progression', name: 'app_progress')]
    public function progress(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $enrollments = $em->getRepository(Enrollment::class)->findBy(['student' => $user]);
        
        $stats = [
            'total' => count($enrollments),
            'completed' => 0,
            'inProgress' => 0,
            'notStarted' => 0,
            'totalLessons' => 0,
            'completedLessons' => 0,
        ];
        
        foreach ($enrollments as $enrollment) {
            $course = $enrollment->getCourse();
            $courseTotalLessons = 0;
            $courseCompletedLessons = 0;
            
            foreach ($course->getModules() as $module) {
                foreach ($module->getLessons() as $lesson) {
                    $courseTotalLessons++;
                    $stats['totalLessons']++;
                    
                    $progress = $em->getRepository(UserLessonProgress::class)->findOneBy([
                        'student' => $user,
                        'lesson' => $lesson
                    ]);
                    if ($progress && $progress->isCompleted()) {
                        $courseCompletedLessons++;
                        $stats['completedLessons']++;
                    }
                }
            }
            
            $courseProgress = $courseTotalLessons > 0 ? round(($courseCompletedLessons / $courseTotalLessons) * 100) : 0;
            
            if ($courseProgress >= 100) {
                $stats['completed']++;
            } elseif ($courseProgress > 0) {
                $stats['inProgress']++;
            } else {
                $stats['notStarted']++;
            }
        }
        
        return $this->render('profile/progress.html.twig', [
            'stats' => $stats,
            'enrollments' => $enrollments,
        ]);
    }

    /**
     * Calcule la progression réelle d'un cours pour un utilisateur
     */
    private function calculateCourseProgress(User $user, $course, EntityManagerInterface $em): int
    {
        $totalLessons = 0;
        $completedLessons = 0;
        
        foreach ($course->getModules() as $module) {
            foreach ($module->getLessons() as $lesson) {
                $totalLessons++;
                $progress = $em->getRepository(UserLessonProgress::class)->findOneBy([
                    'student' => $user,
                    'lesson' => $lesson
                ]);
                if ($progress && $progress->isCompleted()) {
                    $completedLessons++;
                }
            }
        }
        
        return $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;
    }
}