<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['quiz:read']]),
        new GetCollection(normalizationContext: ['groups' => ['quiz:read']])
    ],
    normalizationContext: ['groups' => ['quiz:read']]
)]
class Quiz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['quiz:read', 'question:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['quiz:read'])]
    private ?string $title = null;

    #[ORM\ManyToOne(targetEntity: Module::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['quiz:read'])]
    private ?Module $module = null;

    #[ORM\Column]
    #[Groups(['quiz:read'])]
    private int $passingScore = 70;

    #[ORM\Column]
    #[Groups(['quiz:read'])]
    private bool $isPublished = false;

    #[ORM\OneToMany(targetEntity: Question::class, mappedBy: 'quiz', cascade: ['persist', 'remove'])]
    #[Groups(['quiz:read'])]
    private Collection $questions;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }
    public function getModule(): ?Module { return $this->module; }
    public function setModule(?Module $module): static { $this->module = $module; return $this; }
    public function getPassingScore(): int { return $this->passingScore; }
    public function setPassingScore(int $passingScore): static { $this->passingScore = $passingScore; return $this; }
    public function isPublished(): bool { return $this->isPublished; }
    public function setIsPublished(bool $isPublished): static { $this->isPublished = $isPublished; return $this; }
    public function getQuestions(): Collection { return $this->questions; }
}