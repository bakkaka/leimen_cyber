<?php
// src/Controller/ForgetPasswordController.php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\BrevoApiMailerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ForgotPasswordController extends AbstractController
{
    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function request(
        Request $request,
        EntityManagerInterface $em,
        BrevoApiMailerService $brevoService, // Votre service d'email
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response {

        if ($request->isMethod('POST')) {

            $email = trim((string) $request->request->get('email'));
            $submittedToken = $request->request->get('_csrf_token');

            // Vérification CSRF
            if (!$csrfTokenManager->isTokenValid(new CsrfToken('forgot_password', $submittedToken))) {
                $this->addFlash('error', 'Token CSRF invalide.');
                return $this->redirectToRoute('app_forgot_password');
            }

            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

            // MÊME SI L'UTILISATEUR N'EXISTE PAS, on redirige avec un message générique
            if ($user) {
                try {
                    // 1. Générer le token
                    $resetToken = bin2hex(random_bytes(32));
                    $expiresAt = new \DateTimeImmutable('+1 hour');

                    // 2. ✅ STOCKER LE TOKEN EN BDD (AVANT l'envoi de l'email)
                    $user->setResetToken($resetToken);
                    $user->setResetTokenExpiresAt($expiresAt);
                    
                    // 3. ✅ FLUSH EN BDD - Sauvegarde immédiate
                    $em->flush();

                    // 4. ✅ VÉRIFIER que le token est bien en BDD (optionnel, pour debug)
                    // $savedToken = $user->getResetToken();
                    // if (!$savedToken) {
                    //     throw new \Exception('Le token n\'a pas été sauvegardé en BDD');
                    // }

                    // 5. ✅ MAINTENANT envoyer l'email (après le flush réussi)
                    // Générer le lien ABSOLU
                   $brevoService->sendResetPasswordEmail(
                  $user->getEmail(),
                    $resetToken
                  );

                    $this->addFlash('success', 'Un email de réinitialisation a été envoyé.');
                    
                } catch (\Exception $e) {
                    // En cas d'erreur, nettoyer le token pour éviter les orphelins
                    $user->setResetToken(null);
                    $user->setResetTokenExpiresAt(null);
                    $em->flush();
                    
                    $this->addFlash('error', 'Une erreur est survenue. Veuillez réessayer.');
                }
            } else {
                // Pour des raisons de sécurité, on dit le même message même si l'utilisateur n'existe pas
                $this->addFlash('success', 'Si cette adresse email existe dans notre système, un email de réinitialisation a été envoyé.');
            }

            return $this->redirectToRoute('app_forgot_password');
        }

        return $this->render('reset_password/request.html.twig', [
            'csrf_token' => $csrfTokenManager->getToken('forgot_password')->getValue()
        ]);
    }

    /**
     * Étape 2: Vérifier le token et afficher le formulaire de nouveau mot de passe
     */
    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function reset(
        string $token,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        // Vérifier si le token existe en BDD et n'a pas expiré
        $user = $em->getRepository(User::class)->findOneBy(['resetToken' => $token]);
        
        if (!$user) {
            $this->addFlash('error', 'Token de réinitialisation invalide.');
            return $this->redirectToRoute('app_forgot_password');
        }
        
        $now = new \DateTimeImmutable();
        $expiresAt = $user->getResetTokenExpiresAt();
        
        if (!$expiresAt || $now > $expiresAt) {
            // Nettoyer le token expiré
            $user->setResetToken(null);
            $user->setResetTokenExpiresAt(null);
            $em->flush();
            
            $this->addFlash('error', 'Ce lien a expiré. Veuillez faire une nouvelle demande.');
            return $this->redirectToRoute('app_forgot_password');
        }
        
        // Afficher le formulaire de nouveau mot de passe
        return $this->render('reset_password/reset.html.twig', [
            'token' => $token
        ]);
    }

    /**
     * Étape 3: Traiter le nouveau mot de passe
     */
    #[Route('/reset-password/{token}/submit', name: 'app_reset_password_submit', methods: ['POST'])]
    public function resetPassword(
        string $token,
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        // Vérifier à nouveau que le token est valide
        $user = $em->getRepository(User::class)->findOneBy(['resetToken' => $token]);
        
        if (!$user) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('app_forgot_password');
        }
        
        $now = new \DateTimeImmutable();
        $expiresAt = $user->getResetTokenExpiresAt();
        
        if (!$expiresAt || $now > $expiresAt) {
            $user->setResetToken(null);
            $user->setResetTokenExpiresAt(null);
            $em->flush();
            
            $this->addFlash('error', 'Ce lien a expiré.');
            return $this->redirectToRoute('app_forgot_password');
        }
        
        $newPassword = $request->request->get('password');
        $confirmPassword = $request->request->get('confirm_password');
        
        if (strlen($newPassword) < 8) {
            $this->addFlash('error', 'Le mot de passe doit contenir au moins 8 caractères.');
            return $this->redirectToRoute('app_reset_password', ['token' => $token]);
        }
        
        if ($newPassword !== $confirmPassword) {
            $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
            return $this->redirectToRoute('app_reset_password', ['token' => $token]);
        }
        
        // Mettre à jour le mot de passe
        $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);
        
        // Supprimer le token (usage unique)
        $user->setResetToken(null);
        $user->setResetTokenExpiresAt(null);
        
        $em->flush();
        
        $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès.');
        return $this->redirectToRoute('app_login');
    }
}