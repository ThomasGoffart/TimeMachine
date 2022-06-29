<?php

namespace NorthernIndustry\TimeMachineBundle\Twig;


use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;
use NorthernIndustry\TimeMachineBundle\Service\DateService;

class DateExtension extends AbstractExtension {

	public function __construct(private readonly DateService $dateService) {

	}

	public function getFilters(): array {
		return [
			new TwigFilter('datetime_format', [$this->dateService, 'formatDatetime']),
			new TwigFilter('date_format', [$this->dateService, 'formatDate']),
			new TwigFilter('time_format', [$this->dateService, 'formatTime'])
		];
	}

}