<?php
namespace WebApp;

/* === DEVELOPER BEGIN */
/**
 *  @desc {{desc}}
 *
 *  @status 0 
 */
/* === DEVELOPER END */
class ViewService {

  public static function create({{deps_list}}) {
    /* === DEVELOPER BEGIN */
    
    // create your instance here
    $templatePath = __DIR__ . '/../../templates/' . $app->getSettings()["templateName"];
    return new \Slim\Views\PhpRenderer($templatePath, [
      // Let the router be available in all templates for rapid linking through
      //  $router->pathFor()
      "router" => $router,
      // Use the templateUrl for linking static js/css resources
      //  this value is set by the App_init middleware
      "templateUrl" => $app->templateUrl,
      // Version number may be appended to static js/css resources for cache
      //  bursting
      "VERSION" => $app->VERSION
    ]);
    
    /* === DEVELOPER END */
  }
  
}