<?php declare(strict_types = 1);

namespace Libormatejka\Watchdog\Rules;

use SplFileInfo;

class RuleJson implements RuleInterface
{
	//kontroluje typ souboru...
	public function getPathPatterns(): array
	{

		return ["0"];
	}

	public function processFile(SplFileInfo $file): array
	{
		return [];
	}

	public static function isJson($string) {

		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}

}
