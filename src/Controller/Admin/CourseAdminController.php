<?php

namespace App\Controller\Admin;

use App\Entity\Course;
use App\Form\CourseType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/courses')]
#[IsGranted('ROLE_ADMIN')]
class CourseAdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_courses')]
    public function index(EntityManagerInterface $em): Response
    {
        $courses = $em->getRepository(Course::class)->findBy([], ['createdAt' => 'DESC']);
        
        return $this->render('admin/course/index.html.twig', [
            'courses' => $courses,
        ]);
    }
    
    #[Route('/new', name: 'app_admin_course_new')]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $course = new Course();
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Conversion des champs texte en JSON
            $this->convertTextareaToJson($form, $course);
            
            $slug = $slugger->slug($course->getTitle())->lower();
            $course->setSlug($slug);
            $course->setCreatedAt(new \DateTimeImmutable());
            
            $em->persist($course);
            $em->flush();
            
            $this->addFlash('success', 'Cours créé avec succès !');
            return $this->redirectToRoute('app_admin_courses');
        }
        
        return $this->render('admin/course/form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Créer un cours',
        ]);
    }
    
    #[Route('/edit/{id}', name: 'app_admin_course_edit')]
    public function edit(Course $course, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $this->convertTextareaToJson($form, $course);
            
            $slug = $slugger->slug($course->getTitle())->lower();
            $course->setSlug($slug);
            
            $em->flush();
            
            $this->addFlash('success', 'Cours modifié avec succès !');
            return $this->redirectToRoute('app_admin_courses');
        }
        
        return $this->render('admin/course/form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modifier le cours',
        ]);
    }
    
    #[Route('/delete/{id}', name: 'app_admin_course_delete', methods: ['POST'])]
    public function delete(Course $course, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $course->getId(), $request->request->get('_token'))) {
            $em->remove($course);
            $em->flush();
            $this->addFlash('success', 'Cours supprimé avec succès !');
        }
        
        return $this->redirectToRoute('app_admin_courses');
    }
    
    private function convertTextareaToJson($form, Course $course): void
    {
        if ($form->has('whatYouWillLearn') && $form->get('whatYouWillLearn')->getData()) {
            $lines = explode("\n", $form->get('whatYouWillLearn')->getData());
            $course->setWhatYouWillLearn(array_filter(array_map('trim', $lines)));
        }
        
        if ($form->has('requirements') && $form->get('requirements')->getData()) {
            $lines = explode("\n", $form->get('requirements')->getData());
            $course->setRequirements(array_filter(array_map('trim', $lines)));
        }
        
        if ($form->has('targetAudience') && $form->get('targetAudience')->getData()) {
            $lines = explode("\n", $form->get('targetAudience')->getData());
            $course->setTargetAudience(array_filter(array_map('trim', $lines)));
        }
    }
}