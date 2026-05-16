<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/comments')]
#[IsGranted('ROLE_ADMIN')]
class CommentAdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_comments')]
    public function index(EntityManagerInterface $em): Response
    {
        $comments = $em->getRepository(Comment::class)->findBy([], ['createdAt' => 'DESC']);
        return $this->render('admin/comment/index.html.twig', [
            'comments' => $comments,
        ]);
    }

    #[Route('/{id}/approve', name: 'app_admin_comment_approve', methods: ['POST'])]
    public function approve(Comment $comment, EntityManagerInterface $em, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('approve' . $comment->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('app_admin_comments');
        }
        $comment->setIsApproved(true);
        $em->flush();
        $this->addFlash('success', 'Commentaire approuvé.');
        return $this->redirectToRoute('app_admin_comments');
    }

    #[Route('/{id}/delete', name: 'app_admin_comment_delete', methods: ['POST'])]
    public function delete(Comment $comment, EntityManagerInterface $em, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $comment->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('app_admin_comments');
        }
        $em->remove($comment);
        $em->flush();
        $this->addFlash('success', 'Commentaire supprimé.');
        return $this->redirectToRoute('app_admin_comments');
    }
}