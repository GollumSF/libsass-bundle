# libsass-bundle
Add libsabb filter for AsseticBundle into Symfony2


##For composer :

<pre>
{
	"require" : {
		"gollumsf/libsass" : "1.0.0"
	},
	"autoload" : {
		"psr-0" : {
			"" : "src/",
			"GollumSF" : "vendor/gollumsf/libsass/libsass-bundle"
		}
	},
	
	"repositories" : [{
			"type" : "package",
			"package" : {
				"name" : "gollumsf/libsass",
				"version" : "1.0.0",
				"autoload" : {
					"psr-0" : {
						"GollumSF" : "vendor/gollumsf/libsass/libsass-bundle"
					}
				},
				"source" : {
					"url" : "https://github.com/GollumSF/libsass-bundle.git",
					"type" : "git",
					"reference" : "master"
				}
			}
		}
	]
}
</pre>
