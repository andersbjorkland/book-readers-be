<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Entity(repositoryClass=ReviewRepository::class)
 */
class Review implements JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="reviews")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Book::class, inversedBy="reviews")
     * @ORM\JoinColumn(nullable=false)
     */
    private $book;

    /**
     * @ORM\Column(type="integer")
     */
    private $score;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $text;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDraft;

    /**
     * @ORM\Column(type="boolean")
     */
    private $wouldRecommend;

    /**
     * @ORM\ManyToMany(targetEntity=Flair::class, inversedBy="reviews", cascade={"persist", "remove"})
     */
    private $flairs;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $summary;

    public function __construct()
    {
        $this->flairs = new ArrayCollection();
        $this->createdAt = new DateTime('now');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getBook(): ?Book
    {
        return $this->book;
    }

    public function setBook(?Book $book): self
    {
        $this->book = $book;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getIsDraft(): ?bool
    {
        return $this->isDraft;
    }

    public function setIsDraft(bool $isDraft): self
    {
        $this->isDraft = $isDraft;

        return $this;
    }

    public function getWouldRecommend(): ?bool
    {
        return $this->wouldRecommend;
    }

    public function setWouldRecommend(bool $wouldRecommend): self
    {
        $this->wouldRecommend = $wouldRecommend;

        return $this;
    }

    /**
     * @return Collection|Flair[]
     */
    public function getFlairs(): Collection
    {
        return $this->flairs;
    }

    public function addFlair(Flair $flair): self
    {
        if (!$this->flairs->contains($flair)) {
            $this->flairs[] = $flair;
        }

        return $this;
    }

    public function removeFlair(Flair $flair): self
    {
        $this->flairs->removeElement($flair);

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

	public function jsonSerialize(): array
	{
    	$flairs = $this->getFlairs();
    	$flairArr = [];
    	for ($i = 0; $i < count($flairs); $i++) {
    		$flairArr[] = $flairs[$i]->getFa();
	    }

		return [
			'book' => $this->getBook()->getData(),
			'score' => $this->getScore(),
			'flairs' => $flairArr,
			'text' => $this->getText(),
			'summary' => $this->getSummary(),
			'recommended' => $this->getWouldRecommend(),
			'isDraft' => $this->getIsDraft()
		];
	}
}
