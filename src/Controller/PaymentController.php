<?php
// src/Controller/PaymentController.php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\Enrollment;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PaymentController extends AbstractController
{
    // ========== INSCRIPTION GRATUITE (pour test) ==========
    #[Route('/checkout/{id}', name: 'app_checkout')]
    #[IsGranted('ROLE_USER')]
    public function checkout(Course $course, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('warning', 'Veuillez vous connecter.');
            return $this->redirectToRoute('app_login');
        }
        
        // Vérifier si déjà inscrit
        $existing = $em->getRepository(Enrollment::class)->findOneBy([
            'student' => $user,
            'course' => $course
        ]);
        
        if ($existing) {
            $this->addFlash('info', 'Déjà inscrit.');
            return $this->redirectToRoute('app_course_learn', ['slug' => $course->getSlug()]);
        }
        
        // Créer l'inscription gratuite
        $enrollment = new Enrollment();
        $enrollment->setStudent($user);
        $enrollment->setCourse($course);
        $enrollment->setEnrolledAt(new \DateTimeImmutable());
        $enrollment->setProgress(0);
        $enrollment->setPaymentMethod('free');
        
        $em->persist($enrollment);
        $em->flush();
        
        $this->addFlash('success', 'Inscription réussie !');
        return $this->redirectToRoute('app_course_learn', ['slug' => $course->getSlug()]);
    }
    
    // ========== PAIEMENT STRIPE ==========
    #[Route('/stripe/checkout/{id}', name: 'app_stripe_checkout')]
    #[IsGranted('ROLE_USER')]
    public function stripeCheckout(Course $course, StripeService $stripeService, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        
        // Vérifier si déjà inscrit
        $existing = $em->getRepository(Enrollment::class)->findOneBy([
            'student' => $user,
            'course' => $course
        ]);
        
        if ($existing) {
            $this->addFlash('info', 'Vous êtes déjà inscrit à cette formation.');
            return $this->redirectToRoute('app_course_learn', ['slug' => $course->getSlug()]);
        }
        
        // Créer la session Stripe
        $lineItems = [[
            'price_data' => [
                'currency' => 'mad',
                'product_data' => [
                    'name' => $course->getTitle(),
                    'description' => $course->getShortDescription() ?: $course->getDescription(),
                ],
                'unit_amount' => $course->getPrice(), // prix en centimes
            ],
            'quantity' => 1,
        ]];
        
        $successUrl = $this->generateUrl('app_stripe_success', ['id' => $course->getId()], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);
        $cancelUrl = $this->generateUrl('app_stripe_cancel', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);
        
        $metadata = [
            'user_id' => $user->getId(),
            'course_id' => $course->getId(),
            'user_email' => $user->getEmail(),
        ];
        
        $session = $stripeService->createCheckoutSession($lineItems, $successUrl, $cancelUrl, $metadata);
        
        return $this->redirect($session->url);
    }
    
    #[Route('/stripe/success/{id}', name: 'app_stripe_success')]
    public function stripeSuccess(Course $course, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        
        // Vérifier si déjà inscrit
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
            $enrollment->setIsCompleted(false);
            
            $em->persist($enrollment);
            $em->flush();
        }
        
        $this->addFlash('success', '✅ Paiement réussi ! Vous êtes inscrit à la formation : ' . $course->getTitle());
        
        return $this->redirectToRoute('app_course_learn', ['slug' => $course->getSlug()]);
    }
    
    #[Route('/stripe/cancel', name: 'app_stripe_cancel')]
    public function stripeCancel(): Response
    {
        $this->addFlash('warning', '❌ Le paiement a été annulé.');
        return $this->redirectToRoute('app_course_index');
    }
}