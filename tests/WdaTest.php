<?php

use PHPUnit\Framework\TestCase;

final class WdaTest extends TestCase {
  
  private $wda;
  
  protected function setUp():void {
    $this->wda = new \Wda\Wda();
  }
  
  public function testCodeComposerJson() {
    $code = $this->wda->getCodeComposerJson();
    $expected = <<<END_OF_CODE
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
    $this->assertEquals($expected, $code);
  }
  
  public function testCodeHtaccess() {
    $code = $this->wda->getCodeHtaccess();
    $expected = <<<END_OF_CODE
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
END_OF_CODE;
    $this->assertEquals($expected, $code);
  }
  
  public function testCodeGitignore() {
    $code = $this->wda->getCodeGitignore();
    $expected = <<<END_OF_CODE
/vendor/
END_OF_CODE;
    $this->assertEquals($expected, $code);
  }
  
  public function testCodeDependenciesPhp() {
    // services
    $ini = <<<END_OF_CODE
[::SERVICES::]

[auth]
deps = db
desc = Funzioni utili per l'autenticazione utente.

[service2]
deps = dep1, dep2

[service3]
desc = una descrizione su una riga
END_OF_CODE;

    $code = $this->wda->getCodeDependenciesService($ini);
    $expected = <<<END_OF_CODE
\$container['auth'] = function(\$c) {
  return new WebApp\Auth(\$c->db);
};

\$container['service2'] = function(\$c) {
  return new WebApp\Service2(\$c->dep1, \$c->dep2);
};

\$container['service3'] = function(\$c) {
  return new WebApp\Service3();
};
END_OF_CODE;

    $this->assertEquals($expected, $code);
  }
  
  public function testCodeMiddlewarePhp() {
  }
  
  public function testCodeRoutesPhp() {
  }
  
  public function testCodeSettingsPhp() {
  }
  
  public function testCodeControllers() {
  }
  
  public function testCodeAppInitMiddleware() {
  }
  
  public function testCodeAuthMiddleware() {
  }
  
  public function testCodeModels() {
  }
  
  public function testCodeAppService() {
  }
  
  public function testCodeDBService() {
  }
  
  public function testCodeServices() {
  }
  
  public function testCodeTemplates() {
  }
  
  public function testCodeCss() {
  }
  
  public function testCodeJs() {
  }
  
  public function testCodeDeveloperAssistant() {
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
}
