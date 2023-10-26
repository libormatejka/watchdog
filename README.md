# Watchdog

Watchdog is a small package that scans directories and files and checks the quality of files based on rules.

### Installation

```shell
composer require libormatejka/watchdog
```

### Configuration

The __watchdog.neon configuration file__ contains settings for the analysis. You can define:

```shell
parameters:
	enabledRules:
		-list of rules...
	includesFolders:
		- list of folders...
	excludesFolders:
		- list of folders...
	includesFilesType:
		- list of file types...
	fileSettings:
		minFilesSize: 10
		maxFileSize: 512000

# File Type Rules (Optional fields)
fileTypeRules:
	json:
		minFilesSize: 10
		maxFileSize: 10
	yaml:
		maxFileSize: 20
	etc...

```

### Usage
```shell
docker-compose -f .docker/docker-compose.yml run php bin/watchdog analyse --config=PATH TO CONFIG FILE
```
