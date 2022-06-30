<?php

namespace NorthernIndustry\TimeMachineBundle\Data;


use ReflectionProperty;

class PropertyData {

	private string $name;

	public function __construct(private readonly ReflectionProperty $reflectionProperty, private readonly object $entity, private readonly string $type) {
		$this->name = $this->reflectionProperty->getName();
	}

	public function getName(): string {
		return $this->name;
	}

	public function getType(): string {
		return $this->type;
	}

	public function getValue(): mixed {
		return $this->reflectionProperty->getValue($this->entity);
	}

}