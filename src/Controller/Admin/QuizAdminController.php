<?php

namespace App\Controller\Admin;

use App\Entity\Quiz;
use App\Entity\Module;
use App\Form\QuizType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/quizzes')]
#[IsGranted('ROLE_ADMIN')]
class QuizAdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_quizzes')]
    public function index(EntityManagerInterface $em): Response
    {
        $quizzes = $em->getRepository(Quiz::class)->findBy([], ['id' => 'DESC']);
        return $this->render('admin/quiz/index.html.twig', [
            'quizzes' => $quizzes,
        ]);
    }

    #[Route('/new', name: 'app_admin_quiz_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $quiz = new Quiz();
        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($quiz);
            $em->flush();
            $this->addFlash('success', 'Quiz créé avec succès.');
            return $this->redirectToRoute('app_admin_quizzes');
        }

        return $this->render('admin/quiz/form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Créer un quiz',
        ]);
    }

    #[Route('/edit/{id}', name: 'app_admin_quiz_edit')]
    public function edit(Quiz $quiz, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Quiz modifié avec succès.');
            return $this->redirectToRoute('app_admin_quizzes');
        }

        return $this->render('admin/quiz/form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modifier le quiz',
        ]);
    }

    #[Route('/delete/{id}', name: 'app_admin_quiz_delete', methods: ['POST'])]
    public function delete(Quiz $quiz, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $quiz->getId(), $request->request->get('_token'))) {
            $em->remove($quiz);
            $em->flush();
            $this->addFlash('success', 'Quiz supprimé.');
        }
        return $this->redirectToRoute('app_admin_quizzes');
    }
}