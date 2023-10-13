<?php declare(strict_types = 1);

namespace Clown\Watchdog;

use Clown\Watchdog\Command\AnalyseCommand;
use Symfony\Component\Console\Application as SymfonyApplication;

class Application extends SymfonyApplication
{
	public function __construct(string $name = 'Analyse', string $version = 'UNKNOWN')
	{
		parent::__construct($name, $version);
		$this->add(new AnalyseCommand());
	}

}
