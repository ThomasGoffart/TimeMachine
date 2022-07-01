<?php

namespace NorthernIndustry\TimeMachineBundle\Entity;


use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use NorthernIndustry\TimeMachineBundle\Repository\ChangeRepository;

#[ORM\Table(name: 'time_machine_change')]
#[ORM\Entity(repositoryClass: ChangeRepository::class)]
class Change {

	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private readonly int $id;

	#[ORM\ManyToOne(targetEntity: History::class, fetch: 'EAGER', inversedBy: 'changes')]
	#[ORM\JoinColumn(nullable: false)]
	private History $history;

	#[ORM\Column(type: 'string', length: 255)]
	private string $property;

	#[ORM\Column(name: 'value', type: 'text', nullable: true)]
	private string $before;

	private string $after;

	private ?string $type = null;

	public ?DateTimeInterface $startsAt = null;

	public ?DateTimeInterface $endsAt = null;

	public function getId(): int {
		return $this->id;
	}

	public function getHistory(): History {
		return $this->history;
	}

	public function setHistory(History $history): self {
		$this->history = $history;

		return $this;
	}

	public function getProperty(): string {
		return $this->property;
	}

	public function setProperty(string $property): self {
		$this->property = $property;

		return $this;
	}

	public function getBefore(): mixed {
		return unserialize($this->before, ['allowed_classes' => true]);
	}

	public function setBefore(mixed $before): self {
		$this->before = serialize($before);

		return $this;
	}

	public function getAfter(): mixed {
		return unserialize($this->after, ['allowed_classes' => true]);
	}

	public function setAfter(mixed $after): self {
		$this->after = serialize($after);

		return $this;
	}

	public function getType(): ?string {
		return $this->type;
	}

	public function setType(?string $type): self {
		$this->type = $type;

		return $this;
	}

	public function getStartsAt(): ?DateTimeInterface {
		return $this->startsAt;
	}

	public function setStartsAt(?DateTimeInterface $startsAt): void {
		$this->startsAt = $startsAt;
	}

	public function getEndsAt(): ?DateTimeInterface {
		return $this->endsAt;
	}

	public function setEndsAt(?DateTimeInterface $endsAt): void {
		$this->endsAt = $endsAt;
	}

}

/**
 * SELECT c1.id, c1.property, c1.value, j1.created_at AS starts_at, h1.created_at AS ends_at
 * FROM `change` c1
 * INNER JOIN `history` h1
 * ON h1.id = c1.history_id
 * LEFT JOIN (
 * SELECT c.property, h.created_at, h.created_at AS starts_at
 * FROM `change` c
 * INNER JOIN `history` h
 * ON h.id = c.history_id
 * LIMIT 1
 * ) j1
 * ON j1.property = c1.property
 * AND j1.created_at < h1.created_at
 * WHERE h1.identifier = 1000
 * AND c1.property = 'lastName'
 */

/**
 * SELECT c1.id, c1.property, c1.value, j1.created_at AS starts_at, h1.created_at AS ends_at
 * FROM `change` c1
 * INNER JOIN `history` h1
 * ON h1.id = c1.history_id
 * LEFT JOIN (
 * SELECT c.id, c.property, h.created_at
 * FROM `change` c
 * INNER JOIN `history` h
 * ON h.id = c.history_id
 * GROUP BY c.id
 * ) j1
 * ON j1.property = c1.property
 * AND j1.created_at < h1.created_at
 * WHERE h1.identifier = 1000
 * AND h1.entity = 'App\\Entity\\User\\User'
 * GROUP BY c1.id
 */