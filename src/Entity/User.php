<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *      normalizationContext={"groups"={"user:read"}},
 *      denormalizationContext={"groups"={"user:write"}},
 *      collectionOperations={"POST"},
 *      itemOperations={"GET"},
 * )
 * @UniqueEntity(fields={"email"})
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"user:write"})
     * @Assert\NotBlank()
     * @Assert\Email()
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @Groups({"user:write"})
     * @Assert\NotBlank()
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $apiToken;

    /**
     * @ORM\ManyToMany(targetEntity=Book::class, inversedBy="users", cascade={"remove"})
     */
    private $toRead;

    /**
     * @ORM\ManyToMany(targetEntity=CurrentRead::class, inversedBy="users")
     */
    private $currentRead;

    public function __construct()
    {
        $this->toRead = new ArrayCollection();
        $this->currentRead = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getIsVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function setApiToken(?string $apiToken): self
    {
        $this->apiToken = $apiToken;

        return $this;
    }

	public function __toString() {
                                                      		return $this->getEmail();
                                                      	}

    /**
     * @return Collection|Book[]
     */
    public function getToRead(): Collection
    {
        return $this->toRead;
    }

    public function addToRead(Book $toRead): self
    {
        if (!$this->toRead->contains($toRead)) {
            $this->toRead[] = $toRead;
        }

        return $this;
    }

    public function removeToRead(Book $toRead): self
    {
        $this->toRead->removeElement($toRead);

        return $this;
    }

    /**
     * @return Collection|CurrentRead[]
     */
    public function getCurrentRead(): Collection
    {
        return $this->currentRead;
    }

    public function addCurrentRead(CurrentRead $currentRead): self
    {
        if (!$this->currentRead->contains($currentRead)) {
            $this->currentRead[] = $currentRead;
        }

        return $this;
    }

    public function removeCurrentRead(CurrentRead $currentRead): self
    {
        $this->currentRead->removeElement($currentRead);

        return $this;
    }


}
