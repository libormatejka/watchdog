<?php declare(strict_types = 1);

namespace Clown\Watchdog\Command;

use Nette\Neon\Neon;
use Nette\Utils\Finder;
use Clown\Watchdog\Rules\RuleInterface;
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

        $config = $this->loadConfig($input, $output);
        $rules = $this->initializeRules($config);

		$output->writeln('<fg=green;bg=magenta;options=underscore,bold>Enabled rules:</>');

		foreach( $rules as $rule ){
			$output->writeln('<fg=magenta;options=bold> - ' . $rule["name"] . ' </>');
		}


        if (empty($rules)) {
            $output->error('No rule enabled!');
            return 1;
        }

        list($totalErrors, $errorFiles) = $this->analyseFolders($output, $rules, $config);

        if ($totalErrors > 0) {
            $output->section('List of files with errors:');
            foreach ($errorFiles as $filePath => $violations) {
                $output->writeln('<error>File: ' . $filePath . '</error>');
                foreach ($violations as $violation) {
                    $output->writeln(' - ' . $violation);
                }
            }
            $output->error('Analysis found ' . $totalErrors . ' error(s) in ' .  count($errorFiles) . ' file(s)!');
            return 1;
        }

        $output->success('Successfully analysed');
        return 0;
    }

	private function getPaths(SymfonyStyle $output, array $config): array
    {
		$paths = $config["parameters"]['includesFolders'];

        while (empty($paths) || $paths[0] === NULL || trim($paths[0]) === '') {
            $inputPath = $output->ask('Please enter the path to the directory you want to analyse');

            if ($inputPath !== null) {
                $paths = explode(' ', $inputPath);
            } else {
                $paths = [null];
            }
        }

        $paths = array_diff($paths, $config["parameters"]['excludesFolders']);

        return $paths;
    }

	private function analyseFolders(SymfonyStyle $output, array $rules, array $config): array
	{
		$paths = $this->getPaths($output, $config);

		if (!$paths) {
            $output->error('Folder is not specified!');
            return [1, []];
        }

		$totalErrors = 0;
		$errorFiles = [];

		$output->writeln('<bg=magenta;options=underscore,bold></>');
		$output->writeln('<bg=magenta;options=underscore,bold>Analyzing folders:</>');
		foreach ($paths as $path) {
			$output->writeln("<fg=magenta;options=bold> - ". $path ." </>");
		}

		foreach ($paths as $path) {

			if (!is_dir($path)) {
				$output->warning('Folder ' . $path . ' not exists! Skipping...');
				continue;
			}

			$finder = Finder::findFiles()
				->from($path)
				->exclude($config["parameters"]["excludesFolders"]);

			$output->section("Folder: " . $path . " (" . iterator_count($finder->getIterator()) . " files)");

			foreach ($finder as $file) {

				$fileExtension = $file->getExtension();

				// Kontrola, zda je typ souboru na seznamu povolených
				if (!in_array($fileExtension, $config['parameters']['includesFilesType'], true)) {
					continue;
				}

				$matchedRules = $this->matchRules($file, $rules);
				$fileViolations = $this->analyseFile($file, $matchedRules);

				if (!empty($fileViolations)) {
					$totalErrors += count($fileViolations);
					$errorFiles[$file->getRealPath()] = $fileViolations;
					foreach ($fileViolations as $violation) {

					}
					$output->writeln('<error>' . $file . ' ✘ </error>');
				} else {
					$output->writeln('<info>' . $file . ' ✔ </info>');
				}
			}
		}

		return [$totalErrors, $errorFiles];
	}

	private function matchRules($file, array $rules): array
	{
		$matchedRules = [];
		foreach ($rules as $rule) {
			if ($rule["object"] instanceof RuleInterface) {
				foreach ($rule["object"]->getPathPatterns() as $pattern) {
					if (preg_match($pattern, $file->getFilename())) {
						$matchedRules[] = $rule["object"];
						break;
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
			$violationsFromRule = $rule->processFile($file);
			$fileViolations = array_merge($fileViolations, $violationsFromRule);
		}
		return $fileViolations;
	}

	private function loadConfig(InputInterface $input, SymfonyStyle $output): array
    {
        $config = [
            'includes' => [],
            'excludes' => [],
            'enabledRules' => []
        ];

        $configPath = (string) $input->getOption('config');
        if ($configPath) {
            $output->writeln("<fg=yellow;options=bold>Using config file: ". $configPath ." </>");
            $loadedConfig = Neon::decode(file_get_contents($configPath));
            $config = array_merge($config, $loadedConfig);
        }

        return $config;
    }

	private function initializeRules(array $config): array
    {
        $rules = [];
		$enabledRules = $config["parameters"]['enabledRules'];
        foreach ($enabledRules as $ruleName) {
			$fullyQualifiedClassName = "Clown\\Watchdog\\Rules\\" . $ruleName;
			if (class_exists($fullyQualifiedClassName) && is_subclass_of($fullyQualifiedClassName, RuleInterface::class)) {
				$subRule = [];
				$subRule["name"] = $ruleName;
				$subRule["object"] = new $fullyQualifiedClassName($config);
				$rules[] = $subRule;
			}
		}

        return $rules;
    }
}
