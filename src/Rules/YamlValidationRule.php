<?php declare(strict_types = 1);

namespace Clown\Watchdog\Rules;

use SplFileInfo;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

class YamlValidationRule implements RuleInterface
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /*
    * Checks the file type
    */
    public function getPathPatterns(): array
    {
        return [
            '#\.ya?ml$#' // Toto pravidlo pokryje jak .yaml, tak .yml rozšíření
        ];
    }

    public function processFile(SplFileInfo $file): array
    {
        $violations = [];
        $fileContent = file_get_contents($file->getPathname());

        try {
            Yaml::parse($fileContent);
        } catch (ParseException $e) {
            $violations[] = "The file does not have a valid YAML structure: " . $e->getMessage();
        }

        return $violations;
    }
}
