# libsass-bundle
Add libsabb filter for AsseticBundle into Symfony2


##For composer :

<pre>
{
	"require" : {
		"gollumsf/libsass" : "1.0.0"
	},
	
	"repositories" : [{
			"type" : "package",
			"package" : {
				"name" : "gollumsf/libsass",
				"version" : "1.0.0",
				"autoload" : {
					"psr-0" : {
						"GollumSF" : "libsass-bundle"
					}
				},
				"source" : {
					"url" : "https://github.com/GollumSF/libsass-bundle.git",
					"type" : "git",
					"reference" : "master"
				}
			}
		}
	],
	
	"scripts" : {
		"post-install-cmd" : [
			"GollumSF\\LibSassBundle\\DistributionBundle\\Composer\\ScriptHandler::submoduleInstall"
		],
		"post-update-cmd" : [
			"GollumSF\\LibSassBundle\\DistributionBundle\\Composer\\ScriptHandler::submoduleUpdate"
		]
	}
}
</pre>

##Configuration

<pre>
	
	assetic:
		node:
			bin: ~  # (optional) default: /usr/bin/node
		nodesass:
			bin: ~  # (optional) default: "%kernel.root_dir%/../vendor/gollumsf/libsass/node-sass/bin/node-sass"
		filters:
			nodesass:
				resource: '%kernel.root_dir%/../vendor/gollumsf/libsass/libsass-bundle/GollumSF/LibSassBundle/Resources/config/nodesass.xml'
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


