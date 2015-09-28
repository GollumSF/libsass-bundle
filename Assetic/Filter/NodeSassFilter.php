<?php

namespace GollumSF\LibSassBundle\Assetic\Filter;


use Assetic\Asset\AssetInterface;
use Assetic\Exception\FilterException;
use Assetic\Factory\AssetFactory;
use Assetic\Filter\BaseProcessFilter;
use Assetic\Filter\DependencyExtractorInterface;
use Assetic\Util\CssUtils;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * This class is based on Assetic\Filter\Sass\SassFilter and is slightly modified to work with node-sass instead of Ruby.
 */
class NodeSassFilter extends BaseProcessFilter implements DependencyExtractorInterface {

	const STYLE_NESTED = 'nested';
	const STYLE_EXPANDED = 'expanded';
	const STYLE_COMPACT = 'compact';
	const STYLE_COMPRESSED = 'compressed';
	
	/**
	 * @var KernelInterface
	 */
	private $kernel;
	private $nodeSassPath;
	private $nodeBin;
	
	// nodesass options
	private $style;
	private $loadPaths = array ();
	
	private $unixNewlines;
	private $debugInfo;
	private $cacheLocation;
	private $noCache;
	private $force;
	private $quiet;
	private $boring;
	private $noLineComments;
	private $javascriptsDir;
	
	// compass configuration file options
	private $httpPath;
	private $fontsDir;
	private $imagesDir;
	
	private $plugins = array ();
	private $httpImagesPath;
	private $httpFontsPath;
	private $httpGeneratedImagesPath;
	private $generatedImagesPath;
	private $httpJavascriptsPath;
	private $homeEnv = true;

	public function __construct(KernelInterface $kernel, $nodeSassPath = '/usr/bin/node-sass', $nodeBin = null) {
		$this->kernel = $kernel;
		$this->nodeSassPath = $nodeSassPath;
		$this->nodeBin = $nodeBin;
		$this->cacheLocation = sys_get_temp_dir ();
		
		if ('cli' !== php_sapi_name ()) {
			$this->boring = true;
		}
	}


    // sass options setters
    public function setUnixNewlines($unixNewlines)
    {
		$this->unixNewlines = $unixNewlines;
	}
	
	public function setDebugInfo($debugInfo)
    {
        $this->debugInfo = $debugInfo;
    }

    public function setCacheLocation($cacheLocation)
    {
        $this->cacheLocation = $cacheLocation;
    }

    public function setNoCache($noCache)
    {
        $this->noCache = $noCache;
    }

    // compass options setters
    public function setForce($force)
    {
        $this->force = $force;
    }
	public function setStyle($style) {
		$this->style = $style;
	}

    public function setQuiet($quiet)
    {
        $this->quiet = $quiet;
    }

    public function setBoring($boring)
    {
        $this->boring = $boring;
    }

    public function setNoLineComments($noLineComments)
    {
        $this->noLineComments = $noLineComments;
    }
	
	public function setImagesDir($imagesDir) {
		$this->imagesDir = $imagesDir;
	}
	
	public function setJavascriptsDir($javascriptsDir)
    {
        $this->javascriptsDir = $javascriptsDir;
    }
	
	public function setFontsDir($fontsDir) {
		$this->fontsDir = $fontsDir;
	}
	
    // compass configuration file options setters
    public function setPlugins(array $plugins)
    {
        $this->plugins = $plugins;
    }

    public function addPlugin($plugin)
    {
        $this->plugins[] = $plugin;
    }

    public function setLoadPaths(array $loadPaths)
    {
        $this->loadPaths = $loadPaths;
    }

    public function addLoadPath($loadPath)
    {
        $this->loadPaths[] = $loadPath;
    }

    public function setHttpPath($httpPath)
    {
        $this->httpPath = $httpPath;
    }

    public function setHttpImagesPath($httpImagesPath)
    {
        $this->httpImagesPath = $httpImagesPath;
    }

    public function setHttpFontsPath($httpFontsPath)
    {
        $this->httpFontsPath = $httpFontsPath;
    }

    public function setHttpGeneratedImagesPath($httpGeneratedImagesPath)
    {
        $this->httpGeneratedImagesPath = $httpGeneratedImagesPath;
    }

    public function setGeneratedImagesPath($generatedImagesPath)
    {
        $this->generatedImagesPath = $generatedImagesPath;
    }

    public function setHttpJavascriptsPath($httpJavascriptsPath)
    {
        $this->httpJavascriptsPath = $httpJavascriptsPath;
    }

