<?php

namespace GollumSF\LibSassBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\HttpKernel\KernelInterface;


class NodeAssetCompilerCommand extends ContainerAwareCommand {
	
	protected function configure()
	{
		$this
			->setName('nodesass:compiler')
			->setDescription('Launch node assets compiler server')
		;
	}
	
	protected function execute(InputInterface $input, OutputInterface $output) {
		
		/* @var $kernel KernelInterface */
		$kernel       = $this->getContainer ()->get ("kernel");
		$node         = $this->getContainer ()->getParameter ("assetic.node.bin");
		$port         = $this->getContainer ()->getParameter ("assetic.nodesass.compiler.port");
		$https        = $this->getContainer ()->getParameter ("assetic.nodesass.compiler.https");
		$portHttp     = $this->getContainer ()->getParameter ("assetic.nodesass.compiler.portHttps");
		$sslKey       = $this->getContainer ()->getParameter ("assetic.nodesass.compiler.sslKey");
		$sslCert      = $this->getContainer ()->getParameter ("assetic.nodesass.compiler.sslCert");
		$node         = $this->getContainer ()->getParameter ("assetic.node.bin");
		$outputStyle  = $this->getContainer ()->getParameter ("assetic.filter.nodesass.style");
		$http_path    = $this->getContainer ()->getParameter ("assetic.filter.nodesass.http_path");
		$fonts_dir    = $this->getContainer ()->getParameter ("assetic.filter.nodesass.fonts_dir");
		$images_dir   = $this->getContainer ()->getParameter ("assetic.filter.nodesass.images_dir");
		$bundlePath   = realpath($kernel->getBundle("GollumSFLibSassBundle")->getPath());
		$rootPath     = realpath($kernel->getRootDir());
		$compile      = $bundlePath."/Resources/nodejs/assets_compiler_httpserver.js";
		$nodeSassPath = realpath(dirname($this->getContainer ()->getParameter ("assetic.nodesass.bin"))."/..");
		
		$includePaths = "";
		foreach ($this->getContainer ()->getParameter ("assetic.filter.nodesass.load_paths") as $includePath) {
			$includePaths .= " --includePaths $includePath";
		}
		
		$cmd = "$node $compile 
			--port $port ".
			($https ? "--https 1 " : "")."
			--portHttps $portHttp 
			--nodeSassPath $nodeSassPath 
			--rootPath $rootPath 
			--bundlePath $bundlePath ".
			($outputStyle ? "--outputStyle $outputStyle" : "")." 
			--http_path $http_path 
			--fonts_dir $fonts_dir 
			--images_dir $images_dir$includePaths
			--sslKey $sslKey
			--sslCert $sslCert
		";
		
		$cmd = str_replace (["\n", "\t"], " ", $cmd);
		
		echo "Start compilation server: $cmd\n";
		$process = new Process($cmd, null, null, null, 0);
		$process->run(function ($type, $buffer) { echo $buffer; });
		if (!$process->isSuccessful()) {
			throw new \RuntimeException(sprintf('An error occurred when executing the "%s" command.', escapeshellarg($cmd)));
		}
		
	}
	
}
