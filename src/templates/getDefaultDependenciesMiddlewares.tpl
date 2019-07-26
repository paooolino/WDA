$container['WebApp\Middleware\AppInit'] = function ($c) {
  return new WebApp\Middleware\AppInit($c->app);
};
