<?php
require __DIR__ . '/vendor/autoload.php';

// Instantiate the Slim App
$settings = require __DIR__ . '/settings.php';
$app = new Slim\App($settings);

// get the DIC container
$container = $app->getContainer();

// Set up dependencies
require __DIR__ . '/app/dependencies.php';

// App middleware
require __DIR__ . '/app/middleware.php';

// App routes
require __DIR__ . '/app/routes.php';

$app->run();