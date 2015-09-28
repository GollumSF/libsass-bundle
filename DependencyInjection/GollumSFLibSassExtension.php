<?php

namespace GollumSF\LibSassBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\Kernel;

class GollumSFLibSassExtension extends Extension {
	
	public function load(array $configs, ContainerBuilder $container) {
		
		if ($container->getParameter("kernel.environment") == 'dev') {
			$loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
			$loader->load('services.xml');
		}
	}
	
}