    public function setHomeEnv($homeEnv)
    {
        $this->homeEnv = $homeEnv;
    }

	public function filterLoad(AssetInterface $asset) {
		$root = $asset->getSourceRoot ();
		$path = $asset->getSourcePath ();
		
		error_reporting(E_ALL);
		ini_set("display_errors", 1);
		
		$loadPaths = $this->loadPaths;
		if ($root && $path) {
			$loadPaths [] = realpath ( dirname ( $root . '/' . $path ) );
		}
		$loadPaths [] = realpath ($this->kernel->getRootDir()."/../vendor/igosuki/compass-mixins/lib/" );
		$loadPaths [] = realpath (__DIR__."/../../Resources/scss/");
		
		$tempDir = realpath(sys_get_temp_dir());
		
		$compassProcessArgs = [
			$this->nodeSassPath,
		];
		if (null !== $this->nodeBin) {
			$compassProcessArgs = array_merge(explode(' ', $this->nodeBin), $compassProcessArgs);
		}
		
		foreach ($loadPaths as $includePath) {
			$compassProcessArgs[] = "--include-path";
			$compassProcessArgs[] = $includePath;
		}
		
		if ($this->style) {
			$compassProcessArgs[] = "--output-style";
			$compassProcessArgs[] = $this->style;
		}
		
		$compassProcessArgs[] = $asset->getSourceRoot ()."/".$asset->getSourcePath();
		
		$tempName = tempnam($tempDir, 'assetic_libsass');
		@unlink($tempName);
		
		$compassProcessArgs[] = $tempName;
		
		$pb = $this->createProcessBuilder($compassProcessArgs);
		
		if ($this->homeEnv) {
			// it's not really usefull but... https://github.com/chriseppstein/compass/issues/376
			$pb->setEnv('HOME', sys_get_temp_dir());
			$this->mergeEnv($pb);
		}
		
		$proc = $pb->getProcess ();
		$code = $proc->run ();
		
		if (0 !== $code) {
			@unlink ($tempName);
			throw FilterException::fromProcess ($proc)->setInput ($asset->getSourceRoot ()."/".$asset->getSourcePath());
		}
		
		$content = file_get_contents($tempName);
		
		$content = str_replace("___COMPASS_HTTP_PATH___" , $this->httpPath ? rtrim($this->httpPath, '/') ."/" : "", $content);
		$content = str_replace("___COMPASS_IMAGES_DIR___", $this->httpPath ? rtrim($this->imagesDir, '/')."/" : "", $content);
		$content = str_replace("___COMPASS_FONTS_DIR___" , $this->fontsDir ? rtrim($this->fontsDir, '/') ."/" : "", $content);
		
		@unlink ($tempName);
		$asset->setContent($content);
		
//         if ($this->force) {
//             $pb->add('--force');
//         }

//         if ($this->style) {
//             $pb->add('--output-style')->add($this->style);
//         }

//         if ($this->quiet) {
//             $pb->add('--quiet');
//         }

//         if ($this->boring) {
//             $pb->add('--boring');
//         }

//         if ($this->noLineComments) {
//             $pb->add('--no-line-comments');
//         }

//         // these two options are not passed into the config file
//         // because like this, compass adapts this to be xxx_dir or xxx_path
//         // whether it's an absolute path or not
//         if ($this->imagesDir) {
//             $pb->add('--images-dir')->add($this->imagesDir);
//         }

//         if ($this->javascriptsDir) {
//             $pb->add('--javascripts-dir')->add($this->javascriptsDir);
//         }

//         // options in config file
//         $optionsConfig = array();

//         if (!empty($loadPaths)) {
//             $optionsConfig['additional_import_paths'] = $loadPaths;
//         }

//         if ($this->unixNewlines) {
//             $optionsConfig['sass_options']['unix_newlines'] = true;
//         }

//         if ($this->debugInfo) {
//             $optionsConfig['sass_options']['debug_info'] = true;
//         }

//         if ($this->cacheLocation) {
//             $optionsConfig['sass_options']['cache_location'] = $this->cacheLocation;
//         }

//         if ($this->noCache) {
//             $optionsConfig['sass_options']['no_cache'] = true;
//         }

//         if ($this->httpPath) {
//             $optionsConfig['http_path'] = $this->httpPath;
//         }

//         if ($this->httpImagesPath) {
//             $optionsConfig['http_images_path'] = $this->httpImagesPath;
//         }

//         if ($this->httpFontsPath) {
//             $optionsConfig['http_fonts_path'] = $this->httpFontsPath;
//         }

//         if ($this->httpGeneratedImagesPath) {
//             $optionsConfig['http_generated_images_path'] = $this->httpGeneratedImagesPath;
//         }

//         if ($this->generatedImagesPath) {
//             $optionsConfig['generated_images_path'] = $this->generatedImagesPath;
//         }

//         if ($this->httpJavascriptsPath) {
//             $optionsConfig['http_javascripts_path'] = $this->httpJavascriptsPath;
//         }

//         if ($this->fontsDir) {
//             $optionsConfig['fonts_dir'] = $this->fontsDir;
//         }

//         // options in configuration file
//         if (count($optionsConfig)) {
//             $config = array();
//             foreach ($this->plugins as $plugin) {
//                 $config[] = sprintf("require '%s'", addcslashes($plugin, '\\'));
//             }
//             foreach ($optionsConfig as $name => $value) {
//                 if (!is_array($value)) {
//                     $config[] = sprintf('%s = "%s"', $name, addcslashes($value, '\\'));
//                 } elseif (!empty($value)) {
//                     $config[] = sprintf('%s = %s', $name, $this->formatArrayToRuby($value));
//                 }
//             }

//             $configFile = tempnam($tempDir, 'assetic_compass');
//             file_put_contents($configFile, implode("\n", $config)."\n");
//             $pb->add('--config')->add($configFile);
//         }

//         $pb->add('--sass-dir')->add('')->add('--css-dir')->add('');

// 		$tempName = tempnam($tempDir, 'assetic_compass');
// 		unlink($tempName); // FIXME: don't use tempnam() here
		
// 		// input
// 		$input = $tempName.'.scss';
		
// 		// work-around for https://github.com/chriseppstein/compass/issues/748
// 		if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
// 			$input = str_replace('\\', '/', $input);
// 		}
		
// 		$pb->add($input);
// 		file_put_contents($input, $asset->getContent());
		
// 		// output
// 		$output = $tempName.'.css';
		
// 		if ($this->homeEnv) {
// 			// it's not really usefull but... https://github.com/chriseppstein/compass/issues/376
// 			$pb->setEnv('HOME', sys_get_temp_dir());
// 			$this->mergeEnv($pb);
// 		}
		

//         $proc = $pb->getProcess();
//         $code = $proc->run();

//         if (0 !== $code) {
//             unlink($input);
//             if (isset($configFile)) {
//                 unlink($configFile);
//             }

//             throw FilterException::fromProcess($proc)->setInput($asset->getContent());
//         }

//         $asset->setContent(file_get_contents($output));

//         unlink($input);
//         unlink($output);
//         if (isset($configFile)) {
//             unlink($configFile);
//         }
    }

