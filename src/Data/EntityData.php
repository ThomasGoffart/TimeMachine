<?php

namespace NorthernIndustry\TimeMachineBundle\Data;


use ReflectionClass;
use ReflectionProperty;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class EntityData {

	private static array $reflections = [];

	private Collection $properties;

	public static function find(object $entity): EntityData {
		$objectId = spl_object_id($entity);

		if (!array_key_exists($objectId, self::$reflections)) {
			self::$reflections[$objectId] = new EntityData($entity);
		}

		return self::$reflections[$objectId];
	}

	public function __construct(object $entity) {
		$reflection = new ReflectionClass($entity);
		$this->properties = new ArrayCollection();

		$reflectionProperties = array_filter($reflection->getProperties(), static fn(ReflectionProperty $reflectionProperty) => count($reflectionProperty->getAttributes(OneToMany::class)) === 0);

		foreach ($reflectionProperties as $reflectionProperty) {
			$type = null;

			$columnAttribute = $reflectionProperty->getAttributes(Column::class);

			if (count($columnAttribute) === 1) {
				$type = $columnAttribute[0]->getArguments()['type'];
			} else {
				$manyToOneAttribute = $reflectionProperty->getAttributes(ManyToOne::class);

				if (count($manyToOneAttribute) === 1) {
					$type = 'relation';
				}
			}

			if ($type) {
				$propertyData = new PropertyData($reflectionProperty, $entity, $type);

				$this->properties->set($propertyData->getName(), $propertyData);
			}
		}
	}

	/**
	 * @return Collection<int, PropertyData>
	 */
	public function getProperties(): Collection {
		return $this->properties;
	}

	public function getProperty(string $name): ?PropertyData {
		return $this->properties->get($name);
	}

}