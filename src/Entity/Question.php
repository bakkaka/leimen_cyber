<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['question:read']]),
        new GetCollection(normalizationContext: ['groups' => ['question:read']])
    ],
    normalizationContext: ['groups' => ['question:read']]
)]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['question:read', 'quiz:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Quiz::class, inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['question:read'])]
    private ?Quiz $quiz = null;

    #[ORM\Column(type: 'text')]
    #[Groups(['question:read'])]
    private ?string $text = null;

    #[ORM\Column(length: 20)]
    #[Groups(['question:read'])]
    private string $type = 'multiple_choice';

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['question:read'])]
    private ?array $options = null;

    #[ORM\Column(length: 255)]
    #[Groups(['question:read'])]
    private ?string $correctAnswer = null;

    public function getId(): ?int { return $this->id; }
    public function getQuiz(): ?Quiz { return $this->quiz; }
    public function setQuiz(?Quiz $quiz): static { $this->quiz = $quiz; return $this; }
    public function getText(): ?string { return $this->text; }
    public function setText(string $text): static { $this->text = $text; return $this; }
    public function getType(): string { return $this->type; }
    public function setType(string $type): static { $this->type = $type; return $this; }
    public function getOptions(): ?array { return $this->options; }
    public function setOptions(?array $options): static { $this->options = $options; return $this; }
    public function getCorrectAnswer(): ?string { return $this->correctAnswer; }
    public function setCorrectAnswer(string $correctAnswer): static { $this->correctAnswer = $correctAnswer; return $this; }
}