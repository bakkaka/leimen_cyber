<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\Enrollment;
use App\Service\StripeService;
use App\Service\PayPalService;
use App\Service\BrevoApiMailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;

class PaymentController extends AbstractController
{
    // ========== INSCRIPTION GRATUITE ==========
    #[Route('/checkout/{id}', name: 'app_checkout')]
    #[IsGranted('ROLE_USER')]
    public function checkout(
        Course $course,
        EntityManagerInterface $em,
        BrevoApiMailerService $brevoApiMailerService
    ): Response {
        $user = $this->getUser();

        $existing = $em->getRepository(Enrollment::class)->findOneBy([
            'student' => $user,
            'course' => $course
        ]);

        if ($existing) {
            $this->addFlash('info', 'Déjà inscrit.');
            return $this->redirectToRoute('app_course_learn', ['slug' => $course->getSlug()]);
        }

        $enrollment = new Enrollment();
        $enrollment->setStudent($user);
        $enrollment->setCourse($course);
        $enrollment->setEnrolledAt(new \DateTimeImmutable());
        $enrollment->setProgress(0);
        $enrollment->setPaymentMethod('free');

        $em->persist($enrollment);
        $em->flush();

        // ✅ Email d'inscription gratuite
        $htmlContent = $this->renderView('emails/course_enrollment.html.twig', [
            'user' => $user,
            'course' => $course
        ]);

        $brevoApiMailerService->sendEmail(
            $user->getEmail(),
            'Inscription à une formation',
            $htmlContent
        );

        $this->addFlash('success', 'Inscription réussie.');
        return $this->redirectToRoute('app_course_learn', ['slug' => $course->getSlug()]);
    }

