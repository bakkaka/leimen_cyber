<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Course;
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
        
        // Récupérer tous les cours pour pouvoir en ajouter un nouveau
        $courses = $em->getRepository(Course::class)->findAll();

        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
            'enrollments' => $enrollments,
            'quizAttempts' => $quizAttempts,
            'moduleProgress' => $moduleProgress,
            'courses' => $courses,
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

    // ==========================================
    // ⭐ LOGIQUE : Donner / Retirer l'accès à UN cours
    // ==========================================

    #[Route('/user/{userId}/course/{courseId}/give-access', name: 'app_admin_enrollment_give_access', methods: ['POST'])]
    public function giveSingleAccess(int $userId, int $courseId, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('give-single-' . $userId . '-' . $courseId, $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('app_admin_user_show', ['id' => $userId]);
        }

        $user = $em->getRepository(User::class)->find($userId);
        $course = $em->getRepository(Course::class)->find($courseId);

        if (!$user || !$course) {
            $this->addFlash('error', 'Utilisateur ou cours non trouvé.');
            return $this->redirectToRoute('app_admin_user_show', ['id' => $userId]);
        }

        // Vérifier si une inscription existe déjà
        $enrollment = $em->getRepository(Enrollment::class)->findOneBy([
            'student' => $user,
            'course' => $course
        ]);

        if (!$enrollment) {
            // CRÉER une nouvelle inscription
            $enrollment = new Enrollment();
            $enrollment->setStudent($user);
            $enrollment->setCourse($course);
            $enrollment->setEnrolledAt(new \DateTimeImmutable());
            $enrollment->setProgress(0);
            $enrollment->setIsCompleted(false);
            $enrollment->setPaymentMethod('bank_transfer_validated');
            $em->persist($enrollment);
            $this->addFlash('success', sprintf('✅ Inscription créée et accès donné pour "%s" à %s.', $course->getTitle(), $user->getEmail()));
        } else {
            // Si existe déjà, on met juste à jour
            $enrollment->setPaymentMethod('bank_transfer_validated');
            $this->addFlash('success', sprintf('✅ Accès donné pour "%s" à %s.', $course->getTitle(), $user->getEmail()));
        }

        $em->flush();
        return $this->redirectToRoute('app_admin_user_show', ['id' => $userId]);
    }

    #[Route('/user/{userId}/course/{courseId}/remove-access', name: 'app_admin_enrollment_remove_access', methods: ['POST'])]
    public function removeSingleAccess(int $userId, int $courseId, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('remove-single-' . $userId . '-' . $courseId, $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('app_admin_user_show', ['id' => $userId]);
        }

        $user = $em->getRepository(User::class)->find($userId);
        $course = $em->getRepository(Course::class)->find($courseId);

        if (!$user || !$course) {
            $this->addFlash('error', 'Utilisateur ou cours non trouvé.');
            return $this->redirectToRoute('app_admin_user_show', ['id' => $userId]);
        }

        $enrollment = $em->getRepository(Enrollment::class)->findOneBy([
            'student' => $user,
            'course' => $course
        ]);

        if ($enrollment && $enrollment->getPaymentMethod() === 'bank_transfer_validated') {
            // Supprimer ou passer à un statut révoqué
            $em->remove($enrollment);
            $this->addFlash('danger', sprintf('🔒 Accès retiré pour "%s" à %s.', $course->getTitle(), $user->getEmail()));
        } else {
            $this->addFlash('warning', 'Aucun accès actif à retirer.');
        }

        $em->flush();
        return $this->redirectToRoute('app_admin_user_show', ['id' => $userId]);
    }

    // ==========================================
    // ⭐ BOUTONS GLOBAUX : Donner/Retirer l'accès à TOUS les cours
    // ==========================================

    #[Route('/{id}/give-access-all', name: 'app_admin_user_give_access_all', methods: ['POST'])]
    public function giveAccessAll(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('give-access-all' . $user->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('app_admin_users');
        }

        $courses = $em->getRepository(Course::class)->findAll();
        $count = 0;

        foreach ($courses as $course) {
            $enrollment = $em->getRepository(Enrollment::class)->findOneBy([
                'student' => $user,
                'course' => $course
            ]);

            if (!$enrollment) {
                $enrollment = new Enrollment();
                $enrollment->setStudent($user);
                $enrollment->setCourse($course);
                $enrollment->setEnrolledAt(new \DateTimeImmutable());
                $enrollment->setProgress(0);
                $enrollment->setIsCompleted(false);
                $enrollment->setPaymentMethod('bank_transfer_validated');
                $em->persist($enrollment);
                $count++;
            } elseif ($enrollment->getPaymentMethod() !== 'bank_transfer_validated') {
                $enrollment->setPaymentMethod('bank_transfer_validated');
                $count++;
            }
        }

        $em->flush();
        $this->addFlash('success', sprintf('✅ Accès donné à %d formation(s) pour %s.', $count, $user->getEmail()));
        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/{id}/remove-access-all', name: 'app_admin_user_remove_access_all', methods: ['POST'])]
    public function removeAccessAll(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('remove-access-all' . $user->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('app_admin_users');
        }

        $enrollments = $em->getRepository(Enrollment::class)->findBy([
            'student' => $user,
            'paymentMethod' => 'bank_transfer_validated'
        ]);

        $count = 0;
        foreach ($enrollments as $enrollment) {
            $em->remove($enrollment);
            $count++;
        }

        $em->flush();
        $this->addFlash('danger', sprintf('🔒 Accès retiré pour %d formation(s) à %s.', $count, $user->getEmail()));
        return $this->redirectToRoute('app_admin_users');
    }
}