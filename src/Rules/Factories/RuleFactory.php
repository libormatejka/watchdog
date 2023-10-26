<?php
namespace Clown\Watchdog\Rules\Factories;

use Exception;
use Clown\Watchdog\Rules\RuleInterface;
use Clown\Watchdog\Config\Configuration;

class RuleFactory
{
	private $config;

    public function __construct(Configuration $config)
    {
        $this->config = $config;
    }

	/**
     * Creates and returns an instance of the specified rule.
     *
     * @param string $ruleName Name of the rule to create.
     * @param Configuration $config Configuration settings.
     * @return RuleInterface Returns an instance of the specified rule.
     * @throws Exception Throws an exception if the rule does not exist or is not a valid rule.
     */
    public function createRule(string $ruleName, Configuration $config): RuleInterface
    {
        $fullyQualifiedClassName = "Clown\\Watchdog\\Rules\\" . $ruleName;
        if (class_exists($fullyQualifiedClassName) && is_subclass_of($fullyQualifiedClassName, RuleInterface::class)) {
            return new $fullyQualifiedClassName($config);
        }
        throw new Exception("Rule {$ruleName} does not exist or is not a valid rule.");
    }

	/**
     * Initializes and returns all enabled rules.
     *
     * @return array Returns an array of initialized rules.
     */
    public function initializeAllRules(): array
    {
        $rules = [];
        $enabledRules = $this->config->get("parameters.enabledRules");
        foreach ($enabledRules as $ruleName) {
            $rule = $this->createRule($ruleName, $this->config);
            $rules[] = [
                "name" => $ruleName,
                "object" => $rule
            ];
        }
        return $rules;
    }
}
?>
