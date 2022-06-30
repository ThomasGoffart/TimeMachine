<?php

namespace NorthernIndustry\TimeMachineBundle;


use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

trait CreatedAt {

	#[ORM\Column(type: 'datetime_immutable')]
	private DateTimeImmutable $createdAt;

	public function getCreatedAt(): DateTimeImmutable {
		return $this->createdAt;
	}

	public function setCreatedAt(DateTimeImmutable $createdAt): self {
		$this->createdAt = $createdAt;

		return $this;
	}

}