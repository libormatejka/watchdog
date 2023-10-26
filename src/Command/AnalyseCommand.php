<?php declare(strict_types = 1);

namespace Clown\Watchdog\Command;

use Clown\Watchdog\Config\Configuration;
use Clown\Watchdog\Analyzer\FolderAnalyzer;
use Symfony\Component\Console\Command\Command;
use Clown\Watchdog\Rules\Factories\RuleFactory;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AnalyseCommand extends Command
{
	const SUCCESS = 0;
    const ERROR = 1;

	private $ruleFactory;
	private $config;
	private $folderAnalyzer;
	private $appName;
	private $appVersion;

	/**
     * Constructor for the AnalyseCommand class.
     *
     * @param RuleFactory $ruleFactory
     * @param Configuration $config
     * @param FolderAnalyzer $folderAnalyzer
     */
	public function __construct(
		RuleFactory $ruleFactory,
		Configuration $config,
		FolderAnalyzer $folderAnalyzer,
		string $appName,
    	string $appVersion
		)
	{
		parent::__construct('analyse');
		$this->ruleFactory = $ruleFactory;
		$this->config = $config;
		$this->folderAnalyzer = $folderAnalyzer;
		$this->appName = $appName;
    	$this->appVersion = $appVersion;
		$this->addArgument('path', InputArgument::IS_ARRAY, 'Which folders do you want to analyse?');
		$this->addOption('config', null, InputOption::VALUE_REQUIRED, 'Path to the configuration file');
	}

	/**
     * Executes the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$output = new SymfonyStyle($input, $output);
		$output->title($this->appName . " (" . $this->appVersion . ")");
		$rules = $this->ruleFactory->initializeAllRules($this->config);

		$this->displayRules($output, $rules);

		if (empty($rules)) {
			$output->error('No rule enabled!');
			return self::ERROR;
		}

		list($totalErrors, $errorFiles) = $this->folderAnalyzer->analyse($output, $this->getPaths($output, $this->config), $rules, $this->config);
		return $this->handleErrors($output, $totalErrors, $errorFiles);
	}

	/**
     * Handles errors and displays them.
     *
     * @param SymfonyStyle $output
     * @param int $totalErrors
     * @param array $errorFiles
     * @return int
     */
	private function handleErrors(SymfonyStyle $output, int $totalErrors, array $errorFiles): int
	{
		if ($totalErrors > 0) {
			$output->section('List of files with errors:');
			foreach ($errorFiles as $filePath => $violations) {
				$output->writeln('<error>File: ' . $filePath . '</error>');
				foreach ($violations as $violation) {
					$output->writeln(' - ' . $violation);
				}
			}
			$output->error('Analysis found ' . $totalErrors . ' error(s) in ' . count($errorFiles) . ' file(s)!');
			return self::ERROR;
		}
		$output->success('Successfully analysed');
		return self::SUCCESS;
	}

	/**
     * Displays the enabled rules.
     *
     * @param SymfonyStyle $output
     * @param array $rules
     */
	private function displayRules(SymfonyStyle $output, array $rules): void
	{
		$output->writeln('<fg=green;bg=magenta;options=underscore,bold>Enabled rules:</>');
		foreach ($rules as $rule) {
			$output->writeln('<fg=magenta;options=bold> - ' . $rule["name"] . ' </>');
		}
	}

	/**
     * Retrieves the paths for analysis.
     *
     * @param SymfonyStyle $output
     * @param Configuration $config
     * @return array
     */
	private function getPaths(SymfonyStyle $output, Configuration $config): array
    {
		$paths = $config->get("parameters.includesFolders");

        while (empty($paths) || $paths[0] === NULL || trim($paths[0]) === '') {
            $inputPath = $output->ask('Please enter the path to the directory you want to analyse');

            if ($inputPath !== null) {
                $paths = explode(' ', $inputPath);
            } else {
                $paths = [null];
            }
        }

        $paths = array_diff( $paths, $config->get("parameters.excludesFolders") );

        return $paths;
    }
}
