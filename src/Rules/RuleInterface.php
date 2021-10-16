<?php declare(strict_types = 1);

namespace Finie\Watchdog\Rules;

use SplFileInfo;

interface RuleInterface
{

	public function getPathPatterns(): array;

	public function processFile(SplFileInfo $file): array;

}
