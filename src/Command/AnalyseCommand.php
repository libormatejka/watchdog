<?php declare(strict_types = 1);

namespace Libormatejka\Watchdog\Command;

use Nette\Utils\Finder;
use Libormatejka\Watchdog\Rules\RuleJson;
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
		// Nadefinuje pravidla
		$rules = [new RuleJson()];
		$output = new SymfonyStyle($input, $output);
		$output->title('Watchdog');

		$paths = $this->getPaths($input, $output);

		if (!$paths) {
			$output->error('Folder is not specified!');
			return 1;
		}

		$this->analyseFolders($paths, $rules, $output);

		$output->success('Successfully analysed');
		return 0;
	}

	private function getPaths(InputInterface $input, SymfonyStyle $output): array
	{
		$paths = (array) $input->getArgument('path');

		while (empty($paths) || $paths[0] === NULL || trim($paths[0]) === '') {
			$inputPath = $output->ask('Prosím, zadejte cestu k adresáři, který chcete analyzovat');

			if ($inputPath !== null) {
				$paths = explode(' ', $inputPath);
			} else {
				$paths = [null]; // Reset paths to ensure the loop continues
			}
		}

		return $paths;
	}

	private function analyseFolders(array $paths, array $rules, SymfonyStyle $output): void
	{
		$output->writeln('Analyzing folders:');
		foreach ($paths as $path) {
			$output->writeln(" - " . $path);

			if (!is_dir($path)) {
				$output->error('Folder not exists!');
				return;
			}

			$finder = Finder::findFiles()->from($path);
			$output->section("Folder: " . $path . " (" . $finder->count() . " files)");

			foreach ($finder as $file) {
				$matchedRules = $this->matchRules($file, $rules);
				$fileViolations = $this->analyseFile($file, $matchedRules);
				$output->writeln('<info>' . $file . ' ✔ </info>');
			}
		}
	}

	private function matchRules($file, array $rules): array
	{
		$matchedRules = [];
		foreach ($rules as $rule) {
			// Logic to match rules goes here
		}
		return $matchedRules;
	}

	private function analyseFile($file, array $matchedRules): array
	{
		$fileViolations = [];
		foreach ($matchedRules as $rule) {
			$fileViolations = $fileViolations + $rule->processFile($file);
		}
		return $fileViolations;
	}

}
