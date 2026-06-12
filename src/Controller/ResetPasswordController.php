<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class ResetPasswordController extends AbstractController
{
    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function reset(
        Request $request,
        string $token,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response {
      $user = $em->getRepository(User::class)
    ->createQueryBuilder('u')
    ->where('u.resetToken = :token')
    ->andWhere('u.resetTokenExpiresAt > :now')
    ->setParameter('token', $token)
    ->setParameter('now', new \DateTimeImmutable())
    ->getQuery()
    ->getOneOrNullResult();

        if (!$user) {
            $this->addFlash('error', 'Token invalide ou expiré.');
            return $this->redirectToRoute('app_forgot_password');
        }

        if ($request->isMethod('POST')) {
            $plainPassword = $request->request->get('password');
            $submittedToken = $request->request->get('_csrf_token');

            // ⭐⭐⭐ VÉRIFICATION CSRF ⭐⭐⭐
            if (!$csrfTokenManager->isTokenValid(new CsrfToken('reset_password', $submittedToken))) {
                $this->addFlash('error', 'Token CSRF invalide.');
                return $this->redirectToRoute('app_reset_password', ['token' => $token]);
            }

            // Mettre à jour le mot de passe
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            $user->setResetToken(null);
            $user->setResetTokenExpiresAt(null);
            $em->flush();

            // ⭐⭐⭐ SOLUTION CSRF : invalider l'ancienne session ⭐⭐⭐
            $request->getSession()->invalidate();

            $this->addFlash('success', 'Mot de passe réinitialisé. Connectez-vous.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/reset.html.twig', [
            'token' => $token,
            'csrf_token' => $csrfTokenManager->getToken('reset_password')->getValue()
        ]);
    }
}