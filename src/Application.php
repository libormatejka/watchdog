<?php declare(strict_types = 1);

namespace Cboy\Watchdog;

use Cboy\Watchdog\Command\AnalyseCommand;
use Symfony\Component\Console\Application as SymfonyApplication;

class Application extends SymfonyApplication
{

	public function __construct(string $name = 'UNKNOWN', string $version = 'UNKNOWN')
	{
		parent::__construct($name, $version);
		$this->add(new AnalyseCommand());
	}

}
