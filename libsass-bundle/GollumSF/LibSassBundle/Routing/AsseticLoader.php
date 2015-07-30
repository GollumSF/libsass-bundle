<?php

namespace GollumSF\LibSassBundle\Routing;

use Symfony\Bundle\AsseticBundle\Routing\AsseticLoader as AsseticLoaderBase;
use Assetic\Factory\LazyAssetManager;
use Symfony\Component\Routing\RouteCollection;

class AsseticLoader extends AsseticLoaderBase {
	
	public function __construct(LazyAssetManager $am) {
		$this->am = $am;
	}

	public function load($routingResource, $type = null)
	{
		$routes = parent::load($routingResource, $type);
			
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
						$route->setHost("www.devglowbl.com:8989");
						$route->setPath($leaf->getSourceRoot()."/".$leaf->getSourcePath()."?".$route->getPath());
					}
				}
			}
		}
		
		return $routes;
	}
	
}
