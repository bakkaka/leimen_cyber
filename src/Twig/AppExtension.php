<?php

namespace App\Twig;

use App\Entity\Module;
use App\Entity\User;
use App\Entity\UserModuleProgress;
use App\Repository\UserLessonProgressRepository;
use App\Repository\UserModuleProgressRepository;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private EntityManagerInterface $entityManager;
    private UserLessonProgressRepository $progressRepository;

    public function __construct(EntityManagerInterface $entityManager, UserLessonProgressRepository $progressRepository)
    {
        $this->entityManager = $entityManager;
        $this->progressRepository = $progressRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getCompletedLessons', [$this, 'getCompletedLessons']),
            new TwigFunction('getUserModuleProgress', [$this, 'getUserModuleProgress']),
        ];
    }

    public function getCompletedLessons(User $user, \App\Entity\Course $course): array
    {
        $progresses = $this->progressRepository->findBy(['student' => $user]);
        $completed = [];
        foreach ($progresses as $progress) {
            $lesson = $progress->getLesson();
            if ($progress->isCompleted() && $lesson && $lesson->getModule()->getCourse()->getId() === $course->getId()) {
                $completed[] = $lesson;
            }
        }
        return $completed;
    }

    public function getUserModuleProgress(User $user, Module $module): ?UserModuleProgress
    {
        return $this->entityManager->getRepository(UserModuleProgress::class)->findOneBy([
            'user' => $user,
            'module' => $module
        ]);
    }
}