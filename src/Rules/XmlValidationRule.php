<?php declare(strict_types = 1);

namespace Clown\Watchdog\Rules;

use SplFileInfo;
use Clown\Watchdog\Config\Configuration;

class XmlValidationRule implements RuleInterface
{
    private $config;

    public function __construct(Configuration $config)
    {
        $this->config = $config;
    }

    /*
    * Checks the file type
    */
    public function getPathPatterns(): array
    {
        return [
            '#\.xml$#'
        ];
    }

    public function processFile(SplFileInfo $file): array
    {
        $violations = [];
        $fileContent = file_get_contents($file->getPathname());

        $dom = new \DOMDocument();
        $isValid = @$dom->loadXML($fileContent);

        if (!$isValid) {
            $violations[] = "The file does not have a valid XML structure.";
        }

        return $violations;
    }
}

