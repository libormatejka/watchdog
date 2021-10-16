<?php declare(strict_types = 1);

namespace Finie\Watchdog\Command;

use Finie\StructureChecker\Rules\RuleInterface;
use Nette\Configurator;
use Nette\Neon\Neon;
use Nette\Utils\Finder;
use Nette\Utils\Strings;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AnalyseCommand extends Command
{

	public function __construct()
	{
		parent::__construct('analyse');
		$this->addArgument('path', InputArgument::REQUIRED);
		$this->addOption('config', null, InputOption::VALUE_REQUIRED);
		$this->addOption('temp', null, InputOption::VALUE_REQUIRED);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		// Default rules
		$rules = [];

		// Default excludes
		$excludes = [];

		// Process config
		if ($input->getOption('config') !== null) {
			$config = (string) $input->getOption('config');
			$config = Neon::decode(file_get_contents($config));

			$temp = (string) $input->getOption('temp');

			$configurator = new Configurator();
			$configurator->setTempDirectory($temp);
			$configurator->addConfig($config);
			$container = $configurator->createContainer();
			$keys = $container->findByType(RuleInterface::class);
			foreach ($keys as $key) {
				$rules[] = $container->getService($key);
			}
			$excludes = $container->getParameters()['excludes'] ?? [];
		}

		$errors = [];
		$path = (string) $input->getArgument('path');
		$i = 0;
		/** @var SplFileInfo $file */
		$finder = Finder::findFiles()
			->from($path)
			->exclude($excludes);

		foreach ($finder as $file) {
			// Match rules
			$matchedRules = [];
			foreach ($rules as $rule) {
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
			if (count($matchedRules) == 0) {
				$output->write('.');
			} elseif (count($fileViolations) === 0) {
				$output->write('<info>.</info>');
			} else {
				$errors[$file->getPathname()] = $fileViolations;
				$output->write('<error>F</error>');
			}

			$i++;
			if ($i % 50 === 0) {
				$output->writeln('');
			}
		}
		$output->writeln('');
		$output->writeln('');
		$output->writeln($finder->count() . ' files');

		foreach ($errors as $file => $fails) {
			$io = new SymfonyStyle($input, $output);
			$io->section($file);
			foreach ($fails as $fail) {
				$io->error('- ' . $fail);
			}
		}
		if (count($errors) !== 0) {
			return 1;
		}
		return 0;
	}

}
