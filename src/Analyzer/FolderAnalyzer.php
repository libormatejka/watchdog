<?php
namespace Clown\Watchdog\Analyzer;

use Nette\Utils\Finder;
use Clown\Watchdog\Rules\RuleInterface;
use Clown\Watchdog\Config\Configuration;
use Symfony\Component\Console\Style\SymfonyStyle;

class FolderAnalyzer
{
	/**
     * @var Finder Instance of the Finder utility.
     */
	private $finder;

	 /**
     * Constructor for the FolderAnalyzer class.
     *
     * @param Finder $finder Instance of the Finder utility.
     */
	public function __construct(Finder $finder)
    {
        $this->finder = $finder;
    }

	/**
     * Analyzes the given folders based on the provided rules and configuration.
     *
     * @param SymfonyStyle $output Console output handler.
     * @param array $paths List of folder paths to analyze.
     * @param array $rules List of rules to apply during analysis.
     * @param Configuration $config Configuration settings.
     * @return array Returns an array containing the total number of errors and a list of error files.
     */
    public function analyse(SymfonyStyle $output, array $paths, array $rules, Configuration $config): array
    {
        $totalErrors = 0;
        $errorFiles = [];

		$outputMessages[] = '<bg=magenta;options=underscore,bold></>';
        $outputMessages[] = '<bg=magenta;options=underscore,bold>Analyzing folders:</>';

		foreach ($paths as $path) {
			$outputMessages[] = "<fg=magenta;options=bold> - ". $path ." </>";
		}

        foreach ($paths as $path) {
            if (!is_dir($path)) {
                $outputMessages[] = 'Folder ' . $path . ' not exists! Skipping...';
                continue;
            }

			$finder = $this->finder::findFiles()
                ->from($path)
                ->exclude($config->get("parameters.excludesFolders"));

			$output->section("Folder: " . $path . " (" . iterator_count($finder->getIterator()) . " files)");

            foreach ($finder as $file) {
                $fileExtension = $file->getExtension();

                if (!in_array($fileExtension, $config->get("parameters.includesFilesType"), true)) {
                    continue;
                }

                $matchedRules = $this->matchRules($file, $rules);
                $fileViolations = $this->analyseFile($file, $matchedRules);

                if (!empty($fileViolations)) {
					$totalErrors += count($fileViolations);
					$errorFiles[$file->getRealPath()] = $fileViolations;
					foreach ($fileViolations as $violation) {

					}
					$outputMessages[] = '<error>' . $file . ' ✘ </error>';
				} else {
					$outputMessages[] = '<info>' . $file . ' ✔ </info>';
				}
            }
        }

		// Print all messages at once
        foreach ($outputMessages as $message) {
            $output->writeln($message);
        }

        return [$totalErrors, $errorFiles];
    }

	/**
     * Matches the given file against the provided rules.
     *
     * @param SplFileInfo $file File to match against the rules.
     * @param array $rules List of rules to match against.
     * @return array Returns a list of matched rules.
     */
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

	/**
     * Analyzes the given file based on the matched rules.
     *
     * @param SplFileInfo $file File to analyze.
     * @param array $matchedRules List of rules that matched the file.
     * @return array Returns a list of violations found in the file.
     */
    private function analyseFile($file, array $matchedRules): array
    {
        $fileViolations = [];
        foreach ($matchedRules as $rule) {
            $violationsFromRule = $rule->processFile($file);
            $fileViolations = array_merge($fileViolations, $violationsFromRule);
        }
        return $fileViolations;
    }
}

?>
