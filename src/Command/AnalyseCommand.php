<?php declare(strict_types = 1);

namespace Clown\Watchdog\Command;

use Nette\Neon\Neon;
use Nette\Utils\Finder;
use Clown\Watchdog\Rules\RuleInterface;
use Clown\Watchdog\Rules\JsonValidationRule;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
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
		$this->addOption('config', null, InputOption::VALUE_REQUIRED, 'Path to the configuration file');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$output = new SymfonyStyle($input, $output);
		$output->title('Watchdog');

		// Config
		$configPath = (string) $input->getOption('config');
		if ($configPath) {
			$output->writeln('Using config file: ' . $configPath);
			$config = Neon::decode(file_get_contents($configPath));
			$includes = $config['parameters']['includes'] ?? [];
			$excludes = $config['parameters']['excludes'] ?? [];
			$enabledRules = $config['parameters']['enabledRules'] ?? [];
		}

		// Nadefinuje pravidla
		$rules = [];
		if (in_array('JsonValidationRule', $enabledRules)) {
			$rules[] = new JsonValidationRule();
		}

		if (empty($rules)) {
			$output->error('No rule enabled!');
			return 1;
		}

		// Get Folders
		$paths = $this->getPaths($input, $output);

		// Add included folders
		$paths = array_merge($paths, $includes);

		// Remove excluded folders from paths
		if (!empty($excludes)) {
			$paths = array_diff($paths, $excludes);
		}

		if (!$paths) {
			$output->error('Folder is not specified!');
			return 1;
		}

		// Analyse Folders
		$totalErrors  = $this->analyseFolders($paths, $rules, $output, $excludes);

		if ($totalErrors > 0) {
			$output->error('Analysis found ' . $totalErrors . ' errors!');
			return 1;
		}

		$output->success('Successfully analysed');
		return 0;
	}

	private function getPaths(InputInterface $input, SymfonyStyle $output): array
	{
		$paths = (array) $input->getArgument('path');

		while (empty($paths) || $paths[0] === NULL || trim($paths[0]) === '') {
			$inputPath = $output->ask('Please enter the path to the directory you want to analyse');

			if ($inputPath !== null) {
				$paths = explode(' ', $inputPath);
			} else {
				$paths = [null];
			}
		}

		return $paths;
	}

	private function analyseFolders(array $paths, array $rules, SymfonyStyle $output, array $excludes): int
	{
		$totalErrors = 0;

		$output->writeln('Analyzing folders:');
		foreach ($paths as $path) {
			$output->writeln(" - " . $path);
		}

		foreach ($paths as $path) {

			if (!is_dir($path)) {
				$output->error('Folder ' . $path . ' not exists! Skipping...');
				continue;
			}

			$finder = Finder::findFiles()
				->from($path)
				->exclude($excludes);
			$output->section("Folder: " . $path . " (" . $finder->count() . " files)");

			foreach ($finder as $file) {
				$matchedRules = $this->matchRules($file, $rules);
				$fileViolations = $this->analyseFile($file, $matchedRules);

				if (!empty($fileViolations)) {
					$totalErrors += count($fileViolations);
					foreach ($fileViolations as $violation) {

					}
					$output->writeln('<error>' . $file . ' ✘ ' . $violation . '</error>');
				} else {
					$output->writeln('<info>' . $file . ' ✔ </info>');
				}
			}
		}

		return $totalErrors;
	}

	private function matchRules($file, array $rules): array
	{
		$matchedRules = [];
		foreach ($rules as $rule) {
			if ($rule instanceof RuleInterface) {
				foreach ($rule->getPathPatterns() as $pattern) {
					if (preg_match($pattern, $file->getFilename())) {
						$matchedRules[] = $rule;
						break; // Pokud soubor odpovídá vzoru, přidáme pravidlo a přejdeme na další pravidlo
					}
				}
			}
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
