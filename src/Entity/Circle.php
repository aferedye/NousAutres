<?php

namespace App\Entity;

use App\Repository\CircleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CircleRepository::class)]
#[ORM\Table(name: 'circle')]
#[ORM\HasLifecycleCallbacks]
class Circle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 140)]
    #[Assert\NotBlank(message: 'name is required')]
    #[Assert\Length(max: 140)]
    private string $name = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 160, unique: true)]
    private string $slug = '';

    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'public'])]
    private string $visibility = 'public'; // public | private | hidden

    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'active'])]
    private string $status = 'active'; // active | archived

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $updatedAt;

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }

    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): self { $this->slug = $slug; return $this; }

    public function getVisibility(): string { return $this->visibility; }
    public function setVisibility(string $visibility): self { $this->visibility = $visibility; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $dt): self { $this->createdAt = $dt; return $this; }

    public function getUpdatedAt(): \DateTime { return $this->updatedAt; }
    public function setUpdatedAt(\DateTime $dt): self { $this->updatedAt = $dt; return $this; }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $now = new \DateTimeImmutable('now');
        $this->createdAt = $now;
        $this->updatedAt = new \DateTime('now');

        if (!$this->slug) {
            $this->slug = self::slugify($this->name);
        }
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTime('now');
        if (!$this->slug) {
            $this->slug = self::slugify($this->name);
        }
    }

    public static function slugify(string $value): string
    {
        $value = strtolower(trim($value));
        // replace non letters or digits by -
        $value = preg_replace('~[^a-z0-9]+~', '-', $value) ?? '';
        $value = trim($value, '-');
        if ($value === '') {
            $value = 'circle-' . substr(sha1((string)mt_rand()), 0, 8);
        }
        return $value;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'slug' => $this->getSlug(),
            'visibility' => $this->getVisibility(),
            'status' => $this->getStatus(),
            'createdAt' => $this->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'updatedAt' => $this->getUpdatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }
}
