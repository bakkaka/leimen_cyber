<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        UrlGeneratorInterface $urlGenerator
    ): Response {

        $user = new User();

        $form = $this->createForm(
            RegistrationFormType::class,
            $user
        );

        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
        ) {

            $plainPassword = $form->get('plainPassword')->getData();

            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $plainPassword
                )
            );

            $user->setRoles(['ROLE_USER']);

            $user->setCreatedAt(new \DateTimeImmutable());

            $user->setIsVerified(false);

            // ✅ Génération du token
            $token = bin2hex(random_bytes(32));
            $user->setConfirmationToken($token);

            $entityManager->persist($user);
            $entityManager->flush();

            // ✅ Génération de l'URL de confirmation
            $confirmationUrl = $urlGenerator->generate(
                'app_confirm_registration',
                ['token' => $token],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            // ✅ Email de confirmation
            $email = (new TemplatedEmail())
                ->from('abdoubakka@gmail.com')
                ->to($user->getEmail())
                ->subject('Confirmation de votre inscription')
                ->htmlTemplate('emails/confirm_registration.html.twig')
                ->context([
                    'user' => $user,
                    'confirmationUrl' => $confirmationUrl
                ]);

            $mailer->send($email);

            $this->addFlash(
                'success',
                'Compte créé. Un email de confirmation vous a été envoyé.'
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render(
            'registration/register.html.twig',
            [
                'registrationForm' => $form->createView(),
            ]
        );
    }
}