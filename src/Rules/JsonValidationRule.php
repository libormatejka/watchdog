<?php declare(strict_types = 1);

namespace Clown\Watchdog\Rules;

use SplFileInfo;
use Clown\Watchdog\Config\Configuration;

class JsonValidationRule implements RuleInterface
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
            '#\.json$#'
        ];
	}

	public function processFile(SplFileInfo $file): array
    {
        $violations = [];
        $fileContent = file_get_contents($file->getPathname());

        json_decode($fileContent);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $violations[] = "The file does not have a valid JSON structure: " . json_last_error_msg();
        }

        return $violations;
    }

}
