
var fs = require('fs');
var sass = require('../node-sass/lib/index.js');

var outputStyle  = "expanded";
var rootPath     = "/home/dduboeuf/Works/Glowbl/git/Glowbl_portal";
var bundlePath   = rootPath+"/vendor/gollumsf/libsass/libsass-bundle/GollumSF/LibSassBundle";
var includePaths = [
	rootPath+"/src/Glowbl/CommonBundle/Resources/public/css/"
];

var ___COMPASS_HTTP_PATH___  = "//www.devglowbl.com/web";
var ___COMPASS_FONT_DIR___   = "fonts";
var ___COMPASS_IMAGES_DIR___ = "images";

var http = require('http');

http.createServer(function (req, res) {
	
	var assetPath = req.url;
	
	if (!fs.existsSync(assetPath)) {
		res.writeHead(404, {"Content-Type": "text/plain"});
		res.write("404 File not found: "+assetPath+"\n");
		res.end();
		
		console.error ("404 File not found: "+assetPath);
		return;
	}
	
	if (assetPath.substr(-3) == '.js') {
		
		fs.readFile(assetPath, "utf8", function (err,data) {
			res.writeHead(200, {"Content-Type": "text/javascript"});
			res.write(data);
			res.end();
		});
		return
	}
	
	var includeFinal = [
		bundlePath+"/Resources/compass/include/",
		bundlePath+"/Resources/compass/compass-mixins/lib/"
	];
	for (var i = 0; i < includePaths.length; i++) {
		includeFinal.push(includePaths[i]);
	}
	
	var options = ({
		file: assetPath,
		outputStyle: outputStyle,
		includePaths: includeFinal
	});
	
	console.log ("Compile: "+assetPath);
	sass.render(options, function(err, result) {
		if (err) {
			console.error (err);
			res.writeHead(500, {"Content-Type": "text/plain"});
			res.write(err+"\n");
		} else {
			res.writeHead(200, {'Content-Type': 'text/css'});
			
			content = result.css.toString();
			content = content.replace(new RegExp('___COMPASS_HTTP_PATH___', 'g') , ___COMPASS_HTTP_PATH___  ? ___COMPASS_HTTP_PATH___ +'/' : '');
			content = content.replace(new RegExp('___COMPASS_FONT_DIR___', 'g')  , ___COMPASS_FONT_DIR___   ? ___COMPASS_FONT_DIR___  +'/' : '');
			content = content.replace(new RegExp('___COMPASS_IMAGES_DIR___', 'g'), ___COMPASS_IMAGES_DIR___ ? ___COMPASS_IMAGES_DIR___+'/' : '');
			
			res.write (content);
		}
		res.end();
	});
	
}).listen(9999);