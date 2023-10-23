<?php declare(strict_types = 1);

namespace Clown\Watchdog\Rules;

use SplFileInfo;

class FileSizeValidationRule implements RuleInterface
{
	private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

	public function getPathPatterns(): array
	{
		$fileTypes = $this->config["parameters"]["includesFilesType"];
		$patterns = [];
		foreach ($fileTypes as $fileType) {
			$patterns[] = '#\.' . $fileType . '$#';
		}
		return $patterns;
	}

	public function processFile(SplFileInfo $file): array
    {
		$fileType = $file->getExtension();
		$maxFileSize = $this->config["parameters"]["fileSettings"]["maxFileSize"]; // Default value

		// Check if there's a specific max file size for this file type in the config
		if (isset($this->config["fileTypeRules"][$fileType]["maxFileSize"])) {
			$maxFileSize = $this->config["fileTypeRules"][$fileType]["maxFileSize"];
		}
		var_dump( $fileType . " ----------- " .$maxFileSize );

        $violations = [];
		if( $file->getSize() === 0 ){
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
