<?php

namespace NorthernIndustry\TimeMachineBundle\Routing;


use RuntimeException;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;
use NorthernIndustry\TimeMachineBundle\Controller\HomeController;
use Symfony\Bundle\FrameworkBundle\Routing\AnnotatedRouteControllerLoader;

class RoutingAnnotationLoader extends Loader {

	private AnnotatedRouteControllerLoader $annotatedRouteControllerLoader;

	private bool $isLoaded = false;

	public function __construct(AnnotatedRouteControllerLoader $annotatedRouteControllerLoader) {
		parent::__construct();

		$this->annotatedRouteControllerLoader = $annotatedRouteControllerLoader;
	}

	public function load($resource, $type = null): RouteCollection {
		if ($this->isLoaded === true) {
			throw new RuntimeException('Do not add the "time_machine" loader twice');
		}

		$routes = $this->annotatedRouteControllerLoader->load(HomeController::class);

		$this->isLoaded = true;

		return $routes;
	}

	public function supports($resource, $type = null): bool {
		return $type === 'time_machine';
	}
}