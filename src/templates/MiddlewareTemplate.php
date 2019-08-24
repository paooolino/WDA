<?php
/* === DEVELOPER BEGIN */
/**
 *  @desc {{desc}}
 *
 *  @status 0
 */
/* === DEVELOPER END */

namespace WebApp\Middleware;

class {{classname}} {
{{deps_members}}
    
  public function __construct({{deps_list}}) {
{{deps_assign}}
  }
  
  /* === DEVELOPER BEGIN */
  public function __invoke($request, $response, $next) {
    return $next($request, $response);
  } 
  /* === DEVELOPER END */
}
