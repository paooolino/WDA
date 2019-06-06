<?php

use PHPUnit\Framework\TestCase;

final class WdaTest extends TestCase {
  
  private $wda;
  
  protected function setUp():void {
    $this->wda = new \Wda\Wda(__DIR__, 'TestApp');
  }
  
  public function testCreateFile() {
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
    self::rrmdir(__DIR__ .  '/TestApp');
  }
}
