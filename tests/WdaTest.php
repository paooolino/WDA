<?php

use PHPUnit\Framework\TestCase;

final class WdaTest extends TestCase {
  private $app;
  
  protected function setUp():void {
    if (!file_exists('temp'))
      mkdir('temp');
    
    if (!file_exists('temp/testapp')) {
      mkdir('temp/testapp');
      shell_exec("php generatecode.php tests/samples/sample.ini temp/testapp");
      chdir('temp/testapp');
      shell_exec('composer install');
    }
  }
  
  public function testCreate() {
    // mock the index.php and run app to analyze responses
  }
  
  public static function rrmdir($dir) { 
    if (is_dir($dir)) { 
      $objects = scandir($dir); 
      foreach ($objects as $object) { 
        if ($object != "." && $object != "..") { 
          if (is_dir($dir."/".$object))
            self::rrmdir($dir."/".$object);
          else
            unlink($dir."/".$object); 
        } 
      }
      rmdir($dir); 
    } 
  }
  
  public static function tearDownAfterClass():void {
    //self::rrmdir('tests/testapp');
  }
}
