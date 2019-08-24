<?php
/* === DEVELOPER BEGIN */
/**
 *  @desc {{desc}}
 *
 *  @status 0
 */
/* === DEVELOPER END */

namespace WebApp\Middleware;

class App_initMiddleware {
{{deps_members}}
  
  public function __construct({{deps_list}}) {
{{deps_assign}}
  }
  
  /* === DEVELOPER BEGIN */
  public function __invoke($request, $response, $next) {  
    $this->app->baseUrl = $request->getUri()->getBaseUrl();
    $this->app->templateUrl = $this->app->baseUrl 
      . '/templates'
      . '/' . $this->app->templateName;
      
    return $next($request, $response);
  }
  /* === DEVELOPER END */
}
