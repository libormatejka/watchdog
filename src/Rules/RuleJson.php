<?php declare(strict_types = 1);

namespace Finie\Watchdog\Rules;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RuleJson extends Command
{
	/** @var array<mixed, mixed> */
	protected array $config = [];

	/**
	 * @param array<mixed, mixed> $config
	 */
	public function __construct(array $config)
	{
		parent::__construct();
		$this->config = $config;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);

		$jsonFolders = $this->config['parameters']['folders']['json'] ?? [];

		if (!empty($jsonFolders)) {
			$io->section('JSONs');
		}

		foreach( $jsonFolders as $jsonFolder){

			if (is_dir($jsonFolder)) {

				if ($dh = opendir($jsonFolder)) {
					while (($file = readdir($dh)) !== false) {

						if($file != "." && $file != ".."){

							if( strpos($file, ".json") !== false){

								if($this->isJson(file_get_contents($jsonFolder."/".$file)) === FALSE){
									$io->error('Filename: '.$file);
								}else{
									print('[OK] Filename: '.$file);
								}

								$io->newLine();

							}
						}
					}
					closedir($dh);
				}
			}
		}



		return 0;
	}

	public static function isJson($string) {

		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}

}
