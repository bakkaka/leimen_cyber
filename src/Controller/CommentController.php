<?php
// src/Controller/CommentController.php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Lesson;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CommentController extends AbstractController
{
    #[Route('/comment/lesson/{id}/add', name: 'app_comment_lesson_add', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function addLessonComment(Lesson $lesson, Request $request, EntityManagerInterface $em): Response
    {
        $content = $request->request->get('content');
        if (empty($content)) {
            $this->addFlash('error', 'Le commentaire ne peut pas être vide.');
            return $this->redirectToRoute('app_course_lesson', [
                'courseSlug' => $lesson->getModule()->getCourse()->getSlug(),
                'lessonId' => $lesson->getId()
            ]);
        }

        $comment = new Comment();
        $comment->setContent($content);
        $comment->setAuthor($this->getUser());
        $comment->setEntityType('lesson');
        $comment->setEntityId($lesson->getId());
        $comment->setIsApproved(false);

        $em->persist($comment);
        $em->flush();

        $this->addFlash('success', 'Votre question a été ajoutée (en attente de modération).');

        return $this->redirectToRoute('app_course_lesson', [
            'courseSlug' => $lesson->getModule()->getCourse()->getSlug(),
            'lessonId' => $lesson->getId()
        ]);
    }
}