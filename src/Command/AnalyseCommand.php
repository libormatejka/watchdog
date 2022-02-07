<?php declare(strict_types = 1);

namespace Finie\Watchdog\Command;

use Nette\Neon\Neon;
use Nette\Utils\Finder;
use Nette\Utils\Strings;
use Finie\Watchdog\Rules\RuleJson;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AnalyseCommand extends Command
{

	public function __construct()
	{
		parent::__construct('analyse');
		$this->addArgument('path', InputArgument::IS_ARRAY, 'Which folders do you want to analyse?');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$output = new SymfonyStyle($input, $output);
		$output->title('Finie Watchdog');
		$output->writeln('Analysing...');

		$paths = (array) $input->getArgument('path');

		if( empty($paths) ){
			$output->error('Folder not specified!');
			return 1;
		}

		foreach( $paths as $path ){

			$output->section("Folder: " . $path);
			$finder = Finder::findFiles()
				->from($path);
			$output->writeln($finder->count() . ' files');

			$errors = [];

			foreach ($finder as $file) {

				$rule = (new RuleJson)->processFile($file);
				$output->write('<error>F</error>');
			}

		}

		if( $errors){

		}

		$output->success('Successfuly analysed');
		return 0;
	}

}
