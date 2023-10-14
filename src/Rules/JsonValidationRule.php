<?php declare(strict_types = 1);

namespace Clown\Watchdog\Rules;

use SplFileInfo;

class JsonValidationRule implements RuleInterface
{
	//kontroluje typ souboru...
	public function getPathPatterns(): array
	{
		// Vrátí vzor pro všechny soubory s příponou .json
        return [
            '#\.json$#'
        ];
	}

	public function processFile(SplFileInfo $file): array
    {
        $violations = [];
        $fileContent = file_get_contents($file->getPathname());

        // Kontrola, zda má soubor validní JSON strukturu
        json_decode($fileContent);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $violations[] = "Soubor nemá validní JSON strukturu: " . json_last_error_msg();
        }

        return $violations;
    }

}
