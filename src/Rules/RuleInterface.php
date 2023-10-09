<?php declare(strict_types = 1);

namespace Libormatejka\Watchdog\Rules;

use SplFileInfo;

interface RuleInterface
{

	public function getPathPatterns(): array;


	//array vraci seznam chyb...
	public function processFile(SplFileInfo $file): array;

}
