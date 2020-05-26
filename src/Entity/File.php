<?php

namespace App\Entity;

use App\Repository\FileRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FileRepository::class)
 */
class File
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $filename;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $extention;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $version;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     */
    private $attemptToChangeBy;


    public function __construct()
    {
        $this->attemptToChangeBy = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getExtention(): ?string
    {
        return $this->extention;
    }

    public function setExtention(string $extention): self
    {
        $this->extention = $extention;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getAttemptToChangeBy(): ?User
    {
        return $this->attemptToChangeBy;
    }

    public function setAttemptToChangeBy(?User $attemptToChangeBy): self
    {
        $this->attemptToChangeBy = $attemptToChangeBy;

        return $this;
    }
}
