<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'image')]
class Image
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $publicId = null;

    #[ORM\Column(length: 500)]
    private ?string $url = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $alt = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'images')]
    private ?Course $course = null;

    // Getters et setters
    public function getId(): ?int { return $this->id; }
    public function getPublicId(): ?string { return $this->publicId; }
    public function setPublicId(string $publicId): self { $this->publicId = $publicId; return $this; }
    public function getUrl(): ?string { return $this->url; }
    public function setUrl(string $url): self { $this->url = $url; return $this; }
    public function getAlt(): ?string { return $this->alt; }
    public function setAlt(?string $alt): self { $this->alt = $alt; return $this; }
    public function getType(): ?string { return $this->type; }
    public function setType(?string $type): self { $this->type = $type; return $this; }
    public function getCourse(): ?Course { return $this->course; }
    public function setCourse(?Course $course): self { $this->course = $course; return $this; }
}