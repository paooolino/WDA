<?php
namespace Wda;

class Wda {
  private $config;
  //private $root;
  //private $AppName;
  
  //public function __construct($root, $AppName) {
    //$this->root = $root;
    //$this->AppName = $AppName;
  //}
  
  public function __construct() {
  }
  
  public function loadConfigFromString($ini_string) {
    $this->config = parse_ini_string($ini_string, true, INI_SCANNER_RAW);
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
        "WebApp\\\\": "WebApp/src/"
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
  
  public function getCodeDependenciesServices() {
    $config = $this->get_ini_section("SERVICES");
    
    $codearr = [];
    foreach ($config as $item => $item_config) {
      $class_name = ucfirst($item);
      $deps = [];
      if (isset($item_config["deps"])) {
        $deps = array_map(function($dep) {
          return '$c->' . $dep;
        }, $this->sToArr($item_config["deps"]));
      }
      $deps = implode(", ", $deps);
      
      $codearr[] = <<<END_OF_CODE
\$container['$item'] = function(\$c) {
  return new WebApp\\$class_name($deps);
};
END_OF_CODE;
    }
    
    return implode("\r\n\r\n", $codearr);
  }
  
  public function getCodeDependenciesMiddlewares() {
    $config = $this->get_ini_section("MIDDLEWARES");
    
    $codearr = [];
    foreach ($config as $item => $item_config) {
      $class_name = ucfirst($item);
      $deps = [];
      if (isset($item_config["deps"])) {
        $deps = array_map(function($dep) {
          return '$c->' . $dep;
        }, $this->sToArr($item_config["deps"]));
      }
      $deps = implode(", ", $deps);
      
      $codearr[] = <<<END_OF_CODE
\$container['WebApp\Middleware\\$class_name'] = function(\$c) {
  return new WebApp\Middleware\\$class_name($deps);
};
END_OF_CODE;
    }
    
    return implode("\r\n\r\n", $codearr);
  }
  
  public function getCodeDependenciesControllers() {
    $config = $this->get_ini_section("CONTROLLERS");
    
    $codearr = [];
    foreach ($config as $item => $item_config) {
      $class_name = ucfirst(strtolower($item)) . 'Controller';
      $deps = [];
      if (isset($item_config["deps"])) {
        $deps = array_map(function($dep) {
          return '$c->' . $dep;
        }, $this->sToArr($item_config["deps"]));
      }
      
      if (isset($item_config["models"])) {
        // models are dependencies themselves
        $deps = array_merge($deps, array_map(function($model) {
          return '$c->' . ucfirst(trim($model)) . "Model";
        }, explode(",", $item_config["models"])));
      }
  
      // se c'è un template, aggiunge automaticamente la dipendenza dalla view
      // se c'è la view passo anche app, serve per recuperare la templateUrl
      // se non lo è, passo router che serve sicuramente per il redirect
      // passo sempre anche app, può includere funzioni di utilità da usare anche nelle action.
      if (isset($item_config["template"])) {
        $deps[] = '$c->view';
        $deps[] = '$c->app';
      } else {
        $deps[] = '$c->router';
        $deps[] = '$c->app';
      }
  
      $deps = implode(", ", $deps);
      
      $codearr[] = <<<END_OF_CODE
\$container['WebApp\Controller\\$class_name'] = function(\$c) {
  return new WebApp\Controller\\$class_name($deps);
};
END_OF_CODE;
    }
    
    return implode("\r\n\r\n", $codearr);
  }
  
  public function getCodeDependenciesModels() {
    $config = $this->get_ini_section("MODELS");
    
    $codearr = [];
    foreach ($config as $item => $item_config) {
      $class_name = ucfirst(strtolower($item)) . 'Model';
      $deps = [];
      if (isset($item_config["deps"])) {
        $deps = array_map(function($dep) {
          return '$c->' . $dep;
        }, $this->sToArr($item_config["deps"]));
      }
      $deps = implode(", ", $deps);
      
      $codearr[] = <<<END_OF_CODE
\$container['WebApp\Model\\$class_name'] = function(\$c) {
  return new WebApp\Model\\$class_name($deps);
};
END_OF_CODE;
    }
    
    return implode("\r\n\r\n", $codearr);
  }
  
  public function getCodeMiddlewarePhp() {
    $code = <<<END_OF_CODE
<?php
  \$app->add('WebApp\Middleware\AppInit');
END_OF_CODE;
    return $code;
  }
  
  public function getCodeRoutesPhp() {  
    $config = $this->get_ini_section("CONTROLLERS");
    
    $code = "";

    foreach ($config as $route_name => $route_config) {
      $rpath = $route_config["path"];
      $rClassName = 'WebApp\\Controller\\' . ucfirst(strtolower($route_name)) . 'Controller';
      $method = (isset($route_config["method"])) ? $route_config["method"] : "get";
      
      $code .= "\$app->$method('$rpath', '$rClassName')->setName('$route_name');\r\n";
    }
    return $code;
  }
  
  public function getCodeSettingsPhp() {
    return $this->template("settings.php");
  }
  
  private function get_ini_section($section) {
    // trova le posizioni di tutte le sezioni
    $positions = array_filter(array_map(function($item, $index) {
      if (preg_match("/::(.*?)::/", $item) === 1) {
        return ["section" => $item, "pos" => $index];
      }
      return false;
    }, array_keys($this->config), array_keys(array_keys($this->config))),
      function($item) {
        if ($item === false)
          return false;
        return true;
      }
    );

    // trova la posizione della sezione voluta e quella della sezione successiva
    $section_pos = -1;
    $next_pos = -1;
    foreach ($positions as $p) {
      $name = $p["section"];
      if ($section_pos == -1) {
        if ($name == "::$section::") {
          $section_pos = $p["pos"];
        }
      } else {
        $next_pos = $p["pos"];
      }
    }
    
    // ritorna l'opportuno slice dell'array di configurazione
    if ($section_pos == -1) 
      return [];  
    if ($section_pos > -1 && $next_pos == -1)
      return array_slice($this->config, $section_pos+1);    
    if ($section_pos > -1 && $next_pos > -1)
      return array_slice($this->config, $section_pos+1, ($next_pos - $section_pos) - 1);
  }
  
  public function commentline($s) {
    $code = "";
    $code .= "\r\n";
    $code .= "//" . "\r\n";
    $code .= "// " . $s . "\r\n";
    $code .= "//" . "\r\n";
    
    return $code;
  }
  
  public function phpFile($code) {
    return "<?php\r\n" . $code;
  }
  
  public function makedir($dir) {
    if (!is_dir($dir))
      mkdir($dir, 0777, true);  
  }
  
  private function sToArr($s) {
    return array_map('trim', explode(', ', $s));
  }
  
  private function template($filename) {
    return file_get_contents(__DIR__ . "/templates/" . $filename);
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