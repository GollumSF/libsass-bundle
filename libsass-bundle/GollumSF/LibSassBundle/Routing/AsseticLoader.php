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
	
// 		// routes
// 		foreach ($this->am->getNames() as $name) {
// 			$asset = $this->am->get($name);
// 			$formula = $this->am->getFormula($name);
	
// 			$this->loadRouteForAsset($routes, $asset, $name);
	
// 			$debug = isset($formula[2]['debug']) ? $formula[2]['debug'] : $this->am->isDebug();
// 			$combine = isset($formula[2]['combine']) ? $formula[2]['combine'] : !$debug;
	
// 			// add a route for each "leaf" in debug mode
// 			if (!$combine) {
// 				$i = 0;
// 				foreach ($asset as $leaf) {
// 					$this->loadRouteForAsset($routes, $leaf, $name, $i++);
// 				}
// 			}
// 		}

        var_dump($routes);exit();
		return $routes;
	}
	
	/**
	 * Loads a route to serve an supplied asset.
	 *
	 * The fake front controller that {@link UseControllerWorker} adds to the
	 * target URL will be removed before set as a route pattern.
	 *
	 * @param RouteCollection $routes The route collection
	 * @param AssetInterface  $asset  The asset
	 * @param string          $name   The name to use
	 * @param integer         $pos    The leaf index
	 */
	private function loadRouteForAsset(RouteCollection $routes, AssetInterface $asset, $name, $pos = null)
	{
		$defaults = array(
			'_controller' => 'assetic.controller:render',
			'name'        => $name,
			'pos'         => $pos,
		);
	
		// remove the fake front controller
		$pattern = str_replace('_controller/', '', $asset->getTargetPath());
	
		if ($format = pathinfo($pattern, PATHINFO_EXTENSION)) {
			$defaults['_format'] = $format;
		}
	
		$route = '_assetic_'.$name;
		if (null !== $pos) {
			$route .= '_'.$pos;
		}
	
		$routes->add($route, new Route($pattern, $defaults));
	}
}
