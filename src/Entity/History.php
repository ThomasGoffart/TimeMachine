<?php

namespace NorthernIndustry\TimeMachineBundle\Entity;


use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use NorthernIndustry\TimeMachineBundle\Repository\HistoryRepository;

#[ORM\Table(name: 'time_machine_history')]
#[ORM\Entity(repositoryClass: HistoryRepository::class)]
class History {

	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private readonly int $id;

	#[ORM\Column(type: 'string', length: 255)]
	private string $entity;

	#[ORM\Column(type: 'integer')]
	private int $identifier;

	#[ORM\Column(type: 'string', length: 255, nullable: true)]
	private mixed $user;

	#[ORM\Column(type: 'datetime_immutable')]
	private DateTimeImmutable $createdAt;

	#[ORM\OneToMany(mappedBy: 'history', targetEntity: Change::class, cascade: ['persist'], fetch: 'EAGER')]
	private Collection $changes;

	public function __construct() {
		$this->changes = new ArrayCollection();
	}

	public function getId(): int {
		return $this->id;
	}

	public function getEntity(): string {
		return $this->entity;
	}

	public function setEntity(string $entity): self {
		$this->entity = $entity;

		return $this;
	}

	public function getIdentifier(): int {
		return $this->identifier;
	}

	public function setIdentifier(int $identifier): self {
		$this->identifier = $identifier;

		return $this;
	}

	public function getUser(): mixed {
		return $this->user;
	}

	public function setUser(mixed $user): self {
		$this->user = $user;

		return $this;
	}

	public function getCreatedAt(): ?DateTimeImmutable {
		return $this->createdAt;
	}

	public function setCreatedAt(DateTimeImmutable $createdAt): self {
		$this->createdAt = $createdAt;

		return $this;
	}

	/**
	 * @return Collection<int, Change>
	 */
	public function getChanges(): Collection {
		return $this->changes;
	}

	public function addChange(Change $change): self {
		if (!$this->changes->contains($change)) {
			$this->changes[] = $change;

			$change->setHistory($this);
		}

		return $this;
	}

	public function removeChange(Change $change): self {
		if ($this->changes->removeElement($change) && $change->getHistory() === $this) {
			$change->setHistory(null);
		}

		return $this;
	}

}
