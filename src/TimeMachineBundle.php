<?php

namespace NorthernIndustry\TimeMachineBundle;


use Symfony\Component\HttpKernel\Bundle\Bundle;

class TimeMachineBundle extends Bundle {

	public function getPath(): string {
		return dirname(__DIR__);
	}

}