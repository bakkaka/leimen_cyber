<?php
// src/Controller/PrerequisiteController.php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Prerequisite;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PrerequisiteController extends AbstractController
{
    #[Route('/prerequisite/{slug}', name: 'app_prerequisite_show')]
    public function show(string $slug, EntityManagerInterface $em, Request $request): Response
    {
        $prerequisite = $em->getRepository(Prerequisite::class)->findOneBy([
            'slug' => $slug,
            'isPublished' => true
        ]);

        if (!$prerequisite) {
            throw $this->createNotFoundException('Page non trouvée');
        }

        // Récupérer les commentaires pour ce prérequis
        $comments = $em->getRepository(Comment::class)->findBy([
            'entityType' => 'prerequisite',
            'entityId' => $prerequisite->getId(),
            'isApproved' => true
        ], ['createdAt' => 'ASC']);

        $commentForm = $this->createForm(CommentType::class);
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid() && $this->getUser()) {
            $comment = new Comment();
            $comment->setContent($commentForm->get('content')->getData());
            $comment->setAuthor($this->getUser());
            $comment->setEntityType('prerequisite');
            $comment->setEntityId($prerequisite->getId());
            $comment->setIsApproved(false); // modération
            $em->persist($comment);
            $em->flush();
            $this->addFlash('success', 'Votre question a été ajoutée (en attente de modération).');
            return $this->redirectToRoute('app_prerequisite_show', ['slug' => $prerequisite->getSlug()]);
        }

        return $this->render('prerequisite/show.html.twig', [
            'prerequisite' => $prerequisite,
            'comments' => $comments,
            'commentForm' => $commentForm->createView(),
        ]);
    }
}