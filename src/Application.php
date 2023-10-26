<?php declare(strict_types = 1);

namespace Clown\Watchdog;

use RuntimeException;
use Nette\Utils\Finder;
use Clown\Watchdog\Config\Configuration;
use Clown\Watchdog\Command\AnalyseCommand;
use Clown\Watchdog\Analyzer\FolderAnalyzer;
use Clown\Watchdog\Rules\Factories\RuleFactory;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Application as SymfonyApplication;

class Application extends SymfonyApplication
{
    /**
     * Constructor for the Application class.
     *
     * @param string $name The name of the application.
     * @param string $version The version of the application.
     * @param string|null $configPath The path to the configuration file.
     */
    public function __construct(string $name = 'Analyse', string $version = 'UNKNOWN', ?string $configPath = null)
    {
        parent::__construct($name, $version);

        // Check if the provided config path exists
        if ($configPath && !file_exists($configPath)) {
            $output = new ConsoleOutput();
            $output->writeln('<comment>Warning: Configuration file "' . $configPath . '" not found. Using default configuration.</comment>');
            $configPath = null;
        }

		// Default configuration path if not provided or not found
        $configPath = $configPath ?? "config/watchdog.neon";

		// Check if the default config path exists
        if (!file_exists($configPath)) {
            throw new RuntimeException('Default configuration file "config/watchdog.neon" not found. Cannot proceed.');
        }

        $config = new Configuration($configPath);

        $ruleFactory = new RuleFactory($config);
        $finder = new Finder();
        $folderAnalyzer = new FolderAnalyzer($finder);

        $this->add(new AnalyseCommand($ruleFactory, $config, $folderAnalyzer, $name, $version));
    }
}
