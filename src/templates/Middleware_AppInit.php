<?php
/* === DEVELOPER BEGIN */
/**
 *  @desc {{desc}}
 *
 *  @status 0
 */
/* === DEVELOPER END */

namespace WebApp\Middleware;

class AppInit {
  
  private $app;
  
  public function __construct($app) {
    $this->app = $app;
  }
  
  public function __invoke($request, $response, $next) {  
    $this->app->baseUrl = $request->getUri()->getBaseUrl();
    $this->app->templateUrl = $this->app->baseUrl 
      . '/templates'
      . '/' . $this->app->templateName;
      
    return $next($request, $response);
  }
}