<?php
// src/Entity/Comment.php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['comment:read']]),
        new GetCollection(normalizationContext: ['groups' => ['comment:read']])
    ],
    normalizationContext: ['groups' => ['comment:read']]
)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['comment:read', 'lesson:read', 'blog:read', 'prerequisite:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    #[Groups(['comment:read'])]
    private ?string $content = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['comment:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['comment:read'])]
    private ?User $author = null;

    #[ORM\Column]
    #[Groups(['comment:read'])]
    private ?int $entityId = null;

    #[ORM\Column(length: 50)]
    #[Groups(['comment:read'])]
    private ?string $entityType = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isApproved = false;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getContent(): ?string { return $this->content; }
    public function setContent(string $content): static { $this->content = $content; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getAuthor(): ?User { return $this->author; }
    public function setAuthor(?User $author): static { $this->author = $author; return $this; }
    public function getEntityId(): ?int { return $this->entityId; }
    public function setEntityId(int $entityId): static { $this->entityId = $entityId; return $this; }
    public function getEntityType(): ?string { return $this->entityType; }
    public function setEntityType(string $entityType): static { $this->entityType = $entityType; return $this; }
    public function isApproved(): bool { return $this->isApproved; }
    public function setIsApproved(bool $isApproved): static { $this->isApproved = $isApproved; return $this; }
}