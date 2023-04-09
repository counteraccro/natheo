<?php

namespace App\Entity\Admin;

use App\Repository\Admin\MailTranslationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name : "natheo.mail_translation")]
#[ORM\Entity(repositoryClass: MailTranslationRepository::class)]
class MailTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(cascade: ["persist"], inversedBy: 'mailTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Mail $mail = null;

    #[ORM\Column(length: 10)]
    private ?string $local = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMail(): ?Mail
    {
        return $this->mail;
    }

    public function setMail(?Mail $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getLocal(): ?string
    {
        return $this->local;
    }

    public function setLocal(string $local): self
    {
        $this->local = $local;

        return $this;
    }
}
