<?php
require __DIR__ . '/vendor/autoload.php';
use Ifsnop\Mysqldump as IMysqldump;

// load config string.
$ini = file_get_contents($argv[1]);

// create wda helper object.
$wda = new Wda\Wda();

// set config in wda.
$wda->loadConfigFromString($ini);

//
// write files:
//

//composer.json.
file_put_contents("composer.json", $wda->getCodeComposerJson());

// .htaccess
file_put_contents(".htaccess", $wda->getCodeHtaccess());

// .gitignore
file_put_contents(".gitignore", $wda->getCodeGitignore());

// index.php
file_put_contents("index.php", $wda->getIndexCode());

// app directory
$wda->makedir("app");

// dependencies.php
file_put_contents("app/dependencies.php", 
  $wda->phpFile(
    $wda->commentLine("Services")
    . $wda->getDefaultDependenciesServices()
    . $wda->getCodeDependenciesServices()
    
    . $wda->commentLine("Middlewares")
    . $wda->getDefaultDependenciesMiddlewares()
    . $wda->getCodeDependenciesMiddlewares()
    
    . $wda->commentLine("Controllers")
    . $wda->getCodeDependenciesControllers()
    
    . $wda->commentLine("Models")
    . $wda->getCodeDependenciesModels()
  )
);

// middleware.php
file_put_contents("app/middleware.php", $wda->getCodeMiddlewarePhp());

// routes.php
file_put_contents("app/routes.php", $wda->phpFile(
  $wda->getCodeRoutesPhp()
));

// settings.php
file_put_contents("settings.php", $wda->getCodeSettingsPhp());

// source directories
$wda->makedir("app/src/Middleware");
$wda->makedir("app/src/Controller");
$wda->makedir("app/src/Model");

// Middleware: AppInit
file_put_contents("app/src/Middleware/AppInit.php", $wda->getCodeMiddlewareAppInit());
// Middleware: Auth
file_put_contents("app/src/Middleware/Auth.php", $wda->getCodeMiddlewareAuth());

// Controllers
$controllers = $wda->getCodeControllers();
foreach ($controllers["pages"] as $c) {
  file_put_contents("app/src/Controller/" . $c["classname"] . ".php", $c["code"]);
}

// Services
$appServiceCode = $wda->getAppServiceCode();
file_put_contents("app/src/AppService.php", $appServiceCode);

$services = $wda->getCodeServices();
foreach ($services as $s) {
  file_put_contents("app/src/" . $s["classname"] . ".php", $s["code"]);
}

// templates
$templates = $wda->getCodeTemplates();
foreach ($templates as $t) {
  $tpl_source_dir = 'templates/default/src'; 
  $tpl_dest_dir = 'templates/default'; 
  $filename = $t["name"] . '.php';
  if (!file_exists($tpl_source_dir . '/' . $filename)) {
    $wda->create_file($tpl_source_dir, $filename, $t["code"]);
  }
  $wda->compile_template($tpl_source_dir, $tpl_dest_dir, $filename, true);
}

// css
$dir = 'templates/default/css';
$filename = 'style.css';
$code = $wda->getCssCode();
$wda->create_file($dir, $filename, $code);

// js
$dir = 'templates/default/js';
$filename = 'scripts.js';
$code = $wda->getJsCode();
$wda->create_file($dir, $filename, $code);

/*
$wda->writeBootstrap();

$wda->writeDependencies($config);

$wda->writeMiddleware();

$wda->writeSettings();

$wda->writeApp();

$wda->writeDb();

$wda->generateRoutes($config["routes"]);

$wda->generateControllers($config["routes"]);

$wda->generateMiddlewares();

$wda->writeDatabaseDump();
*/
