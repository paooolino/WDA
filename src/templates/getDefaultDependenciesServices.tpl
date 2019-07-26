$container['view'] = function ($c) {
  $templatePath = __DIR__ . '/../templates/' . $c->settings["templateName"];
  return new Slim\Views\PhpRenderer($templatePath, [
    "router" => $c->router,
    "templateUrl" => $c->app->templateUrl,
    "VERSION" => $c->app->VERSION
  ]);
};

$container['app'] = function ($c) {
  return new WebApp\AppService($c->settings["templateName"]);
};
