<?php

namespace App\Controller;

use App\Entity\Quiz;
use App\Entity\UserModuleProgress;
use App\Entity\UserQuizAttempt;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/quiz')]
class QuizController extends AbstractController
{
    #[Route('/{id}', name: 'app_quiz_take')]
    #[IsGranted('ROLE_USER')]
    public function take(Quiz $quiz, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        // Vérifier si l'utilisateur a déjà réussi ce quiz
        $previousSuccess = $em->getRepository(UserQuizAttempt::class)->findOneBy([
            'user' => $user,
            'quiz' => $quiz,
            'passed' => true
        ]);

        if ($previousSuccess) {
            $this->addFlash('info', 'Vous avez déjà réussi ce quiz. Félicitations !');
            return $this->redirectToRoute('app_course_show', ['slug' => $quiz->getModule()->getCourse()->getSlug()]);
        }

        return $this->render('quiz/take.html.twig', [
            'quiz' => $quiz,
            'questions' => $quiz->getQuestions(),
        ]);
    }

    #[Route('/{id}/submit', name: 'app_quiz_submit', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function submit(Quiz $quiz, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $answers = $request->request->all('answers');

        // Éviter de re-soumettre si déjà réussi (sécurité)
        $previousSuccess = $em->getRepository(UserQuizAttempt::class)->findOneBy([
            'user' => $user,
            'quiz' => $quiz,
            'passed' => true
        ]);

        if ($previousSuccess) {
            $this->addFlash('warning', 'Vous avez déjà réussi ce quiz. Vous ne pouvez pas le repasser.');
            return $this->redirectToRoute('app_course_show', ['slug' => $quiz->getModule()->getCourse()->getSlug()]);
        }

        $score = 0;
        $total = $quiz->getQuestions()->count();

        foreach ($quiz->getQuestions() as $question) {
            $userAnswer = $answers[$question->getId()] ?? null;
            if ($userAnswer && $userAnswer === $question->getCorrectAnswer()) {
                $score++;
            }
        }

        $scorePercent = $total > 0 ? round(($score / $total) * 100) : 0;
        $passed = $scorePercent >= $quiz->getPassingScore();

        // Enregistrer la tentative
        $attempt = new UserQuizAttempt();
        $attempt->setUser($user);
        $attempt->setQuiz($quiz);
        $attempt->setScore($scorePercent);
        $attempt->setPassed($passed);
        $em->persist($attempt);
        $em->flush();

        // Mettre à jour la progression du module si le quiz est réussi
        if ($passed) {
            $moduleProgress = $em->getRepository(UserModuleProgress::class)->findOneBy([
                'user' => $user,
                'module' => $quiz->getModule()
            ]);
            if (!$moduleProgress) {
                $moduleProgress = new UserModuleProgress();
                $moduleProgress->setUser($user);
                $moduleProgress->setModule($quiz->getModule());
            }
            $moduleProgress->setCompleted(true);
            $moduleProgress->setCompletedAt(new \DateTime());
            $moduleProgress->setQuizPassed(true);
            $moduleProgress->setQuizScore($scorePercent);
            $em->persist($moduleProgress);
            $em->flush();
        }

        return $this->render('quiz/result.html.twig', [
            'quiz' => $quiz,
            'score' => $scorePercent,
            'passed' => $passed,
            'total' => $total,
            'correct' => $score,
        ]);
    }
}