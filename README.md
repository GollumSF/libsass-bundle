# libsass-bundle
Add libsabb filter for AsseticBundle into Symfony2


##For composer :

<pre>
{
	"require" : {
		"gollumsf/libsass-bundle" : "1.0.0"
	},
	
	"scripts" : {
		"post-update-cmd" : [
			"GollumSF\\LibSassBundle\\DistributionBundle\\Composer\\ScriptHandler::submoduleUpdate"
		]
	}
}
</pre>


##Register the bundle with your kernel:

```php
// in AppKernel::registerBundles()
$bundles = array(
    // ...
    new GollumSF\LibSassBundle\GollumSFLibSassBundle(),
    // ...
);
```

##Configuration

<pre>
	
	assetic:
		node:
			bin: ~  # (optional) default: /usr/bin/node
		nodesass:
			bin: ~  # (optional) default: "%kernel.root_dir%/../vendor/sass/node-sass/bin/node-sass"
		filters:
			nodesass:
				resource: '%kernel.root_dir%/../vendor/gollumsf/libsass-bundle/GollumSF/LibSassBundle/Resources/config/nodesass.xml'
				apply_to: "\.scss$" # (optional)
				style: ~            # (optional) default: expanded
				images_dir: ~       # (optional) default: images
				fonts_dir:  ~       # (optional) default: fonts
				http_path: ~        # (optional) 
				load_paths: []      # (optional) 
				
	parameters:
		assetic.nodesass.compiler.usenodeserver: ~     # (optional) default: false       Enabled generate assets routes for node compiler server (Only dor DEV)
		assetic.nodesass.compiler.host: 127.0.0.1      # (optional) default: "127.0.0.1" Host for node compiler server
		assetic.nodesass.compiler.https: "none"        # (optional) default: "none"      must be "none", "detect", "full" for http/https route generated
		assetic.nodesass.compiler.port: 7979           # (optional) default: 7979        HTTP port for compiler server
		assetic.nodesass.compiler.portHttps: 8989      # (optional) default: 8989        HTTPS port for compiler server
					
</pre>

##Command

Launch compiler dev server if  
<pre>	
php app/console nodesass:compiler
</pre>


