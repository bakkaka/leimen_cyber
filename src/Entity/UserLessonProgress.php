<?php
// src/Entity/UserLessonProgress.php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'user_lesson_progress')]
#[ORM\UniqueConstraint(name: 'unique_user_lesson', columns: ['student_id', 'lesson_id'])]
#[ApiResource(
    normalizationContext: ['groups' => ['progress:read']],
    security: "is_granted('ROLE_USER') and object.student == user"
)]
class UserLessonProgress
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['progress:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'lessonProgresses')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['progress:read'])]
    private ?User $student = null;

    #[ORM\ManyToOne(targetEntity: Lesson::class, inversedBy: 'progresses')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['progress:read', 'lesson:read'])]
    private ?Lesson $lesson = null;

    #[ORM\Column]
    #[Groups(['progress:read'])]
    private bool $completed = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['progress:read'])]
    private ?\DateTime $completedAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['progress:read'])]
    private ?int $videoPosition = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['progress:read'])]
    private ?\DateTime $lastWatchedAt = null;

    // Getters et Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStudent(): ?User
    {
        return $this->student;
    }

    public function setStudent(?User $student): static
    {
        $this->student = $student;
        return $this;
    }

    public function getLesson(): ?Lesson
    {
        return $this->lesson;
    }

    public function setLesson(?Lesson $lesson): static
    {
        $this->lesson = $lesson;
        return $this;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function setCompleted(bool $completed): static
    {
        $this->completed = $completed;
        if ($completed) {
            $this->completedAt = new \DateTime();
        }
        return $this;
    }

    public function getCompletedAt(): ?\DateTime
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTime $completedAt): static
    {
        $this->completedAt = $completedAt;
        return $this;
    }

    public function getVideoPosition(): ?int
    {
        return $this->videoPosition;
    }

    public function setVideoPosition(?int $videoPosition): static
    {
        $this->videoPosition = $videoPosition;
        return $this;
    }

    public function getLastWatchedAt(): ?\DateTime
    {
        return $this->lastWatchedAt;
    }

    public function setLastWatchedAt(?\DateTime $lastWatchedAt): static
    {
        $this->lastWatchedAt = $lastWatchedAt;
        return $this;
    }
}