<?php declare(strict_types = 1);

namespace Clown\Watchdog\Rules;

use SplFileInfo;
use Clown\Watchdog\Config\Configuration;

class FileSizeValidationRule implements RuleInterface
{
	private $config;

	/**
     * Constructor for the FileSizeValidationRule class.
     *
     * @param Configuration $config Configuration settings.
     */
    public function __construct(Configuration $config)
    {
        $this->config = $config;
    }

	/**
     * Returns an array of path patterns for the file types to be validated.
     *
     * @return array Array of path patterns.
     */
	public function getPathPatterns(): array
	{
		$fileTypes = $this->config->get("parameters.includesFilesType");
		$patterns = [];
		foreach ($fileTypes as $fileType) {
			$patterns[] = '#\.' . $fileType . '$#';
		}
		return $patterns;
	}

	 /**
     * Processes the given file and returns an array of violations if any.
     *
     * @param SplFileInfo $file File to be processed.
     * @return array Array of violations.
     */
	public function processFile(SplFileInfo $file): array
    {
		$fileType = $file->getExtension();
		$maxFileSize = $this->config->get('parameters.fileSettings.maxFileSize'); // Default value

		$maxFileTypeSizeValue = $this->config->get('fileTypeRules.' . $fileType . '.maxFileSize');
		if (null !== $maxFileTypeSizeValue) {
			$maxFileSize = $maxFileTypeSizeValue;
		}

        $violations = [];
		if( $file->getSize() === 0 ){
			$violations[] = "The file " . $file->getFilename() . " is empty";
		}
        if ( $file->getSize() > $maxFileSize ) {
            $violations[] = "The file " . $file->getFilename() . " exceeds the allowed size of " . $this->formatBytes( $maxFileSize ) . ".";
        }
        return $violations;
    }

	/**
     * Formats the given bytes into a readable string format.
     *
     * @param int $bytes Bytes to be formatted.
     * @param int $precision Precision for the formatted value.
     * @return string Formatted bytes string.
     */
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
