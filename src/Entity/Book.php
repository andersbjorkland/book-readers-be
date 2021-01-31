<?php

namespace App\Entity;

use App\Entity\CurrentRead;
use App\Repository\BookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BookRepository::class)
 */
class Book
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $volumeId;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="toRead")
     */
    private $users;

	/**
	 * @ORM\OneToOne(targetEntity=CurrentRead::class, cascade={"persist", "remove"}, inversedBy="book")
	 */
	private $currentRead;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $data = [];

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVolumeId(): ?string
    {
        return $this->volumeId;
    }

    public function setVolumeId(string $volumeId): self
    {
        $this->volumeId = $volumeId;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addToRead($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeToRead($this);
        }

        return $this;
    }

    public function getCurrentRead(): ?CurrentRead
    {
    	return $this->currentRead;
    }

	public function setCurrentRead(CurrentRead $currentRead): self
         	{
         		$this->currentRead = $currentRead;
         
         		return $this;
         	}

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): self
    {
        $this->data = $data;

        return $this;
    }
}
