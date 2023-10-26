<?php
namespace Clown\Watchdog\Config;

use Nette\Neon\Neon;

class Configuration
{
	/**
     * @var array Holds the configuration data.
     */
    private $config;

	/**
     * Constructor for the Configuration class.
     *
     * @param string $configPath Path to the configuration file.
     */
    public function __construct(string $configPath)
    {
        $this->loadConfig($configPath);
    }

	/**
     * Loads the configuration from the given path and merges it with the default configuration.
     *
     * @param string $configPath Path to the configuration file.
     */
    private function loadConfig(string $configPath): void
    {
        $defaultConfig = [
            'includes' => [],
            'excludes' => [],
            'enabledRules' => []
        ];

        if ($configPath) {
            $loadedConfig = Neon::decode(file_get_contents($configPath));
            $this->config = array_merge($defaultConfig, $loadedConfig);
        } else {
            $this->config = $defaultConfig;
        }
    }

	/**
     * Retrieves a configuration value by its key. Supports dot notation for nested keys.
     *
     * @param string $key The key to retrieve the value for.
     * @param mixed $default The default value to return if the key is not found.
     * @return mixed The configuration value or the default value if the key is not found.
     */
    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $temp = $this->config;

        foreach ($keys as $keyPart) {
            if (!isset($temp[$keyPart])) {
                return $default;
            }
            $temp = $temp[$keyPart];
        }

        return $temp;
    }
}
?>
