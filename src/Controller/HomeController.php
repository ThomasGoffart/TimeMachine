<?php

namespace NorthernIndustry\TimeMachineBundle\Controller;


use DateTime;
use Twig\Environment;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use NorthernIndustry\TimeMachineBundle\TimeMachine;
use NorthernIndustry\TimeMachineBundle\Data\EntityData;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBag;

class HomeController extends AbstractController {

	public function __construct(private readonly Environment $twig, private readonly ContainerBag $containerBag) {

	}

	#[Route('/time', name: 'time_machine_index', methods: ['GET'])]
	public function index(): Response {
		$configuration = $this->containerBag->get('time_machine.configuration');

		$entities = $configuration['entities'];

		return new Response($this->twig->render('@TimeMachine/home/index.html.twig', [
			'entities' => $entities
		]));
	}

	#[Route('/time/{entity}', name: 'time_machine_show', methods: ['GET'])]
	public function show(string $entity, ManagerRegistry $managerRegistry): Response {
		$configuration = $this->containerBag->get('time_machine.configuration');

		$entities = $configuration['entities'];

		if (!in_array($entity, $entities, true)) {
			throw new NotFoundHttpException();
		}

		$entries = $managerRegistry->getRepository($entity)->findAll();

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

		return new Response($this->twig->render('@TimeMachine/home/state.html.twig', [
			'dateTime'         => $dateTime,
			'data'             => $data,
			'user'             => $user,
			'isBeforeCreation' => $isBeforeCreation
		]));
	}

	#[Route('/time/{entity}/{id}/changes', name: 'time_machine_history', methods: ['GET'])]
	public function history(string $entity, mixed $id, TimeMachine $timeMachine): Response {
		$configuration = $this->containerBag->get('time_machine.configuration');

		$entities = $configuration['entities'];

		if (!in_array($entity, $entities, true)) {
			throw new NotFoundHttpException();
		}

		$histories = $timeMachine->getHistories($entity, $id);

		dump($histories);

		return new Response($this->twig->render('@TimeMachine/home/history.html.twig', [
			'histories' => $histories
		]));
	}

}