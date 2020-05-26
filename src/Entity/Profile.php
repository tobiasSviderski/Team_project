<?php

namespace App\Entity;

use App\Repository\ProfileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProfileRepository::class)
 */
class Profile
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
    private $title;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="profiles")
     */
    private $author;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enable;

    /**
     * @ORM\Column(type="boolean")
     */
    private $forAll;

    /**
     * @ORM\OneToMany(targetEntity=Log::class, mappedBy="profile", orphanRemoval=true)
     */
    private $logs;

    /**
     * @ORM\OneToMany(targetEntity=Subscription::class, mappedBy="profile")
     */
    private $subscriptions;

    /**
     * @ORM\OneToOne(targetEntity=File::class, cascade={"persist", "remove"})
     */
    private $file;

    public function __construct()
    {
        $this->logs = new ArrayCollection();
        $this->forAll = true;
        $this->subscriptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getEnable(): ?bool
    {
        return $this->enable;
    }

    public function setEnable(bool $enable): self
    {
        $this->enable = $enable;

        return $this;
    }

    public function setForAll(bool $forAll): self
    {
        $this->forAll = $forAll;
        return $this;
    }

    public function isforAll(): ?bool
    {
        return $this->forAll;
    }

    /**
     * @return Collection|Log[]
     */
    public function getLogs(): Collection
    {
        return $this->logs;
    }

    public function addLog(Log $log): self
    {
        if (!$this->logs->contains($log)) {
            $this->logs[] = $log;
            $log->setProfile($this);
        }

        return $this;
    }

    public function removeLog(Log $log): self
    {
        if ($this->logs->contains($log)) {
            $this->logs->removeElement($log);
            // set the owning side to null (unless already changed)
            if ($log->getProfile() === $this) {
                $log->setProfile(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Subscription[]
     */
    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }

    public function addSubscription(Subscription $subscription): self
    {
        if (!$this->subscriptions->contains($subscription)) {
            $this->subscriptions[] = $subscription;
            $subscription->setProfile($this);
        }

        $this->forAll = false;

        return $this;
    }

    public function removeSubscription(Subscription $subscription): self
    {
        if ($this->subscriptions->contains($subscription)) {
            $this->subscriptions->removeElement($subscription);
            // set the owning side to null (unless already changed)
            if ($subscription->getProfile() === $this) {
                $subscription->setProfile(null);
            }
        }

        if (empty($this->subscriptions))
        {
            $this->forAll = true;
        }

        return $this;
    }

    public function eraseSubscription(): self
    {
        foreach($this->subscriptions as $subscription)
        {
            $this->removeSubscription($subscription);
        }

        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): self
    {
        $this->file = $file;

        return $this;
    }
}
