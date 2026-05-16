<?php

namespace App\Controller\Admin;

use App\Entity\Question;
use App\Entity\Quiz;
use App\Form\QuestionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/questions')]
#[IsGranted('ROLE_ADMIN')]
class QuestionAdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_questions')]
    public function index(EntityManagerInterface $em): Response
    {
        $questions = $em->getRepository(Question::class)->findBy([], ['id' => 'DESC']);
        return $this->render('admin/question/index.html.twig', [
            'questions' => $questions,
        ]);
    }

    #[Route('/new', name: 'app_admin_question_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $question = new Question();
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($question);
            $em->flush();
            $this->addFlash('success', 'Question créée.');
            return $this->redirectToRoute('app_admin_questions');
        }

        return $this->render('admin/question/form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Créer une question',
        ]);
    }

    #[Route('/edit/{id}', name: 'app_admin_question_edit')]
    public function edit(Question $question, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Question modifiée.');
            return $this->redirectToRoute('app_admin_questions');
        }

        return $this->render('admin/question/form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modifier la question',
        ]);
    }

    #[Route('/delete/{id}', name: 'app_admin_question_delete', methods: ['POST'])]
    public function delete(Question $question, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $question->getId(), $request->request->get('_token'))) {
            $em->remove($question);
            $em->flush();
            $this->addFlash('success', 'Question supprimée.');
        }
        return $this->redirectToRoute('app_admin_questions');
    }
}