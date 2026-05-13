<?php
// src/Controller/Admin/ModuleAdminController.php

namespace App\Controller\Admin;

use App\Entity\Course;
use App\Entity\Module;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/modules')]
#[IsGranted('ROLE_ADMIN')]
class ModuleAdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_modules')]
    public function index(EntityManagerInterface $em): Response
    {
        $modules = $em->getRepository(Module::class)->findBy([], ['course' => 'ASC', 'orderNumber' => 'ASC']);
        
        return $this->render('admin/module/index.html.twig', [
            'modules' => $modules,
        ]);
    }
    
    #[Route('/new', name: 'app_admin_module_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $module = new Module();
        
        if ($request->isMethod('POST')) {
            $title = $request->request->get('title');
            $description = $request->request->get('description');
            $orderNumber = $request->request->get('order_number');
            $courseId = $request->request->get('course_id');
            
            $course = $em->getRepository(Course::class)->find($courseId);
            
            if ($course && $title) {
                $module->setTitle($title);
                $module->setDescription($description);
                $module->setOrderNumber($orderNumber ?: 0);
                $module->setCourse($course);
                $module->setCreatedAt(new \DateTimeImmutable());
                
                $em->persist($module);
                $em->flush();
                
                $this->addFlash('success', 'Module créé avec succès !');
                return $this->redirectToRoute('app_admin_modules');
            }
            
            $this->addFlash('error', 'Erreur lors de la création du module.');
        }
        
        $courses = $em->getRepository(Course::class)->findAll();
        
        return $this->render('admin/module/form.html.twig', [
            'module' => $module,
            'courses' => $courses,
            'title' => 'Créer un module',
        ]);
    }
    
    #[Route('/edit/{id}', name: 'app_admin_module_edit')]
    public function edit(Module $module, Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $title = $request->request->get('title');
            $description = $request->request->get('description');
            $orderNumber = $request->request->get('order_number');
            $courseId = $request->request->get('course_id');
            
            $course = $em->getRepository(Course::class)->find($courseId);
            
            if ($course && $title) {
                $module->setTitle($title);
                $module->setDescription($description);
                $module->setOrderNumber($orderNumber ?: 0);
                $module->setCourse($course);
                
                $em->flush();
                
                $this->addFlash('success', 'Module modifié avec succès !');
                return $this->redirectToRoute('app_admin_modules');
            }
            
            $this->addFlash('error', 'Erreur lors de la modification.');
        }
        
        $courses = $em->getRepository(Course::class)->findAll();
        
        return $this->render('admin/module/form.html.twig', [
            'module' => $module,
            'courses' => $courses,
            'title' => 'Modifier le module',
        ]);
    }
    
    #[Route('/delete/{id}', name: 'app_admin_module_delete', methods: ['POST'])]
    public function delete(Module $module, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $module->getId(), $request->request->get('_token'))) {
            $em->remove($module);
            $em->flush();
            $this->addFlash('success', 'Module supprimé avec succès !');
        }
        
        return $this->redirectToRoute('app_admin_modules');
    }
}