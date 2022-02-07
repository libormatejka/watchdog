<?php declare(strict_types = 1);

namespace Finie\Watchdog\Rules;

use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RuleJson implements RuleInterface
{
	public function getPathPatterns(): array
	{

		return [];
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