    public function filterDump(AssetInterface $asset)
    {
    }

    public function getChildren(AssetFactory $factory, $content, $loadPath = null)
    {
        // todo
        return array();
    }
	
	
// 	private $sassPath;
// 	private $style;
// 	private $quiet;
// 	private $cacheLocation;
	
// 	public function __construct($sassPath = '/usr/bin/node-sass') {
// 		$this->sassPath = $sassPath;
// 		$this->cacheLocation = realpath ( sys_get_temp_dir () );
// 	}
	
// 	public function setStyle($style) {
// 		$this->style = $style;
// 	}
	
// 	public function setQuiet($quiet) {
// 		$this->quiet = $quiet;
// 	}
	
// 	public function filterLoad(AssetInterface $asset) {
// 		$sassProcessArgs = array (
// 			$this->sassPath 
// 		);
		
// 		$pb = $this->createProcessBuilder ( $sassProcessArgs );
		
// 		if ($dir = $asset->getSourceDirectory ()) {
// 			$pb->add ( '--include-path' )->add ( $dir );
// 		}
		
// 		if ($this->style) {
// 			$pb->add ( '--output-style' )->add ( $this->style );
// 		}
		
// 		if ($this->quiet) {
// 			$pb->add ( '--quiet' );
// 		}
		
// 		// input
// 		$pb->add ( $input = tempnam ( sys_get_temp_dir (), 'assetic_sass' ) );
// 		file_put_contents ( $input, $asset->getContent () );
// 		var_dump($pb);exit();
// 		$proc = $pb->getProcess ();
// 		$code = $proc->run ();
// 		unlink ( $input );
		
// 		if (0 !== $code) {
// 			throw FilterException::fromProcess ( $proc )->setInput ( $asset->getContent () );
// 		}
		
// 		$asset->setContent ( $proc->getOutput () );
// 	}
	
// 	public function filterDump(AssetInterface $asset) {
// 	}
}