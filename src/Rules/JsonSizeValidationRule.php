<?php declare(strict_types = 1);

namespace Clown\Watchdog\Rules;

use SplFileInfo;

class JsonSizeValidationRule implements RuleInterface
{
	private $maxSize; // v bytech

    public function __construct(int $maxSize)
    {
        $this->maxSize = $maxSize;
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
        if ($file->getSize() > $this->maxSize) {
            $violations[] = "The file " . $file->getFilename() . " exceeds the allowed size of " . $this->formatBytes($this->maxSize) . ".";
        }
        return $violations;
    }

	private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

}
