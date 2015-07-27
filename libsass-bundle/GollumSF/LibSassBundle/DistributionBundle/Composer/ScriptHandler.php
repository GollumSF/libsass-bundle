<?php

namespace GollumSF\LibSassBundle\DistributionBundle\Composer;

class ScriptHandler {
	
	public static function submoduleInstall(CommandEvent $event) {
		try {
			
			$vendorDir = $options['symfony-vendor-dir'];
			echo shell_exec("git -C $vendorDir/gollumsf/libsass submodule update --init");
			
		} catch (\Exception $e) {
			echo $e->getTraceAsString();
			throw $e;
		}
	}
	
	public static function submoduleUpdate(CommandEvent $event) {
		try {
			
			$vendorDir = $options['symfony-vendor-dir'];
			echo shell_exec("git -C $vendorDir/gollumsf/libsass submodule update");
			
			$oldDir = realpath(getcwd());
			chdir("$vendorDir/gollumsf/libsass/node-sass");
	// 		echo shell_exec("npm install");
			chdir($oldDir);
			
		} catch (\Exception $e) {
			echo $e->getTraceAsString();
			throw $e;
		}
	}
	
}