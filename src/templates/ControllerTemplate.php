<?php
namespace WebApp\Controller;

/* === DEVELOPER BEGIN */
/**
 *  @desc {{desc}}
 *
 *  @status 0 
 */
/* === DEVELOPER END */
class {{classname}} {
{{deps_members}}
  
  public function __construct({{deps_list}}) {
{{deps_assign}}
  }
  
  public function __invoke($request, $response, $args) {  
{{models_content}}    
    
    return $this->view->render($response, '{{templatename}}.php', [
      "templateUrl" => $this->app->templateUrl,
{{models_vars}}
    ]);
  }
{{viewmodels_content}}
}