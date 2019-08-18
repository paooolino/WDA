<?php

use PHPUnit\Framework\TestCase;

final class WdaTest extends TestCase {
  private $app;
  private $dir = __DIR__ . '/testapp';

  // funzione di utilità per la rimozione di tutto il contenuto di una dir
  private function rrmdir($dir) { 
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
  
  // preparazione del test
  private function prepareTest() {
    mkdir($this->dir);
    shell_exec("php generatecode.php tests/samples/sample.ini " . $this->dir);
    chdir($this->dir);
    shell_exec('composer install');
  }
  
  // rimozione completa dei file creati durante il test
  private function cleanupTest() {
    $this->rrmdir($this->dir);
  }
  
  protected function setUp():void {
    // chiamare il prepareTest
    if (file_exists($this->dir)) {
      echo "Skipping installation. Please remove " . $this->dir . " directory if you want a complete test.";
    } else {
      $this->prepareTest();
    }
    require $this->dir . '/vendor/autoload.php';
    $settings = require $this->dir . '/settings.php';
    $app = new Slim\App($settings);
    $container = $app->getContainer();
    require $this->dir . '/app/dependencies.php';
    require $this->dir . '/app/middleware.php';
    require $this->dir . '/app/routes.php';
    $this->app = $app;
  }
  
  public function testHomeResponds() {
    // mock the index.php and run app to analyze responses
    $env = \Slim\Http\Environment::mock([
      'REQUEST_METHOD' => 'GET',
      'REQUEST_URI' => '/'
    ]);
    $request = \Slim\Http\Request::createFromEnvironment($env);
    $response = new \Slim\Http\Response();
    $response = $this->app->process($request, $response);
    
    $expected = 'Please edit the template source file in /templates/default/src/home';
    $this->assertStringContainsString($expected, $response->getBody()->__toString());
  }
  
  public static function tearDownAfterClass():void {
    //self::rrmdir('tests/testapp');
  }
}
