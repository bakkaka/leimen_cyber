<?php
// src/Entity/Prerequisite.php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['prerequisite:read']]),
        new GetCollection(normalizationContext: ['groups' => ['prerequisite:read']])
    ],
    normalizationContext: ['groups' => ['prerequisite:read']]
)]
class Prerequisite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['prerequisite:read', 'comment:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['prerequisite:read'])]
    private ?string $title = null;

    #[ORM\Column(length: 190, unique: true)]
    #[Groups(['prerequisite:read'])]
    private ?string $slug = null;

    #[ORM\Column(type: 'text')]
    #[Groups(['prerequisite:read'])]
    private ?string $content = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Groups(['prerequisite:read'])]
    private ?string $excerpt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['prerequisite:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['prerequisite:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isPublished = true;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'entityId')]
    private Collection $comments;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->isPublished = false;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function generateSlug(): void
    {
        if ($this->slug === null && $this->title !== null) {
            $this->slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $this->title), '-'));
        }
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // Getters / setters
    public function getId(): ?int { return $this->id; }
    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }
    public function getSlug(): ?string { return $this->slug; }
    public function setSlug(?string $slug): static { $this->slug = $slug; return $this; }
    public function getContent(): ?string { return $this->content; }
    public function setContent(string $content): static { $this->content = $content; return $this; }
    public function getExcerpt(): ?string { return $this->excerpt; }
    public function setExcerpt(?string $excerpt): static { $this->excerpt = $excerpt; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function isPublished(): bool { return $this->isPublished; }
    public function setIsPublished(bool $isPublished): static { $this->isPublished = $isPublished; return $this; }
    public function getComments(): Collection { return $this->comments; }
}