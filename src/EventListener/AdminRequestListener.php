<?php

namespace NorthernIndustry\TimeMachineBundle\EventListener;


use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AdminRequestListener implements EventSubscriberInterface {

	public static function getSubscribedEvents(): array {
		return [
			RequestEvent::class => 'onRequest',
		];
	}

	public function __construct(private readonly AuthorizationCheckerInterface $auth) {

	}

	public function onRequest(RequestEvent $event): void {
		if (!$event->isMainRequest()) {
			return;
		}

		$uri = '/' . trim($event->getRequest()->getRequestUri(), '/') . '/';
		$prefix = '/time/';

		if (str_starts_with($uri, $prefix) && !$this->auth->isGranted('ROLE_ADMIN')) {
			$exception = new AccessDeniedException();
			$exception->setSubject($event->getRequest());

			throw $exception;
		}
	}
}