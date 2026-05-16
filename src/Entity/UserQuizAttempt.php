<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'user_quiz_attempt')]
#[ORM\UniqueConstraint(name: 'unique_user_quiz', columns: ['user_id', 'quiz_id'])]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['attempt:read']]),
        new GetCollection(normalizationContext: ['groups' => ['attempt:read']])
    ],
    normalizationContext: ['groups' => ['attempt:read']]
)]
class UserQuizAttempt
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['attempt:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['attempt:read'])]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Quiz::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['attempt:read'])]
    private ?Quiz $quiz = null;

    #[ORM\Column]
    #[Groups(['attempt:read'])]
    private int $score = 0;

    #[ORM\Column]
    #[Groups(['attempt:read'])]
    private bool $passed = false;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['attempt:read'])]
    private ?\DateTimeInterface $attemptedAt = null;

    public function __construct()
    {
        $this->attemptedAt = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }
    public function getQuiz(): ?Quiz { return $this->quiz; }
    public function setQuiz(?Quiz $quiz): static { $this->quiz = $quiz; return $this; }
    public function getScore(): int { return $this->score; }
    public function setScore(int $score): static { $this->score = $score; return $this; }
    public function isPassed(): bool { return $this->passed; }
    public function setPassed(bool $passed): static { $this->passed = $passed; return $this; }
    public function getAttemptedAt(): ?\DateTimeInterface { return $this->attemptedAt; }
    public function setAttemptedAt(\DateTimeInterface $attemptedAt): static { $this->attemptedAt = $attemptedAt; return $this; }
}