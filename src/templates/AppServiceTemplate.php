<?php
namespace WebApp;

/* === DEVELOPER BEGIN */
/**
 *  @desc {{desc}}
 *
 *  @status 0 
 */
/* === DEVELOPER END */
class AppService {  
{{deps_members}}
  
  public $templateUrl; /* will be initiated by App_init middleware */
  public $baseUrl;     /* will be initiated by App_init middleware */
  
  public function __construct({{deps_list}}) {
{{deps_assign}}
  }
  
  /* === DEVELOPER BEGIN */
  
  public $VERSION = "0.0.1";
  
  public function getSettings() {
    return $this->settings;
  }
  
  /* === DEVELOPER END */
}