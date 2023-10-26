<?php declare(strict_types = 1);

namespace Clown\Watchdog\Rules;

use SplFileInfo;
use Clown\Watchdog\Config\Configuration;

interface RuleInterface
{
	public function __construct(Configuration $config);
	public function getPathPatterns(): array;
	public function processFile(SplFileInfo $file): array;

}
