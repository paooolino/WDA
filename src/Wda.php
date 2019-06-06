<?php
namespace Wda;

class Wda {
  //private $root;
  //private $AppName;
  
  //public function __construct($root, $AppName) {
    //$this->root = $root;
    //$this->AppName = $AppName;
  //}
  
  public function __construct() {
  }
  
  public function getCodeComposerJson() {
    return <<<END_OF_CODE
{
    "require": {
        "slim/slim": "^3.12",
        "slim/php-view": "^2.2",
        "ifsnop/mysqldump-php": "^2.7"
    },
    "autoload": {
      "psr-4": {
        "WebApp\\": "WebApp/src/"
      }
    },
    "require-dev": {
        "phpunit/phpunit": "^8"
    }
}
END_OF_CODE;
  }
  
  public function getCodeHtaccess() {
    return <<<END_OF_CODE
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
END_OF_CODE;
  }
  
  public function getCodeGitignore() {
    return <<<END_OF_CODE
/vendor/
END_OF_CODE;
  }
  
  public function getCodeDependenciesService($ini) {
    
  }
  /*
  public function createFile($dir, $filename, $code, $force=true) {
    $dir = $this->root . '/' . $this->AppName . $dir;

    if (!is_dir($dir))
      mkdir($dir, 0777, true);  
    
    $file = $dir . '/' . $filename;
    
    $code = $this->preserve_developer_code($file, $code);
    
    if ($force || !file_exists($file))
      file_put_contents($file, $code);
  }
  
  public function preserve_developer_code($file, $code) {
    // se il file non esiste mantengo il codice così com'è
    if (!file_exists($file))
      return $code;
    
    // trovo i pezzi di codice da preservare dal file originale
    $file_content = file_get_contents($file);
    $start = ("\/\* === DEVELOPER BEGIN \*\/");
    $end = ("\/\* === DEVELOPER END \*\/");
    $preserve_matches = [];
    preg_match_all("/$start(.*?)$end/s", $file_content, $matches);
    
    if (count($matches[0]) > 0) {
      // metto dei segnaposto nel nuovo codice
      $code = preg_replace("/$start(.*?)$end/s", "{{DEVELOPER_CODE}}", $code);
      
      // sostituisco i segnaposto con il codice da preservare
      foreach ($matches[0] as $match) {
        $code = $this->replace_first_occurrence("{{DEVELOPER_CODE}}", $match, $code);
      }
    }
    
    return $code;
  }
  
  public function replace_first_occurrence($search, $replace, $string) {
    $pos = strpos($string, $search);
    if ($pos !== false) {
      $string = substr_replace($string, $replace, $pos, strlen($search));
    }
    return $string;
  }
  
  public function writeBootstrap() {
  }
  */
}