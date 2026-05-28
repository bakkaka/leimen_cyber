<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\Enrollment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use App\Service\StripeService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaymentController extends AbstractController
{
    // ========== INSCRIPTION GRATUITE (test) ==========
    #[Route('/checkout/{id}', name: 'app_checkout')]
    #[IsGranted('ROLE_USER')]
    public function checkout(
        Course $course,
        EntityManagerInterface $em,
        MailerInterface $mailer
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

        // EMAIL INSCRIPTION FORMATION
        $email = (new TemplatedEmail())
            ->from('admin@cybersec.local')
            ->to($user->getEmail())
            ->subject('Inscription à une formation')
            ->htmlTemplate('emails/course_enrollment.html.twig')
            ->context([
                'user' => $user,
                'course' => $course
            ]);

        $mailer->send($email);

        $this->addFlash('success', 'Inscription réussie.');
        return $this->redirectToRoute('app_course_learn', ['slug' => $course->getSlug()]);
    }

    // ========== PAIEMENT STRIPE ==========
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
    public function stripeSuccess(Course $course, EntityManagerInterface $em): Response
    {
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
        }

        $this->addFlash('success', '✅ Paiement réussi !');
        return $this->redirectToRoute('app_course_learn', ['slug' => $course->getSlug()]);
    }

    #[Route('/stripe/cancel', name: 'app_stripe_cancel')]
    public function stripeCancel(): Response
    {
        $this->addFlash('warning', '❌ Paiement annulé.');
        return $this->redirectToRoute('app_course_index');
    }

    // ========== PAIEMENT PAR VIREMENT BANCAIRE ==========
    #[Route('/bank-transfer/{id}', name: 'app_bank_transfer')]
    #[IsGranted('ROLE_USER')]
    public function bankTransfer(Course $course, EntityManagerInterface $em, MailerInterface $mailer): Response
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

        // Créer une inscription en attente
        $enrollment = new Enrollment();
        $enrollment->setStudent($user);
        $enrollment->setCourse($course);
        $enrollment->setEnrolledAt(new \DateTimeImmutable());
        $enrollment->setProgress(0);
        $enrollment->setPaymentMethod('pending_bank');
        $enrollment->setIsCompleted(false);
        $em->persist($enrollment);
        $em->flush();

        // Email à l’admin
        $adminEmail = (new TemplatedEmail())
            ->from('admin@cybersec.local')
            ->to('abdoubakka@gmail.com')
            ->subject('Nouvelle demande d’inscription par virement')
            ->htmlTemplate('emails/bank_transfer_request.html.twig')
            ->context([
                'user' => $user,
                'course' => $course,
                'enrollment' => $enrollment
            ]);
        $mailer->send($adminEmail);

        // Email à l’utilisateur avec les coordonnées bancaires
        $userEmail = (new TemplatedEmail())
            ->from('admin@cybersec.local')
            ->to($user->getEmail())
            ->subject('Instructions de paiement par virement')
            ->htmlTemplate('emails/bank_transfer_instructions.html.twig')
            ->context([
                'user' => $user,
                'course' => $course,
                'bankAccounts' => $this->getBankAccounts()
            ]);
        $mailer->send($userEmail);

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