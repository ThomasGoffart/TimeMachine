<?php

namespace NorthernIndustry\TimeMachineBundle\Service;


use DateTime;
use Exception;
use RuntimeException;
use IntlDateFormatter;
use DateTimeInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class DateService {

	private const DATE_FORMATS = [
		'none'   => IntlDateFormatter::NONE,
		'short'  => IntlDateFormatter::SHORT,
		'medium' => IntlDateFormatter::MEDIUM,
		'long'   => IntlDateFormatter::LONG,
		'full'   => IntlDateFormatter::FULL
	];

	public function __construct(private readonly RequestStack $requestStack) {

	}

	public function formatTime(DateTimeInterface | string $dateTime, string $timeFormat = null, string $pattern = null): ?string {
		return $this->formatDatetime($dateTime, 'none', $timeFormat, $pattern);
	}

	public function formatDate(DateTimeInterface | string $dateTime, string $dateFormat = null, string $pattern = null): ?string {
		return $this->formatDatetime($dateTime, $dateFormat, 'none', $pattern);
	}

	public function formatDatetime(DateTimeInterface | string $dateTime, string $dateFormat = null, string $timeFormat = null, string $pattern = null): ?string {
		if (is_string($dateTime)) {
			try {
				$dateTime = new DateTime($dateTime);
			} catch (Exception $e) {
				throw new RuntimeException($e);
			}
		}

		$locale = $this->requestStack->getCurrentRequest()?->getLocale();

		$timezone = 'Europe/Paris';

		$formatter = new IntlDateFormatter($locale, self::DATE_FORMATS[$dateFormat] ?? self::DATE_FORMATS['full'], self::DATE_FORMATS[$timeFormat] ?? self::DATE_FORMATS['medium'], $timezone, null, $pattern);

		return $formatter->format($dateTime) ?: null;
	}

}