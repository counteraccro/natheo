<?php

declare(strict_types=1);

namespace App\Entity\Admin\Content\Media;

use App\Enum\Installation\DoctrineStrategy;
use App\Repository\Admin\Content\Media\MediaTranslationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MediaTranslationRepository::class)]
#[ORM\Table(name: 'media_translation')]
#[ORM\UniqueConstraint(name: 'UNIQ_MEDIA_TRANSLATION_MEDIA_LOCALE', columns: ['media_id', 'locale'])]
class MediaTranslation
{
    public const string DEFAULT_ALIAS = 'mediaTranslation';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: DoctrineStrategy::CURRENT)]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'mediaTranslations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Media $media = null;

    #[ORM\Column(length: 10)]
    private ?string $locale = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    /**
     * Texte alternatif d'une image
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $alt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(?Media $media): static
    {
        $this->media = $media;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getAlt(): ?string
    {
        return $this->alt;
    }

    public function setAlt(?string $alt): static
    {
        $this->alt = $alt;

        return $this;
    }
}
