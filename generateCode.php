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

// dependencies.php
$wda->makedir("app");
file_put_contents("app/dependencies.php", 
  $wda->phpFile(
    $wda->commentLine("Services")
    . $wda->getCodeDependenciesServices()
    
    . $wda->commentLine("Middlewares")
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
file_put_contents("app/settings.php", $wda->getCodeSettingsPhp());

/*
$wda->writeBootstrap();

$wda->writeDependencies($config);

$wda->writeMiddleware();

$wda->writeSettings();

$wda->writeApp();

$wda->writeDb();

$wda->writeCss();

$wda->writeJs();

$wda->generateRoutes($config["routes"]);

$wda->generateControllers($config["routes"]);

$wda->generateMiddlewares();

$wda->generateServices();

$wda->generateTemplates();

$wda->copyDeveloperAssistant();

$wda->writeDatabaseDump();
*/
