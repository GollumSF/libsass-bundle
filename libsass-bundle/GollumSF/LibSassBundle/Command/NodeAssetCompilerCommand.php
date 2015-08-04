<?php

namespace GollumSF\LibSassBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\HttpKernel\KernelInterface;
use GollumSF\LibSassBundle\Routing\AsseticLoader;


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
		$https        = $this->getContainer ()->getParameter ("assetic.nodesass.compiler.https");
		$port         = $this->getContainer ()->getParameter ("assetic.nodesass.compiler.port");
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
		
		$cmd = "$node $compile ".
			($port     ? "--port $port " : "").
			($https != AsseticLoader::HTTPS_MODE_NONE && $portHttp ? "--portHttps $portHttp ": ""). 
			($https != AsseticLoader::HTTPS_MODE_NONE ? "--https 1 " : "")."
			--nodeSassPath $nodeSassPath 
			--rootPath $rootPath 
			--bundlePath $bundlePath ".
			($outputStyle ? "--outputStyle $outputStyle" : "")." 
			--http_path $http_path 
			--fonts_dir $fonts_dir 
			--images_dir $images_dir$includePaths ".
			($https != AsseticLoader::HTTPS_MODE_NONE && $sslKey  ? "--sslKey $sslKey "   : "").
			($https != AsseticLoader::HTTPS_MODE_NONE && $sslCert ? "--sslCert $sslCert " : "")."
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
