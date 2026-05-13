<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\Enrollment;
use App\Entity\Lesson;
use App\Entity\UserLessonProgress;
use App\Repository\CourseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CourseController extends AbstractController
{
    #[Route('/formations', name: 'app_course_index')]
    public function index(CourseRepository $courseRepository): Response
    {
        return $this->render('course/index.html.twig', [
            'courses' => $courseRepository->findBy(['isPublished' => true]),
            'beginnerCourses' => $courseRepository->findBy(['level' => 'beginner', 'isPublished' => true]),
            'intermediateCourses' => $courseRepository->findBy(['level' => 'intermediate', 'isPublished' => true]),
            'advancedCourses' => $courseRepository->findBy(['level' => 'advanced', 'isPublished' => true]),
        ]);
    }

    #[Route('/formations/{slug}', name: 'app_course_show')]
    public function show(string $slug, EntityManagerInterface $em): Response
    {
        $course = $em->getRepository(Course::class)->findOneBy([
            'slug' => $slug,
            'isPublished' => true
        ]);

        if (!$course) {
            throw $this->createNotFoundException('Formation non trouvée');
        }

        $isEnrolled = false;
        $enrollment = null;

        if ($this->getUser()) {
            $enrollment = $em->getRepository(Enrollment::class)->findOneBy([
                'student' => $this->getUser(),
                'course' => $course
            ]);
            $isEnrolled = $enrollment !== null;
        }

        return $this->render('course/show.html.twig', [
            'course' => $course,
            'isEnrolled' => $isEnrolled,
            'enrollment' => $enrollment
        ]);
    }

    #[Route('/formation/{slug}/learn', name: 'app_course_learn')]
    #[IsGranted('ROLE_USER')]
    public function learn(string $slug, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        
        $course = $em->getRepository(Course::class)->findOneBy(['slug' => $slug, 'isPublished' => true]);
        
        if (!$course) {
            throw $this->createNotFoundException('Formation non trouvée');
        }
        
        $enrollment = $em->getRepository(Enrollment::class)->findOneBy(['student' => $user, 'course' => $course]);
        
        if (!$enrollment) {
            $this->addFlash('warning', 'Vous devez vous inscrire.');
            return $this->redirectToRoute('app_course_show', ['slug' => $slug]);
        }
        
        // Chercher la première leçon non terminée
        foreach ($course->getModules() as $module) {
            foreach ($module->getLessons() as $lesson) {
                $progress = $em->getRepository(UserLessonProgress::class)->findOneBy([
                    'student' => $user,
                    'lesson' => $lesson
                ]);
                if (!$progress || !$progress->isCompleted()) {
                    return $this->redirectToRoute('app_course_lesson', [
                        'courseSlug' => $slug,
                        'lessonId' => $lesson->getId()
                    ]);
                }
            }
        }
        
        // Toutes les leçons sont terminées
        $this->addFlash('success', '🎉 Félicitations ! Vous avez terminé toutes les leçons de cette formation.');
        return $this->redirectToRoute('app_course_show', ['slug' => $slug]);
    }

    #[Route('/formation/{courseSlug}/lesson/{lessonId}', name: 'app_course_lesson')]
    #[IsGranted('ROLE_USER')]
    public function lesson(string $courseSlug, int $lessonId, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        
        $course = $em->getRepository(Course::class)->findOneBy(['slug' => $courseSlug, 'isPublished' => true]);
        
        if (!$course) {
            throw $this->createNotFoundException('Formation non trouvée');
        }
        
        $enrollment = $em->getRepository(Enrollment::class)->findOneBy(['student' => $user, 'course' => $course]);
        
        if (!$enrollment) {
            $this->addFlash('warning', 'Vous devez vous inscrire.');
            return $this->redirectToRoute('app_course_show', ['slug' => $courseSlug]);
        }
        
        $lesson = $em->getRepository(Lesson::class)->find($lessonId);
        
        if (!$lesson || $lesson->getModule()->getCourse() !== $course) {
            throw $this->createNotFoundException('Leçon non trouvée');
        }
        
        // Récupérer ou créer la progression
        $progress = $em->getRepository(UserLessonProgress::class)->findOneBy([
            'student' => $user,
            'lesson' => $lesson
        ]);
        
        if (!$progress) {
            $progress = new UserLessonProgress();
            $progress->setStudent($user);
            $progress->setLesson($lesson);
            $progress->setCompleted(false);
            $em->persist($progress);
            $em->flush();
        }
        
        // Calcul de la progression totale
        $totalLessons = 0;
        $completedLessons = 0;
        foreach ($course->getModules() as $module) {
            foreach ($module->getLessons() as $l) {
                $totalLessons++;
                $p = $em->getRepository(UserLessonProgress::class)->findOneBy(['student' => $user, 'lesson' => $l]);
                if ($p && $p->isCompleted()) {
                    $completedLessons++;
                }
            }
        }
        $progressPercent = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;
        $enrollment->setProgress($progressPercent);
        $em->flush();
        
        // Leçons précédente / suivante
        $lessonsList = [];
        foreach ($course->getModules() as $module) {
            foreach ($module->getLessons() as $l) {
                $lessonsList[] = $l;
            }
        }
        
        $prevLesson = null;
        $nextLesson = null;
        foreach ($lessonsList as $index => $l) {
            if ($l->getId() === $lesson->getId()) {
                $prevLesson = $lessonsList[$index - 1] ?? null;
                $nextLesson = $lessonsList[$index + 1] ?? null;
                break;
            }
        }
        
        return $this->render('course/learn.html.twig', [
            'course' => $course,
            'lesson' => $lesson,
            'progress' => $progress,
            'enrollment' => $enrollment,
            'stats' => [
                'percentage' => $progressPercent,
                'completed' => $completedLessons,
                'total' => $totalLessons
            ],
            'prevLesson' => $prevLesson,
            'nextLesson' => $nextLesson
        ]);
    }

    #[Route('/lesson/{id}/complete', name: 'app_lesson_complete', methods: ['POST'])]
#[IsGranted('ROLE_USER')]
public function completeLesson(Lesson $lesson, Request $request, EntityManagerInterface $em): Response
{
    $user = $this->getUser();
    $course = $lesson->getModule()->getCourse();

    if (!$this->isCsrfTokenValid('complete_' . $lesson->getId(), $request->request->get('_token'))) {
        $this->addFlash('error', 'Token invalide.');
        return $this->redirectToRoute('app_course_show', ['slug' => $course->getSlug()]);
    }

    // 1. Récupérer ou créer la progression
    $progress = $em->getRepository(UserLessonProgress::class)->findOneBy([
        'student' => $user,
        'lesson' => $lesson
    ]);

    if (!$progress) {
        $progress = new UserLessonProgress();
        $progress->setStudent($user);
        $progress->setLesson($lesson);
        $em->persist($progress);
    }

    // 2. Forcer la mise à jour (même si déjà complétée, on s'assure)
    $progress->setCompleted(true);
    $progress->setCompletedAt(new \DateTime());

    // 3. Flush immédiat pour sauvegarder
    $em->flush();

    $this->addFlash('success', '✅ Leçon marquée comme terminée.');

    // 4. Recalculer la progression totale
    $totalLessons = 0;
    $completedLessons = 0;
    foreach ($course->getModules() as $module) {
        foreach ($module->getLessons() as $l) {
            $totalLessons++;
            $p = $em->getRepository(UserLessonProgress::class)->findOneBy([
                'student' => $user,
                'lesson' => $l
            ]);
            if ($p && $p->isCompleted()) {
                $completedLessons++;
            }
        }
    }

    $progressPercent = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;

    // 5. Mettre à jour l'enrollment
    $enrollment = $em->getRepository(Enrollment::class)->findOneBy([
        'student' => $user,
        'course' => $course
    ]);

    if ($enrollment) {
        $enrollment->setProgress($progressPercent);
        if ($progressPercent >= 100) {
            $enrollment->setIsCompleted(true);
        }
        $em->flush();
        $this->addFlash('info', "Progression : {$progressPercent}%");
    }

    // 6. Redirection personnalisée (si présente dans le formulaire)
    $redirect = $request->request->get('_redirect');
    if ($redirect) {
        return $this->redirect($redirect);
    }

    // 7. Trouver la prochaine leçon non terminée (comportement par défaut)
    $nextLesson = null;
    foreach ($course->getModules() as $module) {
        foreach ($module->getLessons() as $l) {
            $p = $em->getRepository(UserLessonProgress::class)->findOneBy([
                'student' => $user,
                'lesson' => $l
            ]);
            if (!$p || !$p->isCompleted()) {
                $nextLesson = $l;
                break 2;
            }
        }
    }

    if ($nextLesson) {
        return $this->redirectToRoute('app_course_lesson', [
            'courseSlug' => $course->getSlug(),
            'lessonId' => $nextLesson->getId()
        ]);
    }

    // 8. Plus de leçons → formation terminée
    $this->addFlash('success', '🎉 Félicitations ! Formation terminée avec succès !');
    return $this->redirectToRoute('app_course_show', ['slug' => $course->getSlug()]);
}
}