<?php

namespace NorthernIndustry\TimeMachineBundle\Controller;


use Twig\Environment;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController {

	public function __construct(private readonly Environment $twig) {

	}

	#[Route('/time', name: 'time_machine_index', methods: ['GET'])]
	public function index(): Response {
		return new Response($this->twig->render('@TimeMachine/home/index.html.twig'));
	}

}