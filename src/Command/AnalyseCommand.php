<?php declare(strict_types = 1);

namespace Finie\Watchdog\Command;

use Nette\Utils\Finder;
use Nette\Utils\Strings;
use Finie\Watchdog\Rules\RuleJson;
use Symfony\Component\Console\Command\Command;
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

		// Default rules
		$rules = [new RuleJson()];

		$output = new SymfonyStyle($input, $output);
		$output->title('Finie Watchdog');
		$output->writeln('Analysing...');

		$paths = (array) $input->getArgument('path');

		if( empty($paths) ){
			$output->error('Folder not specified!');
			return 1;
		}


		foreach( $paths as $path ){

			if( !is_dir($path) ){
				$output->error('Folder not exists!');
				return 1;
			}

			$output->section("Folder: " . $path);
			$finder = Finder::findFiles()
				->from($path);
			$output->writeln($finder->count() . ' files');

			$errors = [];

			foreach ($finder as $file) {

				// Match rules
				$matchedRules = [];

				foreach ($rules as $rule) {
					print_r($file);
					foreach ($rule->getPathPatterns() as $pattern) {

						if (Strings::match((string) $file, $pattern) !== null) {
							$matchedRules[] = $rule;
							break;
						}
					}
				}

				// Analyse
				$fileViolations = [];
				foreach ($matchedRules as $rule) {
					$fileViolations = $fileViolations + $rule->processFile($file);
				}

				print_r( $matchedRules );




			}



			/*

			foreach ($finder as $file) {

				$rule = (new RuleJson)->processFile($file);

				if($rule === true ){

					$output->write('<info>'.$file . '</info>');

				}else{
					$output->write('<error>'.$file . '</error>');
				}

			}*/

		}

		if( $errors){

		}

		$output->success('Successfuly analysed');
		return 0;
	}

}
