<?php

namespace GollumSF\LibSassBundle\DistributionBundle\Composer;
use Symfony\Component\Process\Process;
use Composer\Script\CommandEvent;
use Symfony\Component\Filesystem\Filesystem;

class ScriptHandler {
	
	public static function submoduleUpdate(CommandEvent $event) {
		self::submoduleInstall($event);
	}
	
	public static function submoduleInstall(CommandEvent $event) {
		
		//set_time_limit(0);
		
		$oldDir = realpath(getcwd());
		chdir("vendor/gollumsf/libsass");

		self::executeCommand("git --version");
		self::executeCommand("git submodule update --init");
		self::executeCommand("git submodule foreach git reset --hard");

		chdir("node-sass");
		if (file_exists("node_modules")) {
			echo "Remove dir node_modules\n";
			(new Filesystem())->remove("node_modules");
		}
		self::executeCommand("npm install");
		chdir($oldDir);
			
	}
	
	protected static function executeCommand($cmd, $timeout = 300) {
		
		echo "Run => ".$cmd.":\n";
		$process = new Process($cmd, null, null, null, $timeout);
		$process->run(function ($type, $buffer) { echo $buffer; });
		if (!$process->isSuccessful()) {
			throw new \RuntimeException(sprintf('An error occurred when executing the "%s" command.', escapeshellarg($cmd)));
		}
	}
}