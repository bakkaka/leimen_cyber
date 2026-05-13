<?php
// src/Controller/Admin/LessonAdminController.php

namespace App\Controller\Admin;

use App\Entity\Lesson;
use App\Entity\Module;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/lessons')]
#[IsGranted('ROLE_ADMIN')]
class LessonAdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_lessons')]
    public function index(EntityManagerInterface $em): Response
    {
        $lessons = $em->getRepository(Lesson::class)->findBy([], ['module' => 'ASC', 'orderNumber' => 'ASC']);
        
        return $this->render('admin/lesson/index.html.twig', [
            'lessons' => $lessons,
        ]);
    }
    
    #[Route('/new', name: 'app_admin_lesson_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $lesson = new Lesson();
        
        if ($request->isMethod('POST')) {
            $title = $request->request->get('title');
            $content = $request->request->get('content');
            $videoUrl = $request->request->get('video_url');
            $duration = $request->request->get('duration');
            $orderNumber = $request->request->get('order_number');
            $moduleId = $request->request->get('module_id');
            
            $module = $em->getRepository(Module::class)->find($moduleId);
            
            if ($module && $title) {
                $lesson->setTitle($title);
                $lesson->setContent($content);
                $lesson->setVideoUrl($videoUrl);
                $lesson->setVideoPlatform('youtube');
                $lesson->setDuration($duration ?: 0);
                $lesson->setOrderNumber($orderNumber ?: 0);
                $lesson->setModule($module);
                $lesson->setCreatedAt(new \DateTimeImmutable());
                
                $em->persist($lesson);
                $em->flush();
                
                $this->addFlash('success', 'Leçon créée avec succès !');
                return $this->redirectToRoute('app_admin_lessons');
            }
            
            $this->addFlash('error', 'Erreur lors de la création de la leçon.');
        }
        
        $modules = $em->getRepository(Module::class)->findAll();
        
        return $this->render('admin/lesson/form.html.twig', [
            'lesson' => $lesson,
            'modules' => $modules,
            'title' => 'Créer une leçon',
        ]);
    }
    
    #[Route('/edit/{id}', name: 'app_admin_lesson_edit')]
    public function edit(Lesson $lesson, Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $title = $request->request->get('title');
            $content = $request->request->get('content');
            $videoUrl = $request->request->get('video_url');
            $duration = $request->request->get('duration');
            $orderNumber = $request->request->get('order_number');
            $moduleId = $request->request->get('module_id');
            
            $module = $em->getRepository(Module::class)->find($moduleId);
            
            if ($module && $title) {
                $lesson->setTitle($title);
                $lesson->setContent($content);
                $lesson->setVideoUrl($videoUrl);
                $lesson->setDuration($duration ?: 0);
                $lesson->setOrderNumber($orderNumber ?: 0);
                $lesson->setModule($module);
                
                $em->flush();
                
                $this->addFlash('success', 'Leçon modifiée avec succès !');
                return $this->redirectToRoute('app_admin_lessons');
            }
            
            $this->addFlash('error', 'Erreur lors de la modification.');
        }
        
        $modules = $em->getRepository(Module::class)->findAll();
        
        return $this->render('admin/lesson/form.html.twig', [
            'lesson' => $lesson,
            'modules' => $modules,
            'title' => 'Modifier la leçon',
        ]);
    }
    
    #[Route('/delete/{id}', name: 'app_admin_lesson_delete', methods: ['POST'])]
    public function delete(Lesson $lesson, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $lesson->getId(), $request->request->get('_token'))) {
            $em->remove($lesson);
            $em->flush();
            $this->addFlash('success', 'Leçon supprimée avec succès !');
        }
        
        return $this->redirectToRoute('app_admin_lessons');
    }
}