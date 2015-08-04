<?php

namespace GollumSF\LibSassBundle\Routing;

use Symfony\Bundle\AsseticBundle\Routing\AsseticLoader as AsseticLoaderBase;
use Assetic\Factory\LazyAssetManager;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\Request;

class AsseticLoader extends AsseticLoaderBase {

	const HTTPS_MODE_NONE   = "none";
	const HTTPS_MODE_DETECT = "detect";
	const HTTPS_MODE_FULL   = "full";
	
	
	/**
	 * @var AsseticLoaderBase
	 */
	private $serviceOrigin;
	
	private $use       = false;
	private $host      = "127.0.0.1";
	private $port      = 7979;
	private $portHttps = 8989;
	private $https     = self::HTTPS_MODE_NONE;
	
	/**
	 * @var ContainerInterface
	 */
	private $container;
	
	public function __construct(LazyAssetManager $am, $container, $serviceOrigin) {
		parent::__construct($am);
		$this->container = $container;
		$this->serviceOrigin = $serviceOrigin;
		
		$this->use       = $container->getParameter("assetic.nodesass.compiler.usenodeserver");
		$this->host      = $container->getParameter("assetic.nodesass.compiler.host");
		$this->port      = $container->getParameter("assetic.nodesass.compiler.port");
		$this->portHttps = $container->getParameter("assetic.nodesass.compiler.portHttps");
		$this->https     = $container->getParameter("assetic.nodesass.compiler.https");
		
	}

	public function load($routingResource, $type = null) {
		
		$routes = parent::load($routingResource, $type);
		
		if (!$this->use) {
			return $routes;
		}
		
		/* @var $request Request */
		$request = $this->container->get("request");
			
		foreach ($this->am->getNames() as $name) {
			$asset = $this->am->get($name);
			$formula = $this->am->getFormula($name);
			
			$debug = isset($formula[2]['debug']) ? $formula[2]['debug'] : $this->am->isDebug();
			$combine = isset($formula[2]['combine']) ? $formula[2]['combine'] : !$debug;
			
			if (!$combine && $debug) {
				$i = 0;
				foreach ($asset as $leaf) {
					
					$routeName = "_assetic_".$name."_".$i++;
					$route = $routes->get($routeName);
					if (
						$route && 
						($pos = strpos($leaf->getSourcePath(), '.')) !== false && 
						in_array(substr($leaf->getSourcePath(), $pos), ['.js', '.css', '.scss'])
					) {
						if (
							($this->https == self::HTTPS_MODE_DETECT && $request->isSecure()) ||
							$this->https == self::HTTPS_MODE_FULL
						) {
							$route->setHost($this->host.($this->portHttps ? ":".$this->portHttps : ""));
							if ($this->portHttps) {
								$route->setSchemes("https");
							}
							$route->setPath($leaf->getSourceRoot()."/".$leaf->getSourcePath()."?".$route->getPath());
						} else {
							$route->setHost($this->host.":".($this->port ? ":".$this->port : ""));
							if ($this->port) {
								$route->setSchemes("http");
							}
							$route->setPath($leaf->getSourceRoot()."/".$leaf->getSourcePath()."?".$route->getPath());
						}
					}
				}
			}
		}
		
		return $routes;
	}
	
}
