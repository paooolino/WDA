<?php

use PHPUnit\Framework\TestCase;

final class WdaTest extends TestCase {
  
  private $wda;
  
  protected function setUp():void {
    $this->wda = new \Wda\Wda();
  }
  
  public function testCodeComposerJson() {
    $code = $this->wda->getCodeComposerJson();
    $expected = $this->template("composer.json");
    $this->assertEquals($expected, $code);
  }
  
  public function testCodeHtaccess() {
    $code = $this->wda->getCodeHtaccess();
    $expected = $this->template(".htaccess");
    $this->assertEquals($expected, $code);
  }
  
  public function testCodeGitignore() {
    $code = $this->wda->getCodeGitignore();
    $expected = $this->template(".gitignore");
    $this->assertEquals($expected, $code);
  }
  
  public function testCodeDependenciesPhp() {
    //
    // services
    //
    $ini = $this->sample("services.ini");
    $this->wda->loadConfigFromString($ini);
    $code = $this->wda->getCodeDependenciesServices();
    $expected = $this->sample("dependencies-services.txt");
    $this->assertEquals($expected, $code);
    
    //
    // middlewares
    //
    $ini = $this->sample("middlewares.ini");
    $this->wda->loadConfigFromString($ini);
    $code = $this->wda->getCodeDependenciesMiddlewares();
    $expected = $this->sample("dependencies-middlewares.txt");
    $this->assertEquals($expected, $code);
    
    //
    // controllers
    //
    $ini = $this->sample("controllers.ini");
    $this->wda->loadConfigFromString($ini);
    $code = $this->wda->getCodeDependenciesControllers();
    $expected = $this->sample("dependencies-controllers.txt");
    $this->assertEquals($expected, $code);
    
    //
    // models
    //
    $ini = $this->sample("models.ini");
    $this->wda->loadConfigFromString($ini);
    $code = $this->wda->getCodeDependenciesModels();
    $expected = $this->sample("dependencies-models.txt");
    $this->assertEquals($expected, $code);
  }
  
  public function testCodeMiddlewarePhp() {
    $code = $this->wda->getCodeMiddlewarePhp();
    $expected = $this->template("middleware.php");
    $this->assertEquals($expected, $code);
  }
  
  public function testCodeRoutesPhp() {
    $ini = $this->sample("controllers.ini");
    $this->wda->loadConfigFromString($ini);
    $code = $this->wda->getCodeRoutesPhp();
    $expected = $this->sample("routes.txt");
    $this->assertEquals($expected, $code);
  }
  
  public function testCodeSettingsPhp() {
    $code = $this->wda->getCodeSettingsPhp();
    $expected = $this->template("settings.php");
    $this->assertEquals($expected, $code);
  }
  
  public function testCodeControllers() {
    $ini = $this->sample("controllers.ini");
    $this->wda->loadConfigFromString($ini);
    $classes = $this->wda->getControllerClasses();
    
    $expected = $this->sample("HomeController.txt");
    $this->assertEquals($expected, $classes[0]);
    
    $expected = $this->sample("LoginController.txt");
    $this->assertEquals($expected, $classes[1]);
    
    $expected = $this->sample("Login_actionController.txt");
    $this->assertEquals($expected, $classes[2]);
  }
  
  public function testCodeAppInitMiddleware() {
    $code = $this->wda->getCodeAppInitMiddleware();
    $expected = $this->template("AppInitMiddleware.php");
    $this->assertEquals($expected, $code);
  }
  
  public function testCodeAuthMiddleware() {
    $code = $this->wda->getCodeAppInitMiddleware();
    $expected = $this->template("AuthMiddleware.php");
    $this->assertEquals($expected, $code);
  }
  
  public function testCodeModels() {
    $ini = $this->sample("models.ini");
    $this->wda->loadConfigFromString($ini);
    $classes = $this->wda->getModelClasses();
    
    $expected = $this->sample("User_by_username_passwordModel.txt");
    $this->assertEquals($expected, $classes[0]);
    
    $expected = $this->sample("MessageModel.txt");
    $this->assertEquals($expected, $classes[1]);
  }
  
  public function testCodeAppService() {
    $code = $this->wda->getCodeAppService();
    $expected = $this->template("App.php");
    $this->assertEquals($expected, $code);
  }
  
  public function testCodeDBService() {
    $code = $this->wda->getCodeDBService();
    $expected = $this->template("DB.php");
    $this->assertEquals($expected, $code);
  }
  
  public function testCodeServices() {
    $ini = $this->sample("services.ini");
    $this->wda->loadConfigFromString($ini);
    $classes = $this->wda->getServicesClasses();
    
    $expected = $this->sample("Auth.txt");
    $this->assertEquals($expected, $classes[0]);
    
    $expected = $this->sample("Service2.txt");
    $this->assertEquals($expected, $classes[1]);
    
    $expected = $this->sample("Service3.txt");
    $this->assertEquals($expected, $classes[2]);
  }
  
  public function testCodeTemplates() {
    $ini = $this->sample("controllers.ini");
    $this->wda->loadConfigFromString($ini);
    $templates = $this->wda->getMainTemplatesCode();
    
    $expected = $this->sample("home.tpl");
    $this->assertEquals($expected, $templates[0]);
    
    $expected = $this->sample("login.tpl");
    $this->assertEquals($expected, $templates[1]);
  }
  
  public function testCodeCss() {
    $code = $this->wda->getCodeCSS();
    $expected = $this->template("style.css");
    $this->assertEquals($expected, $code);
  }
  
  public function testCodeJs() {
    $code = $this->wda->getCodeJS();
    $expected = $this->template("main.js");
    $this->assertEquals($expected, $code);
  }
  
  public function testCodeDeveloperAssistant() {
    $code = $this->wda->getCodeJS();
    $expected = $this->template("developer_assistant.php");
    $this->assertEquals($expected, $code);
  }
  
  public function testDatabaseDump() {
  }
  
  public function testCreateFile() {
    return;
    
    $this->wda->createFile('', 'test.php', 'test string');
    $file = __DIR__ .  '/TestApp/test.php';
    $this->assertFileExists($file);
    $content = file_get_contents($file);
    $this->assertEquals("test string", $content);
    
    $code = <<<END_OF_CODE
/* === DEVELOPER BEGIN */
  this won't change
/* === DEVELOPER END */
this will be updated every time    
END_OF_CODE;
    $this->wda->createFile('/subdir', 'test.php', $code);
    $file = __DIR__ .  '/TestApp/subdir/test.php';
    $this->assertFileExists($file);
    $content = file_get_contents($file);
    $this->assertEquals($code, $content);
    
    $code = <<<END_OF_CODE
/* === DEVELOPER BEGIN */
  updated!
/* === DEVELOPER END */
updated!  
END_OF_CODE;
    $expected_code =<<<END_OF_CODE
/* === DEVELOPER BEGIN */
  this won't change
/* === DEVELOPER END */
updated!  
END_OF_CODE;
    $this->wda->createFile('/subdir', 'test.php', $code);
    $file = __DIR__ .  '/TestApp/subdir/test.php';
    $content = file_get_contents($file);
    $this->assertEquals($expected_code, $content);
  }
  
  /*
  public function testWriteBootstrap() {
    $this->assertEquals(1,1);
  }
  */
  
  /*
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
  */
  
  public static function tearDownAfterClass():void {
    //self::rrmdir(__DIR__ .  '/TestApp');
  }
  
  private function template($filename) {
    return file_get_contents(__DIR__ . "/../src/templates/" . $filename);
  }
  
  private function sample($filename) {
    return file_get_contents(__DIR__ . "/samples/" . $filename);
  }
}
