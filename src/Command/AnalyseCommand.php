<?php declare(strict_types = 1);

namespace Finie\Watchdog\Command;

use Nette\Neon\Neon;
use Finie\Watchdog\Rules\RuleJson;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AnalyseCommand extends Command
{

	public function __construct()
	{
		parent::__construct('analyse');
	}
	
	protected function execute(InputInterface $input, OutputInterface $output): int
	{		
		$config = Neon::decode($this->loadConfig());

		$io = new SymfonyStyle($input, $output);
		$io->title('Finie Watchdog');
		$io->writeln('Analysing...');
		
		$command = new RuleJson($config);
		$command->run(new ArrayInput([]), $output);	

		$io->success('Successfuly analysed');
		return 0;
	}

	private function loadConfig(): string
	{
		$configInWorkingDirectory = getcwd() . '/.finie/config.neon';
		if(is_file($configInWorkingDirectory)) {
			return file_get_contents($configInWorkingDirectory);
		}

		$devConfig = __DIR__ . '/../.finie/config.neon';
		if(is_file($devConfig)) {
			return file_get_contents($configInWorkingDirectory);
		}

		throw new \InvalidArgumentException('Config file not found');
	}

}
