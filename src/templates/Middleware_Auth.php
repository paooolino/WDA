<?php
/* === DEVELOPER BEGIN */
/**
 *  @desc {{desc}}
 *
 *  @status 0
 */
/* === DEVELOPER END */

namespace WebApp\Middleware;

class Auth {
  
  private $app;
  
  public function __construct($app) {
    $this->app = $app;
  }
  
  public function __invoke($request, $response, $next) {
    return $next($request, $response);
  }
}