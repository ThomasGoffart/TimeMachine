<?php

namespace NorthernIndustry\TimeMachineBundle\EventListener;


use DateTimeImmutable;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\Security;
use Doctrine\Common\Collections\ArrayCollection;
use NorthernIndustry\TimeMachineBundle\Entity\Change;
use NorthernIndustry\TimeMachineBundle\Entity\History;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;

class DatabaseSubscriber implements EventSubscriberInterface {

	private bool $enabled;
	private array $entities;
	private array $ignoredColumns;

	private Collection $histories;

	public function __construct(private readonly Security $security) {
		$this->histories = new ArrayCollection();
	}

	public function isEnabled(): bool {
		return $this->enabled;
	}

	public function setEnabled(bool $enabled): void {
		$this->enabled = $enabled;
	}

	public function getEntities(): array {
		return $this->entities;
	}

	public function setEntities(array $entities): void {
		$this->entities = $entities;
	}

	public function getIgnoredColumns(): array {
		return $this->ignoredColumns;
	}

	public function setIgnoredColumns(array $ignoredColumns): void {
		$this->ignoredColumns = $ignoredColumns;
	}

	public function getSubscribedEvents(): array {
		return [
			Events::preUpdate,
			Events::postUpdate,
			Events::preFlush
		];
	}

	public function preUpdate(PreUpdateEventArgs $event): void {
		if (!$this->isEnabled()) {
			return;
		}

		$entities = $this->getEntities();

		$entity = $event->getEntity();
		$class = get_class($entity);

		if (!empty($entities) && !in_array($class, $entities, true)) {
			return;
		}

		$history = new History();
		$history->setEntity($class);
		$history->setIdentifier($entity->getId());
		$history->setCreatedAt(new DateTimeImmutable());

		$user = $this->security->getUser();

		if ($user && method_exists($user, 'getId')) {
			$history->setUser($user->getId());
		}

		$ignoredColumns = $this->getIgnoredColumns();

		foreach ($event->getEntityChangeSet() as $property => $values) {
			if (!in_array($property, $ignoredColumns, true)) {
				$oldValue = $values[0];

				$change = new Change();
				$change->setProperty($property);
				$change->setBefore($oldValue);

				$history->addChange($change);
			}
		}

		if ($history->getChanges()->count()) {
			$this->histories->add($history);
		}
	}

	public function postUpdate(LifecycleEventArgs $event): void {
		$entityManager = $event->getEntityManager();

		foreach ($this->histories as $history) {
			$entityManager->persist($history);
			$entityManager->flush();
		}
	}

	public function preFlush(PreFlushEventArgs $event): void {
		$entityManager = $event->getEntityManager();

		foreach ($entityManager->getUnitOfWork()->getScheduledEntityInsertions() as $object) {
			$hasCreatedBy = method_exists($object, 'getCreatedBy') && method_exists($object, 'setCreatedBy');
			$hasCreatedAt = method_exists($object, 'getCreatedAt') && method_exists($object, 'setCreatedAt');

			$user = $this->security->getUser();

			if ($hasCreatedBy) {
				/** @phpstan-ignore-next-line */
				$object->setCreatedBy($user);
			}

			if ($hasCreatedAt) {
				$object->setCreatedAt(new DateTimeImmutable());
			}

			if ($hasCreatedBy || $hasCreatedAt) {
				$entityManager->persist($object);
			}
		}

		foreach ($entityManager->getUnitOfWork()->getScheduledEntityDeletions() as $object) {
			$hasDeletedBy = method_exists($object, 'getDeletedBy') && method_exists($object, 'setDeletedBy');
			$hasDeletedAt = method_exists($object, 'getDeletedAt') && method_exists($object, 'setDeletedAt');

			if ($hasDeletedBy) {
				if ($this->security->getUser() === null) {
					continue;
				}

				/** @phpstan-ignore-next-line */
				$object->setDeletedBy($this->security->getUser());
			}

			if ($hasDeletedAt) {
				if ($object->getDeletedAt() instanceof DateTimeImmutable) {
					continue;
				}

				$object->setDeletedAt(new DateTimeImmutable());
			}

			if ($hasDeletedBy || $hasDeletedAt) {
				$entityManager->persist($object);
			}
		}
	}

}