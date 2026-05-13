<?php
// src/Entity/Lesson.php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'lesson')]
#[ApiResource(
    normalizationContext: ['groups' => ['lesson:read']]
)]
class Lesson
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['lesson:read', 'course:read', 'module:read', 'progress:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'lessons')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['lesson:read'])]
    private ?Module $module = null;

    #[ORM\Column(length: 200)]
    #[Groups(['lesson:read', 'course:read', 'module:read'])]
    private ?string $title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['lesson:read'])]
    private ?string $teaser = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['lesson:read'])]
    private ?string $content = null;

    #[ORM\Column(length: 500)]
    #[Groups(['lesson:read'])]
    private ?string $videoUrl = null;

    #[ORM\Column(length: 20)]
    #[Groups(['lesson:read'])]
    private ?string $videoPlatform = null;

    #[ORM\Column]
    #[Groups(['lesson:read'])]
    private ?int $duration = null;

    #[ORM\Column]
    #[Groups(['lesson:read'])]
    private ?int $orderNumber = null;

    #[ORM\Column]
    private bool $isFreePreview = false;

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['lesson:read'])]
    private ?array $keyTakeaways = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['lesson:read'])]
    private ?string $actionItem = null;

    #[ORM\OneToMany(targetEntity: UserLessonProgress::class, mappedBy: 'lesson', cascade: ['remove'])]
    private Collection $progresses;

    public function __construct()
    {
        $this->progresses = new ArrayCollection();
    }

    // Getters et Setters

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getTeaser(): ?string
    {
        return $this->teaser;
    }

    public function setTeaser(?string $teaser): static
    {
        $this->teaser = $teaser;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getVideoUrl(): ?string
    {
        return $this->videoUrl;
    }

    public function setVideoUrl(string $videoUrl): static
    {
        $this->videoUrl = $videoUrl;
        return $this;
    }

    public function getVideoPlatform(): ?string
    {
        return $this->videoPlatform;
    }

    public function setVideoPlatform(string $videoPlatform): static
    {
        $this->videoPlatform = $videoPlatform;
        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;
        return $this;
    }

    public function getOrderNumber(): ?int
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(int $orderNumber): static
    {
        $this->orderNumber = $orderNumber;
        return $this;
    }

    public function isFreePreview(): bool
    {
        return $this->isFreePreview;
    }

    public function setIsFreePreview(bool $isFreePreview): static
    {
        $this->isFreePreview = $isFreePreview;
        return $this;
    }

    public function getKeyTakeaways(): ?array
    {
        return $this->keyTakeaways;
    }

    public function setKeyTakeaways(?array $keyTakeaways): static
    {
        $this->keyTakeaways = $keyTakeaways;
        return $this;
    }

    public function getActionItem(): ?string
    {
        return $this->actionItem;
    }

    public function setActionItem(?string $actionItem): static
    {
        $this->actionItem = $actionItem;
        return $this;
    }

    public function getProgresses(): Collection
    {
        return $this->progresses;
    }

    public function addProgress(UserLessonProgress $progress): static
    {
        if (!$this->progresses->contains($progress)) {
            $this->progresses->add($progress);
            $progress->setLesson($this);
        }
        return $this;
    }

    public function removeProgress(UserLessonProgress $progress): static
    {
        if ($this->progresses->removeElement($progress)) {
            if ($progress->getLesson() === $this) {
                $progress->setLesson(null);
            }
        }
        return $this;
    }
}