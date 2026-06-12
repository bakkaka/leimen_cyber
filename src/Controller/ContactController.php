<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Service\BrevoApiMailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        BrevoApiMailerService $brevoApiMailerService
    ): Response {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarder en base
            $contact->setCreatedAt(new \DateTimeImmutable());
            $em->persist($contact);
            $em->flush();

            // ✅ Envoyer l'email via Brevo (au lieu de MailerInterface)
            $htmlContent = '
                <h1>Message de ' . htmlspecialchars($contact->getName()) . '</h1>
                <p><strong>Email :</strong> ' . htmlspecialchars($contact->getEmail()) . '</p>
                <p><strong>Message :</strong><br>' . nl2br(htmlspecialchars($contact->getMessage())) . '</p>
            ';

            $brevoApiMailerService->sendEmail(
                'admin@cyberleimen.com',  // Destinataire : admin du site
                'Nouveau message de contact - Cyber Formation Maroc',
                $htmlContent
            );

            $this->addFlash('success', 'Votre message a été envoyé avec succès !');
            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}