    // ========== STRIPE ==========
    #[Route('/stripe/checkout/{id}', name: 'app_stripe_checkout')]
    #[IsGranted('ROLE_USER')]
    public function stripeCheckout(Course $course, StripeService $stripeService, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        $existing = $em->getRepository(Enrollment::class)->findOneBy([
            'student' => $user,
            'course' => $course
        ]);

        if ($existing) {
            $this->addFlash('info', 'Vous êtes déjà inscrit.');
            return $this->redirectToRoute('app_course_learn', ['slug' => $course->getSlug()]);
        }

        $lineItems = [[
            'price_data' => [
                'currency' => 'mad',
                'product_data' => ['name' => $course->getTitle()],
                'unit_amount' => $course->getPrice(),
            ],
            'quantity' => 1,
        ]];

        $successUrl = $this->generateUrl('app_stripe_success', ['id' => $course->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $cancelUrl = $this->generateUrl('app_stripe_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $session = $stripeService->createCheckoutSession($lineItems, $successUrl, $cancelUrl);
        return $this->redirect($session->url);
    }

    #[Route('/stripe/success/{id}', name: 'app_stripe_success')]
    public function stripeSuccess(
        Course $course,
        EntityManagerInterface $em,
        BrevoApiMailerService $brevoApiMailerService
    ): Response {
        $user = $this->getUser();

        $existing = $em->getRepository(Enrollment::class)->findOneBy([
            'student' => $user,
            'course' => $course
        ]);

        if (!$existing) {
            $enrollment = new Enrollment();
            $enrollment->setStudent($user);
            $enrollment->setCourse($course);
            $enrollment->setEnrolledAt(new \DateTimeImmutable());
            $enrollment->setProgress(0);
            $enrollment->setPaymentMethod('stripe');
            $em->persist($enrollment);
            $em->flush();

            try {
                $htmlContent = $this->renderView('emails/course_enrollment.html.twig', [
                    'user' => $user,
                    'course' => $course
                ]);

                $brevoApiMailerService->sendEmail(
                    $user->getEmail(),
                    '✅ Confirmation de paiement - Formation validée',
                    $htmlContent
                );

                $this->addFlash('success', '✅ Paiement réussi et email envoyé !');
            } catch (\Exception $e) {
                $this->addFlash('danger', '⚠️ Paiement réussi mais ERREUR EMAIL : ' . $e->getMessage());
            }
        } else {
            $this->addFlash('info', '⚠️ Déjà inscrit, aucun email envoyé.');
        }

        return $this->redirectToRoute('app_course_learn', ['slug' => $course->getSlug()]);
    }

    #[Route('/stripe/cancel', name: 'app_stripe_cancel')]
    public function stripeCancel(): Response
    {
        $this->addFlash('warning', '❌ Paiement annulé.');
        return $this->redirectToRoute('app_course_index');
    }

    // ========== PAYPAL ==========
    #[Route('/paypal/checkout/{id}', name: 'app_paypal_checkout')]
    #[IsGranted('ROLE_USER')]
    public function paypalCheckout(Course $course, PayPalService $payPalService): Response
    {
        $amount = $course->getPrice() / 100;
        $successUrl = $this->generateUrl('app_paypal_success', ['id' => $course->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $cancelUrl = $this->generateUrl('app_paypal_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $order = $payPalService->createOrder($amount, 'USD', $successUrl, $cancelUrl);

        foreach ($order['links'] as $link) {
            if ($link['rel'] === 'approve') {
                return $this->redirect($link['href']);
            }
        }

        $this->addFlash('error', 'Erreur lors de la création de la commande PayPal.');
        return $this->redirectToRoute('app_course_show', ['slug' => $course->getSlug()]);
    }

    #[Route('/paypal/success/{id}', name: 'app_paypal_success')]
    public function paypalSuccess(
        Course $course,
        Request $request,
        EntityManagerInterface $em,
        BrevoApiMailerService $brevoApiMailerService
    ): Response {
        $user = $this->getUser();
        $token = $request->query->get('token');

        if (!$token) {
            $this->addFlash('error', 'Token PayPal manquant.');
            return $this->redirectToRoute('app_course_show', ['slug' => $course->getSlug()]);
        }

        $existing = $em->getRepository(Enrollment::class)->findOneBy([
            'student' => $user,
            'course' => $course
        ]);

        if (!$existing) {
            $enrollment = new Enrollment();
            $enrollment->setStudent($user);
            $enrollment->setCourse($course);
            $enrollment->setEnrolledAt(new \DateTimeImmutable());
            $enrollment->setProgress(0);
            $enrollment->setPaymentMethod('paypal');
            $em->persist($enrollment);
            $em->flush();

            $htmlContent = $this->renderView('emails/course_enrollment.html.twig', [
                'user' => $user,
                'course' => $course
            ]);

            $brevoApiMailerService->sendEmail(
                $user->getEmail(),
                '✅ Confirmation de paiement PayPal - Formation validée',
                $htmlContent
            );
        }

        $this->addFlash('success', '✅ Paiement PayPal réussi !');
        return $this->redirectToRoute('app_course_learn', ['slug' => $course->getSlug()]);
    }

    #[Route('/paypal/cancel', name: 'app_paypal_cancel')]
    public function paypalCancel(): Response
    {
        $this->addFlash('warning', '❌ Paiement PayPal annulé.');
        return $this->redirectToRoute('app_course_index');
    }

    // ========== VIREMENT BANCAIRE ==========
    #[Route('/bank-transfer/{id}', name: 'app_bank_transfer')]
    #[IsGranted('ROLE_USER')]
    public function bankTransfer(
        Course $course,
        EntityManagerInterface $em,
        BrevoApiMailerService $brevoApiMailerService
    ): Response {
        $user = $this->getUser();

        $existing = $em->getRepository(Enrollment::class)->findOneBy([
            'student' => $user,
            'course' => $course
        ]);

        if ($existing) {
            $this->addFlash('info', 'Vous êtes déjà inscrit.');
            return $this->redirectToRoute('app_course_learn', ['slug' => $course->getSlug()]);
        }

        $enrollment = new Enrollment();
        $enrollment->setStudent($user);
        $enrollment->setCourse($course);
        $enrollment->setEnrolledAt(new \DateTimeImmutable());
        $enrollment->setProgress(0);
        $enrollment->setPaymentMethod('pending_bank');
        $enrollment->setIsCompleted(false);
        $em->persist($enrollment);
        $em->flush();

        // Admin
        $adminHtml = $this->renderView('emails/course_enrollment.html.twig', [
            'user' => $user,
            'course' => $course
        ]);
        $brevoApiMailerService->sendEmail(
            'admin@cyberleimen.com',
            'Nouvelle demande d’inscription par virement',
            $adminHtml
        );

        // Utilisateur
        $userHtml = $this->renderView('emails/bank_transfer_instructions.html.twig', [
            'user' => $user,
            'course' => $course,
            'bankAccounts' => $this->getBankAccounts()
        ]);
        $brevoApiMailerService->sendEmail(
            $user->getEmail(),
            'Instructions de paiement par virement',
            $userHtml
        );

        $this->addFlash('success', 'Votre demande a été enregistrée. Vous allez recevoir un email avec les coordonnées bancaires.');
        return $this->redirectToRoute('app_course_show', ['slug' => $course->getSlug()]);
    }

    private function getBankAccounts(): array
    {
        return [
            'saham' => [
                'name' => 'SAHAM BANK',
                'rib' => '022450000343002731459253',
                'swift' => 'SGMBMAMCSFS',
                'account_name' => 'Cyber Formation Maroc'
            ]
        ];
    }
}