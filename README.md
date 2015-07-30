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
			bin: ~  # default: /usr/bin/node
		nodesass:
			bin: ~  # default: "%kernel.root_dir%/../vendor/gollumsf/libsass/node-sass/bin/node-sass"
		filters:
			nodesass:
				resource: '%kernel.root_dir%/../vendor/gollumsf/libsass/libsass-bundle/GollumSF/LibSassBundle/Resources/config/nodesass.xml'
				apply_to: "\.scss$"
				style: ~       # default: expanded
				images_dir: ~  # default: images
				fonts_dir:  ~  # default: fonts
				http_path: ~
				load_paths: []
</pre>

