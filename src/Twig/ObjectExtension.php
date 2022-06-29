<?php

namespace NorthernIndustry\TimeMachineBundle\Twig;


use Exception;
use Twig\TwigFilter;
use DateTimeInterface;
use Doctrine\ORM\Proxy\Proxy;
use Twig\Extension\AbstractExtension;
use Symfony\Contracts\Translation\TranslatorInterface;
use NorthernIndustry\TimeMachineBundle\Service\DateService;

class ObjectExtension extends AbstractExtension {

	public function __construct(private readonly DateService $dateService, private readonly TranslatorInterface $translator) {

	}

	public function getFilters(): array {
		return [
			new TwigFilter('readable', [$this, 'readable'])
		];
	}

	public function readable(mixed $data, string $type = null): string {
		// string, text, boolean, integer, smallint, bigint, float
		// array, json, object, binary, blob
		// date, time, datetime & immutables
		// decimal

		if ($data === null) {
			return '<i>null</i>';
		}

		if ($type === 'date' || $type === 'date_immutable') {
			return $this->dateService->formatDate($data);
		}

		if ($type === 'datetime' || $type === 'datetime_immutable') {
			return $this->dateService->formatDatetime($data);
		}

		if ($type === 'time' || $type === 'time_immutable') {
			return $this->dateService->formatDatetime($data);
		}

		if ($type === 'boolean') {
			return '<b title="' . $this->translator->trans($data ? 'Yes' : 'No') . '">' . ($data ? '✓' : '✗') . '</b>';
		}

		if ($data instanceof DateTimeInterface) {
			return $data->format('d/m/Y H:i:s');
		}

		if ($data instanceof Proxy) {
			return '<b>' . $data->getId() . '</b>' . (method_exists($data, '__toString') ? ' / ' . $data : '');
		}

		if (is_array($data)) {
			try {
				return json_encode($data, JSON_THROW_ON_ERROR);
			} catch (Exception $e) {
				return '<b>' . $e->getMessage() . '</b>';
			}
		}

		return (string) $data;
	}

}