<?php

namespace NorthernIndustry\TimeMachineBundle;


use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use NorthernIndustry\TimeMachineBundle\Entity\Change;
use NorthernIndustry\TimeMachineBundle\Data\EntityData;
use NorthernIndustry\TimeMachineBundle\Repository\HistoryRepository;

class TimeMachine {

	public function __construct(private readonly EntityManagerInterface $entityManager, private readonly HistoryRepository $historyRepository) {

	}

	/**
	 * @template T
	 *
	 * @param class-string<T> | T $class
	 * @param int | null $id
	 *
	 * @return Collection<int, T>
	 */
	public function getHistories(mixed $class, mixed $id = null): Collection {
		if (is_string($class)) {
			$entity = $this->entityManager->getRepository($class)->find($id);
		} else {
			$entity = $class;

			$class = get_class($entity);
			$id = $entity->getId();
		}

		$histories = $this->historyRepository->findBy([
			'entity'     => $class,
			'identifier' => $id
		]);

		$data = EntityData::find($entity);

		/** @var Collection<int, Change> $changes */
		$changes = new ArrayCollection();

		foreach ($histories as $history) {
			foreach ($history->getChanges() as $change) {
				$changes->add($change);
			}
		}

		foreach ($changes as $index => $change) {
			$array = new ArrayCollection($changes->slice($index + 1));

			/** @var ?Change $hasAfter */
			$hasAfter = $array->filter(fn(Change $c) => $c->getProperty() === $change->getProperty())->first() ?: null;

			if ($hasAfter) {
				$after = $hasAfter->getBefore();
			} else {
				$after = $data->getProperty($change->getProperty())?->getValue();
			}

			$change->setType($data->getProperty($change->getProperty())?->getType());
			$change->setAfter($after);
		}

		return new ArrayCollection($histories);
	}

	/**
	 * @template T
	 *
	 * @param T $entity
	 *
	 * @return T
	 */
	public function getState(mixed $entity, DateTimeInterface $dateTime) {
		$resultSetMapping = new ResultSetMapping();

		$resultSetMapping->addEntityResult(Change::class, 'change');

		$resultSetMapping->addFieldResult('change', 'id', 'id');
		$resultSetMapping->addFieldResult('change', 'property', 'property');
		$resultSetMapping->addFieldResult('change', 'value', 'before');

		$sql = "
			SELECT entity.id, entity.property, entity.value, entity.starts_at, entity.ends_at
			
			FROM (
				SELECT c1.id AS id,
				       c1.property AS property,
				       c1.value AS value,
				       j1.created_at AS starts_at,
				       h1.created_at AS ends_at
				
				FROM `time_machine_change` c1
				
				INNER JOIN `time_machine_history` h1
				ON h1.id = c1.history_id
				
				LEFT JOIN (
				
					SELECT c.id, c.property, h.created_at
					FROM `time_machine_change` c
					
					INNER JOIN `time_machine_history` h
					ON h.id = c.history_id
					
					GROUP BY c.id
				
				) j1
				
				ON j1.property = c1.property
				AND j1.created_at < h1.created_at
				
				WHERE h1.entity = :entity
			    AND h1.identifier = :identifier
				
				GROUP BY c1.id, c1.property, c1.value, j1.created_at, h1.created_at
			) AS entity
			
			WHERE (entity.starts_at < :dateTime OR entity.starts_at IS NULL)
			AND entity.ends_at > :dateTime
		";

		$query = $this->entityManager->createNativeQuery(trim(preg_replace('/\s\s+/', ' ', $sql)), $resultSetMapping)
		                             ->setParameter('entity', get_class($entity))
		                             ->setParameter('identifier', $entity->getId())
		                             ->setParameter('dateTime', $dateTime);

		$results = $query->getResult();

		foreach ($results as $result) {
			$method = 'set' . ucfirst($result->getProperty());

			$entity->$method($result->getBefore());
		}

		return $entity;
	}

}