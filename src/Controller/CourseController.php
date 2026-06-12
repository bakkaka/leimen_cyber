<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Course;
use App\Entity\Enrollment;
use App\Entity\Lesson;
use App\Entity\Quiz;
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
        // ✅ GESTION 404 : Cours inexistant ou non publié
        $course = $em->getRepository(Course::class)->findOneBy([
            'slug' => $slug,
            'isPublished' => true
        ]);

        if (!$course) {
            throw $this->createNotFoundException('La formation demandée n\'existe pas ou n\'est pas encore disponible.');
        }

        $isEnrolled = false;
        $isAccessGranted = false;
        $enrollment = null;
        $userProgress = 0;
        $enrollmentId = null;

        if ($this->getUser()) {
            $enrollment = $em->getRepository(Enrollment::class)->findOneBy([
                'student' => $this->getUser(),
                'course' => $course
            ]);
            $isEnrolled = $enrollment !== null;
            
            // L'accès est autorisé si le paiement n'est pas en attente
            if ($enrollment && $enrollment->getPaymentMethod() !== 'pending_bank') {
                $isAccessGranted = true;
                $userProgress = $enrollment->getProgress();
                $enrollmentId = $enrollment->getId();
            }
        }

        return $this->render('course/show.html.twig', [
            'course' => $course,
            'isEnrolled' => $isEnrolled,
            'isAccessGranted' => $isAccessGranted,
            'enrollment' => $enrollment,
            'userProgress' => $userProgress,
            'enrollmentId' => $enrollmentId,
        ]);
    }

    #[Route('/formation/{slug}/learn', name: 'app_course_learn')]
    #[IsGranted('ROLE_USER')]
    public function learn(string $slug, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        
        // ✅ GESTION 404 : Cours inexistant ou non publié
        $course = $em->getRepository(Course::class)->findOneBy(['slug' => $slug, 'isPublished' => true]);
        
        if (!$course) {
            throw $this->createNotFoundException('La formation demandée n\'existe pas.');
        }
        
        // ✅ GESTION 403 : Utilisateur non connecté (déjà géré par IsGranted)
        
        $enrollment = $em->getRepository(Enrollment::class)->findOneBy(['student' => $user, 'course' => $course]);
        
        // ✅ GESTION 403 : Utilisateur non inscrit
        if (!$enrollment) {
            throw $this->createAccessDeniedException('Vous devez vous inscrire à cette formation pour y accéder.');
        }
        
        // ✅ GESTION 403 : Paiement en attente
        if ($enrollment->getPaymentMethod() === 'pending_bank') {
            throw $this->createAccessDeniedException('Votre paiement est en attente de validation. Contactez-nous sur WhatsApp pour activer votre accès.');
        }
        
        // Si la formation est déjà terminée (100%), rediriger vers la page du cours
        if ($enrollment->getProgress() >= 100) {
            $this->addFlash('info', '🎉 Félicitations ! Vous avez déjà terminé cette formation.');
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
        
        // ✅ GESTION 404 : Cours inexistant
        $course = $em->getRepository(Course::class)->findOneBy(['slug' => $courseSlug, 'isPublished' => true]);
        
        if (!$course) {
            throw $this->createNotFoundException('La formation demandée n\'existe pas.');
        }
        
        $enrollment = $em->getRepository(Enrollment::class)->findOneBy(['student' => $user, 'course' => $course]);
        
        // ✅ GESTION 403 : Utilisateur non inscrit
        if (!$enrollment) {
            throw $this->createAccessDeniedException('Vous devez vous inscrire à cette formation pour accéder aux leçons.');
        }
        
        // ✅ GESTION 403 : Paiement en attente
        if ($enrollment->getPaymentMethod() === 'pending_bank') {
            throw $this->createAccessDeniedException('Votre paiement est en attente de validation. Contactez-nous sur WhatsApp.');
        }
        
        // ✅ GESTION 404 : Leçon inexistante ou n'appartenant pas au cours
        $lesson = $em->getRepository(Lesson::class)->find($lessonId);
        
        if (!$lesson || $lesson->getModule()->getCourse() !== $course) {
            throw $this->createNotFoundException('La leçon demandée n\'existe pas ou n\'appartient pas à cette formation.');
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

        // Déterminer si la leçon suivante appartient au même module
        $hasNextLessonInSameModule = false;
        if ($nextLesson && $nextLesson->getModule()->getId() === $lesson->getModule()->getId()) {
            $hasNextLessonInSameModule = true;
        }

        // Récupérer les commentaires pour cette leçon
        $comments = $em->getRepository(Comment::class)->findBy([
            'entityType' => 'lesson',
            'entityId' => $lesson->getId(),
            'isApproved' => true
        ], ['createdAt' => 'ASC']);

        // Récupérer le quiz associé au module de cette leçon (si publié)
        $moduleQuiz = $em->getRepository(Quiz::class)->findOneBy([
            'module' => $lesson->getModule(),
            'isPublished' => true
        ]);

        // Calcul de la progression du module uniquement
        $moduleLessons = $lesson->getModule()->getLessons();
        $moduleTotal = count($moduleLessons);
        $moduleCompleted = 0;
        foreach ($moduleLessons as $l) {
            $p = $em->getRepository(UserLessonProgress::class)->findOneBy(['student' => $user, 'lesson' => $l]);
            if ($p && $p->isCompleted()) {
                $moduleCompleted++;
            }
        }
        $moduleProgressPercent = $moduleTotal > 0 ? round(($moduleCompleted / $moduleTotal) * 100) : 0;

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
            'moduleProgress' => $moduleProgressPercent,
            'prevLesson' => $prevLesson,
            'nextLesson' => $nextLesson,
            'hasNextLessonInSameModule' => $hasNextLessonInSameModule,
            'comments' => $comments,
            'moduleQuiz' => $moduleQuiz,
        ]);
    }

    #[Route('/lesson/{id}/complete', name: 'app_lesson_complete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function completeLesson(Lesson $lesson, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $course = $lesson->getModule()->getCourse();

        // ✅ GESTION 404 : Vérifier que la leçon existe (déjà fait par ParamConverter)
        if (!$lesson) {
            throw $this->createNotFoundException('La leçon demandée n\'existe pas.');
        }

        if (!$this->isCsrfTokenValid('complete_' . $lesson->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('app_course_show', ['slug' => $course->getSlug()]);
        }

        // Vérifier que l'utilisateur a bien accès à cette leçon
        $enrollment = $em->getRepository(Enrollment::class)->findOneBy([
            'student' => $user,
            'course' => $course
        ]);

        // ✅ GESTION 403 : Utilisateur non inscrit
        if (!$enrollment) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette leçon.');
        }

        // ✅ GESTION 403 : Paiement en attente
        if ($enrollment->getPaymentMethod() === 'pending_bank') {
            throw $this->createAccessDeniedException('Votre paiement est en attente de validation.');
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
            $em->persist($progress);
        }

        // Forcer la mise à jour
        $progress->setCompleted(true);
        $progress->setCompletedAt(new \DateTime());
        $em->flush();

        $this->addFlash('success', '✅ Leçon marquée comme terminée.');

        // Recalculer la progression totale
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

        // Mettre à jour l'enrollment
        if ($enrollment) {
            $enrollment->setProgress($progressPercent);
            if ($progressPercent >= 100) {
                $enrollment->setIsCompleted(true);
            }
            $em->flush();
            $this->addFlash('info', "Progression : {$progressPercent}%");
        }

        // Redirection personnalisée
        $redirect = $request->request->get('_redirect');
        if ($redirect) {
            return $this->redirect($redirect);
        }

        // Redirection par défaut
        return $this->redirectToRoute('app_course_lesson', [
            'courseSlug' => $course->getSlug(),
            'lessonId' => $lesson->getId()
        ]);
    }
}