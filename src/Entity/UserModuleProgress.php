<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'user_module_progress')]
#[ORM\UniqueConstraint(name: 'unique_user_module', columns: ['user_id', 'module_id'])]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['user_module_progress:read']]),
        new GetCollection(normalizationContext: ['groups' => ['user_module_progress:read']])
    ],
    normalizationContext: ['groups' => ['user_module_progress:read']],
    security: "is_granted('ROLE_USER') and object.getUser() == user"
)]
class UserModuleProgress
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user_module_progress:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['user_module_progress:read'])]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Module::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['user_module_progress:read'])]
    private ?Module $module = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['user_module_progress:read'])]
    private bool $completed = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['user_module_progress:read'])]
    private ?\DateTimeInterface $completedAt = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['user_module_progress:read'])]
    private bool $quizPassed = false;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['user_module_progress:read'])]
    private ?int $quizScore = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getModule(): ?Module
    {
        return $this->module;
    }

    public function setModule(?Module $module): static
    {
        $this->module = $module;
        return $this;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function setCompleted(bool $completed): static
    {
        $this->completed = $completed;
        if ($completed && $this->completedAt === null) {
            $this->completedAt = new \DateTimeImmutable();
        }
        return $this;
    }

    public function getCompletedAt(): ?\DateTimeInterface
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeInterface $completedAt): static
    {
        $this->completedAt = $completedAt;
        return $this;
    }

    public function isQuizPassed(): bool
    {
        return $this->quizPassed;
    }

    public function setQuizPassed(bool $quizPassed): static
    {
        $this->quizPassed = $quizPassed;
        return $this;
    }

    public function getQuizScore(): ?int
    {
        return $this->quizScore;
    }

    public function setQuizScore(?int $quizScore): static
    {
        $this->quizScore = $quizScore;
        return $this;
    }
}