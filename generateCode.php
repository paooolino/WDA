<?php
require __DIR__ . '/vendor/autoload.php';
use Ifsnop\Mysqldump as IMysqldump;

$appname = "";  // get from command line

$wda = new Wda\Wda($appname);

$config = $wda->parseConfig();

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

