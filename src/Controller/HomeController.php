<?php

namespace NorthernIndustry\TimeMachineBundle\Controller;


use DateTime;
use Twig\Environment;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use NorthernIndustry\TimeMachineBundle\TimeMachine;
use NorthernIndustry\TimeMachineBundle\Entity\Change;
use NorthernIndustry\TimeMachineBundle\Data\EntityData;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBag;

class HomeController extends AbstractController {

	public function __construct(private readonly Environment $twig, private readonly ContainerBag $containerBag) {

	}

	#[Route('/time', name: 'time_machine_index', methods: ['GET'])]
	public function index(EntityManagerInterface $entityManager): Response {
		$configuration = $this->containerBag->get('time_machine.configuration');

		$entities = $configuration['entities'];
		$counts = [];

		foreach ($entities as $entity) {
			$counts[$entity] = $entityManager->getRepository($entity)->count([]);
		}

		return new Response($this->twig->render('@TimeMachine/home/index.html.twig', [
			'entities' => $entities,
			'counts'   => $counts
		]));
	}

	#[Route('/time/{entity}', name: 'time_machine_show', methods: ['GET'])]
	public function show(string $entity, EntityManagerInterface $entityManager, Request $request, PaginatorInterface $paginator): Response {
		$configuration = $this->containerBag->get('time_machine.configuration');

		$entities = $configuration['entities'];

		if (!in_array($entity, $entities, true)) {
			throw new NotFoundHttpException();
		}

		$query = $entityManager->createQuery("SELECT u FROM $entity u ORDER BY u.id");
		$entries = $paginator->paginate($query, $request->query->get('page', 1), 10);

		$data = EntityData::find($entries[0]);

		return new Response($this->twig->render('@TimeMachine/home/show.html.twig', [
			'entity'  => $entity,
			'entries' => $entries,
			'data'    => $data
		]));
	}

	#[Route('/time/{entity}/{id}', name: 'time_machine_state', methods: ['GET'])]
	public function state(string $entity, mixed $id, Request $request, ManagerRegistry $managerRegistry, TimeMachine $timeMachine): Response {
		$configuration = $this->containerBag->get('time_machine.configuration');

		$entities = $configuration['entities'];

		if (!in_array($entity, $entities, true)) {
			throw new NotFoundHttpException();
		}

		$date = $request->query->get('date');
		$time = $request->query->get('time');

		$user = $managerRegistry->getRepository($entity)->find($id);

		if ($date !== null) {
			$dateTime = new DateTime($date . ' ' . $time);

			$user = $timeMachine->getState($user, $dateTime);
		} else {
			$dateTime = new DateTime();
		}

		$data = EntityData::find($user);

		$isBeforeCreation = $user->getCreatedAt() > $dateTime;

		return new Response($this->twig->render('@TimeMachine/entity/state.html.twig', [
			'entity'           => $entity,
			'id'               => $id,
			'dateTime'         => $dateTime,
			'data'             => $data,
			'user'             => $user,
			'isBeforeCreation' => $isBeforeCreation
		]));
	}

	#[Route('/time/{entity}/{id}/changes', name: 'time_machine_changes', methods: ['GET'])]
	public function changes(string $entity, mixed $id, TimeMachine $timeMachine): Response {
		$configuration = $this->containerBag->get('time_machine.configuration');

		$entities = $configuration['entities'];

		if (!in_array($entity, $entities, true)) {
			throw new NotFoundHttpException();
		}

		$histories = $timeMachine->getHistories($entity, $id);

		dump($histories);

		return new Response($this->twig->render('@TimeMachine/entity/changes.html.twig', [
			'entity'    => $entity,
			'id'        => $id,
			'histories' => $histories
		]));
	}

	#[Route('/time/{entity}/{id}/timeline', name: 'time_machine_timeline', methods: ['GET'])]
	public function timeline(string $entity, mixed $id, ManagerRegistry $managerRegistry, TimeMachine $timeMachine): Response {
		$entry = $managerRegistry->getRepository($entity)->find($id);

		$properties = EntityData::find($entry)->getProperties();
		$histories = $timeMachine->getHistories($entry);

		dump($properties, $histories);

		$values = [];
		$offsets = [];

		$allChanges = [];

		foreach ($histories as $history) {
			foreach ($history->getChanges() as $change) {
				$allChanges[$change->getId()] = $change;
			}
		}

		foreach ($properties as $property) {

			foreach ($histories as $history) {
				$propertyName = $property->getName();

				$change = $history->getChanges()
				                  ->filter(fn(Change $change) => $change->getProperty() === $propertyName)
				                  ->last();

				if (!isset($offsets[$propertyName])) {
					$offsets[$propertyName] = 0;
				}

				++$offsets[$propertyName];

				if ($change) {
					$values[$propertyName][$change->getId()] = $offsets[$propertyName];

					$offsets[$propertyName] = 0;
				}
			}
		}

		return new Response($this->twig->render('@TimeMachine/entity/timeline.html.twig', [
			'entity'     => $entity,
			'id'         => $id,
			'entry'      => $entry,
			'properties' => $properties,
			'histories'  => $histories,
			'changes'    => $allChanges,
			'values'     => $values,
			'offsets'    => $offsets
		]));
	}

}