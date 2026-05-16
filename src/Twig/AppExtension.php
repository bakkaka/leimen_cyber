<?php
// src/Twig/AppExtension.php

namespace App\Twig;

use App\Entity\Course;
use App\Entity\User;
use App\Repository\UserLessonProgressRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private UserLessonProgressRepository $progressRepository;

    public function __construct(UserLessonProgressRepository $progressRepository)
    {
        $this->progressRepository = $progressRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getCompletedLessons', [$this, 'getCompletedLessons']),
        ];
    }

    public function getCompletedLessons(User $user, Course $course): array
    {
        $progresses = $this->progressRepository->findBy([
            'student' => $user,
        ]);

        $completedLessons = [];
        foreach ($progresses as $progress) {
            $lesson = $progress->getLesson();
            if ($progress->isCompleted() && $lesson && $lesson->getModule()->getCourse()->getId() === $course->getId()) {
                $completedLessons[] = $lesson;
            }
        }

        return $completedLessons;
    }
}