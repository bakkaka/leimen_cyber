<?php
// src/Controller/RegistrationController.php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        MailService $mailService,
        UrlGeneratorInterface $urlGenerator
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordHasher->hashPassword($user, $form->get('plainPassword')->getData())
            );
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setIsVerified(false);
            
            // Générer token de confirmation
            $token = bin2hex(random_bytes(32));
            $user->setConfirmationToken($token);

            $entityManager->persist($user);
            $entityManager->flush();

            // Envoyer email de confirmation
            $confirmationUrl = $urlGenerator->generate('app_confirm', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
            $mailService->sendRegistrationConfirmation($user, $confirmationUrl);

            $this->addFlash('success', 'Compte créé ! Vérifiez vos emails.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/confirm/{token}', name: 'app_confirm')]
    public function confirm(string $token, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->findOneBy(['confirmationToken' => $token]);
        
        if (!$user) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('app_login');
        }

        $user->setIsVerified(true);
        $user->setConfirmationToken(null);
        $entityManager->flush();

        $this->addFlash('success', 'Email confirmé ! Connectez-vous.');
        return $this->redirectToRoute('app_login');
    }
}