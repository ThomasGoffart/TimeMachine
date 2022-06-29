<?php

namespace NorthernIndustry\TimeMachineBundle\Data;


use ReflectionProperty;
use Doctrine\ORM\Mapping\Column;

class PropertyData {

	private string $name;

	private ?string $type = null;

	public function __construct(private readonly ReflectionProperty $reflectionProperty) {
		$this->name = $this->reflectionProperty->getName();

		$columnAttribute = $this->reflectionProperty->getAttributes(Column::class);

		if (count($columnAttribute) === 1) {
			$this->type = $columnAttribute[0]->getArguments()['type'];
		}
	}

	public function getName(): string {
		return $this->name;
	}

	public function getType(): ?string {
		return $this->type;
	}

}