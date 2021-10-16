<?php declare(strict_types = 1);

namespace Finie\Watchdog;

use Nette\Configurator;

class Bootstrap
{

	public static function boot(): Configurator
	{
		$configurator = new Configurator();
		$configurator->setTempDirectory(__DIR__ . '/../temp');
		return $configurator;
	}

}
