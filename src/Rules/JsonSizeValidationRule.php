<?php declare(strict_types = 1);

namespace Clown\Watchdog\Rules;

use SplFileInfo;

class JsonSizeValidationRule implements RuleInterface
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
            '#\.json$#'
        ];
	}

	public function processFile(SplFileInfo $file): array
    {
		$maxFileSize = $this->config["maxFileSize"];
		$emptyFile = $this->config["emptyFile"];

        $violations = [];
		if( $emptyFile === true && $file->getSize() === 0 ){
			$violations[] = "The file " . $file->getFilename() . " is empty";
		}
        if ( $file->getSize() > $maxFileSize ) {
            $violations[] = "The file " . $file->getFilename() . " exceeds the allowed size of " . $this->formatBytes( $maxFileSize ) . ".";
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
