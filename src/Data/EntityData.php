<?php

namespace NorthernIndustry\TimeMachineBundle\Data;


use ReflectionClass;
use ReflectionProperty;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class EntityData {

	private static array $reflections = [];

	private ReflectionClass $reflection;
	private Collection $properties;

	public static function find(object $entity): EntityData {
		$objectId = spl_object_id($entity);

		if (!array_key_exists($objectId, self::$reflections)) {
			self::$reflections[$objectId] = new EntityData($entity);

//			dump('New ' . $objectId);
		}

		return self::$reflections[$objectId];
	}

	public function __construct(object $entity) {
		$this->reflection = new ReflectionClass($entity);
		$this->properties = new ArrayCollection();

		$reflectionProperties = array_filter($this->reflection->getProperties(), static fn(ReflectionProperty $reflectionProperty) => count($reflectionProperty->getAttributes(OneToMany::class)) === 0);

		foreach ($reflectionProperties as $reflectionProperty) {
			$this->properties->add(new PropertyData($reflectionProperty));
		}
	}

	/**
	 * @return Collection<int, PropertyData>
	 */
	public function getProperties(): Collection {
		return $this->properties;
	}

}