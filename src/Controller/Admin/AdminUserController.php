<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Enrollment;
use App\Entity\UserQuizAttempt;
use App\Entity\UserModuleProgress;
use App\Form\UserAdminType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
class AdminUserController extends AbstractController
{
    #[Route('/', name: 'app_admin_users')]
    public function index(EntityManagerInterface $em): Response
    {
        $users = $em->getRepository(User::class)->findBy([], ['createdAt' => 'DESC']);
        return $this->render('admin/user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/new', name: 'app_admin_user_new')]
    public function new(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserAdminType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            }
            
            $role = $form->get('roles')->getData();
            if ($role === 'ROLE_ADMIN') {
                $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
            } else {
                $user->setRoles(['ROLE_USER']);
            }
            
            $user->setIsVerified(true);
            $user->setIsActive(true);
            $user->setCreatedAt(new \DateTimeImmutable());
            
            $em->persist($user);
            $em->flush();
            
            $this->addFlash('success', 'Utilisateur créé avec succès.');
            return $this->redirectToRoute('app_admin_users');
        }
        
        return $this->render('admin/user/new.html.twig', [
            'form' => $form->createView(),
            'title' => 'Créer un utilisateur',
        ]);
    }

    #[Route('/{id}', name: 'app_admin_user_show')]
    public function show(int $id, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }
        
        $enrollments = $em->getRepository(Enrollment::class)->findBy(['student' => $user]);
        $quizAttempts = $em->getRepository(UserQuizAttempt::class)->findBy(['user' => $user], ['attemptedAt' => 'DESC']);
        $moduleProgress = $em->getRepository(UserModuleProgress::class)->findBy(['user' => $user]);

        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
            'enrollments' => $enrollments,
            'quizAttempts' => $quizAttempts,
            'moduleProgress' => $moduleProgress,
        ]);
    }

    #[Route('/{id}/toggle-status', name: 'app_admin_user_toggle_status', methods: ['POST'])]
    public function toggleStatus(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('toggle' . $user->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('app_admin_users');
        }

        $user->setIsActive(!$user->isActive());
        $em->flush();

        $status = $user->isActive() ? 'activé' : 'désactivé';
        $this->addFlash('success', "Utilisateur {$status} avec succès.");
        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/{id}/make-admin', name: 'app_admin_user_make_admin', methods: ['POST'])]
    public function makeAdmin(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('make-admin' . $user->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('app_admin_users');
        }

        $roles = $user->getRoles();
        if (!in_array('ROLE_ADMIN', $roles)) {
            $roles[] = 'ROLE_ADMIN';
            $user->setRoles($roles);
            $em->flush();
            $this->addFlash('success', "Rôle ADMIN ajouté à {$user->getEmail()}.");
        } else {
            $this->addFlash('warning', "L'utilisateur est déjà administrateur.");
        }
        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/{id}/remove-admin', name: 'app_admin_user_remove_admin', methods: ['POST'])]
    public function removeAdmin(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('remove-admin' . $user->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('app_admin_users');
        }

        $roles = $user->getRoles();
        if (($key = array_search('ROLE_ADMIN', $roles)) !== false) {
            unset($roles[$key]);
            $user->setRoles(array_values($roles));
            $em->flush();
            $this->addFlash('success', "Rôle ADMIN retiré à {$user->getEmail()}.");
        } else {
            $this->addFlash('warning', "L'utilisateur n'est pas administrateur.");
        }
        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/enrollment/{id}/validate', name: 'app_admin_enrollment_validate', methods: ['POST'])]
    public function validateEnrollment(Enrollment $enrollment, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('validate' . $enrollment->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('app_admin_users');
        }

        if ($enrollment->getPaymentMethod() === 'pending_bank') {
            $enrollment->setPaymentMethod('bank_transfer_validated');
            $enrollment->setIsCompleted(false);
            $em->flush();

            $this->addFlash('success', 'Paiement validé. L\'utilisateur peut maintenant accéder à la formation.');
        } else {
            $this->addFlash('warning', 'Cette inscription n\'est pas en attente de virement.');
        }

        return $this->redirectToRoute('app_admin_user_show', ['id' => $enrollment->getStudent()->getId()]);
    }
}