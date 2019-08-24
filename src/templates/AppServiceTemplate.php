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
  
  private $templateName;
  
  public $templateUrl; /* will be initiated by App_init middleware */
  public $baseUrl;     /* will be initiated by App_init middleware */
  
  public function __construct($templateName) {
    $this->templateName = $templateName;
  }
  
  /* === DEVELOPER BEGIN */
  
  public $VERSION = "0.0.1";
  
  /* === DEVELOPER END */
}