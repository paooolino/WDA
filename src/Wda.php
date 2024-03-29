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
  
  public function create_file($dir, $filename, $code, $force=true) {
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

  public function getCssCode() {
    return $this->template("style.css");
  }
  
  public function getJsCode() {
    return $this->template("scripts.js");
  }
  
  public function getCodeComposerJson() {
    return $this->template("composer.json");
  }
  
  public function getCodeHtaccess() {
    return $this->template(".htaccess");
  }
  
  public function getCodeGitignore() {
    return $this->template(".gitignore");
  }
  
  public function getIndexCode() {
    return $this->template("index.php");
  }
  
  public function getDefaultDependenciesServices() {
    return $this->template("getDefaultDependenciesServices.tpl");
  }
  
  public function getAppServiceCode() {
    return $this->template("AppService.php");
  }
  
  public function getCodeDependenciesServices() {
    $config = $this->get_ini_section("SERVICES");
    
    $codearr = [];
    foreach ($config as $item => $item_config) {
      $class_name = ucfirst($item) . "Service";
      $deps = [];
      if (isset($item_config["deps"])) {
        $deps = array_map(function($dep) {
          return '$c->' . $dep;
        }, $this->sToArr($item_config["deps"]));
      }
      $deps = implode(", ", $deps);
      
      if (isset($item_config["type"]) && $item_config["type"] == "factory") {
        $codearr[] = <<<END_OF_CODE
\$container['$item'] = function(\$c) {
  return WebApp\\$class_name::create($deps);
};
END_OF_CODE;
      } else {
        $codearr[] = <<<END_OF_CODE
\$container['$item'] = function(\$c) {
  return new WebApp\\$class_name($deps);
};
END_OF_CODE;
      }
    }
    
    return implode("\r\n\r\n", $codearr);
  }
  
  public function getDefaultDependenciesMiddlewares() {
    return $this->template("getDefaultDependenciesMiddlewares.tpl");
  }
  
  public function getCodeDependenciesMiddlewares() {
    $config = $this->get_ini_section("MIDDLEWARES");
    
    $codearr = [];
    foreach ($config as $item => $item_config) {
      $class_name = ucfirst($item) . "Middleware";
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
  
  private function getMiddlewaresForRoutes($route_config) {
    $middlewares = "";
    if (isset($route_config["middlewares"])) {
      $middlewares_arr = $this->sToArr($route_config["middlewares"]);
      foreach ($middlewares_arr as $middleware_name) {
        $mClassName = 'WebApp\\Middleware\\' . ucfirst(strtolower($middleware_name)) . 'Middleware';
        $middlewares .= "->add('$mClassName')";
      }
    }
    return $middlewares;
  }
  
  private function get_deps_controller($route_config) {
    $deps = [];
    
    // dipendenze implicite
    //  app: sempre
    //  view: se si tratta di un controller-page
    //  router: se si tratta di un controller-action
    $deps[] = 'app';
    if (isset($route_config["template"])) {
      $deps[] = 'view';
    } else {
      $deps[] = 'router';
    }
    
    // dipendenze esplicite
    if (isset($route_config["deps"])) {
      $deps = array_merge($deps, $this->sToArr($route_config["deps"]));
    }
    
    // models are dependencies themselves
    if (isset($route_config["models"])) {
      $deps = array_merge($deps, array_map(function($model) {
        return ucfirst(trim($model)) . "Model";
      }, explode(",", $route_config["models"])));
    }
    
    return $deps;
  }
  
  private function get_deps_model($model_config) {
    return $this->get_deps_item($model_config);
  }
  
  private function get_deps_service($service_config) {
    return $this->get_deps_item($service_config);
  }
  
  private function get_deps_middleware($middleware_config) {
    return $this->get_deps_item($middleware_config);
  }
  
  private function get_deps_item($item_config) {
    $deps = [];
    if (isset($item_config["deps"])) {
      $deps = array_merge($deps, $this->sToArr($item_config["deps"]));
    }
    return $deps;
  }
  
  public function getCodeDependenciesControllers() {
    $config = $this->get_ini_section("CONTROLLERS");
    
    $codearr = [];
    foreach ($config as $item => $item_config) {
      $class_name = ucfirst(strtolower($item)) . 'Controller';
      $deps = $this->get_deps_controller($item_config);

      $deps_list = $this->getDepsList(array_map(function($d) {
        return 'c->' . $d;
      }, $deps));
      
      $codearr[] = <<<END_OF_CODE
\$container['WebApp\Controller\\$class_name'] = function(\$c) {
  return new WebApp\Controller\\$class_name($deps_list);
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
\$container['$class_name'] = function(\$c) {
  return new WebApp\Model\\$class_name($deps);
};
END_OF_CODE;
    }
    
    return implode("\r\n\r\n", $codearr);
  }
  
  public function getCodeMiddlewarePhp() {
    // find middlewares with routes = ALL
    $config = $this->get_ini_section("MIDDLEWARES");
    
    $code = "<?php\r\n";
    foreach ($config as $middleware_name => $middleware_config) {
      if (isset($middleware_config["routes"]) && $middleware_config["routes"] == "ALL") {
        $mClassName = 'WebApp\\Middleware\\' . ucfirst(strtolower($middleware_name)) . 'Middleware';
        $code .= "\$app->add('$mClassName');\r\n";      
      }
    }
    return $code;
  }
  
  public function getCodeRoutesPhp() {  
    $config = $this->get_ini_section("CONTROLLERS");

    $code = "";
    foreach ($config as $route_name => $route_config) {
      $rpath = $route_config["path"];
      $rClassName = 'WebApp\\Controller\\' . ucfirst(strtolower($route_name)) . 'Controller';
      $method = (isset($route_config["method"])) ? $route_config["method"] : "get";
      $middlewares = $this->getMiddlewaresForRoutes($route_config);
      
      $code .= "\$app->$method('$rpath', '$rClassName')->setName('$route_name')$middlewares;\r\n";
    }
    return $code;
  }
  
  public function getCodeSettingsPhp() {
    return $this->template("settings.php");
  }
  
  public function getCodeMiddlewareAppInit() {
    return $this->template("Middleware_AppInit.php");
  }
  
  public function getCodeMiddlewareAuth() {
    return $this->template("Middleware_Auth.php");
  }
  
  public function getCodeControllers() {
    $controllers = [
      "pages" => [],
      "actions" => []
    ];

    $config = $this->get_ini_section("CONTROLLERS");
    foreach ($config as $route_name => $route_config) {
      $classname = ucfirst(strtolower($route_name)) . 'Controller';
      if (isset($route_config["template"])) {
        $controllers["pages"][] = [
          "classname" => $classname,
          "code" => $this->getCodeControllerPage($classname, $route_config)
        ];
      } else {
        $controllers["actions"][] = [
          "classname" => $classname,
          "code" => $this->getCodeControllerAction($classname, $route_config)
        ];
      }
    }
    
    return $controllers;
  }
  
  public function getCodeServices() {
    $services = [];

    $config = $this->get_ini_section("SERVICES");
    foreach ($config as $route_name => $route_config) {
      $classname = ucfirst(strtolower($route_name)) . 'Service';
      $services[] = [
        "classname" => $classname,
        "code" => $this->getCodeService($classname, $route_config)
      ];
    }
    
    return $services;
  }
  
  public function getCodeModels() {
    $models = [];

    $config = $this->get_ini_section("MODELS");
    foreach ($config as $model_name => $model_config) {
      $classname = ucfirst(strtolower($model_name)) . 'Model';
      $models[] = [
        "classname" => $classname,
        "code" => $this->getCodeModel($classname, $model_config)
      ];
    }
    
    return $models;
  }
  
  public function getCodeMiddlewares() {
    $middlewares = [];

    $config = $this->get_ini_section("MIDDLEWARES");
    foreach ($config as $middleware_name => $middleware_config) {
      $classname = ucfirst(strtolower($middleware_name)) . 'Middleware';
      $middlewares[] = [
        "classname" => $classname,
        "code" => $this->getCodeMiddleware($classname, $middleware_config)
      ];
    }
    
    return $middlewares;
  }
  
  public function getCodeTemplates() {
    $templates = [];

    $config = $this->get_ini_section("CONTROLLERS");
    foreach ($config as $route_name => $route_config) {
      if (isset($route_config["template"])) {
        $name = $route_config["template"];
        $desc = $route_config["desc"] ?? "";
        $models_vars = ['$templateUrl'];
        if (isset($route_config["models"])) {
          $models_vars = array_merge(
            $models_vars,
            array_map(function($model) {
              $m = trim($model);
              return '$' . $m;
            }, explode(",", $route_config["models"]))
          );
        }
        $templates[] = [
          "name" => $name,
          "desc" => $desc,
          "code" => $this->getCodeTemplate($name),
          "vars" => $models_vars
        ];
      }
    }
    
    return $templates;
  }
  
  public function replace_in_file($filepath, $regex, $newvalue) {
    $tpl = file_get_contents($filepath);
    $tpl = preg_replace($regex, $newvalue, $tpl);
    file_put_contents($filepath, $tpl);
  }
  
  public function compile_template($src, $dest, $filename, $mainTemplate=false) {
    // carica il template source
    $tpl = file_get_contents($src . '/' . $filename);
    
    // compila i tag
    $tags = [];
    preg_match_all("/{{(.*?)}}/", $tpl, $tags);
    for ($i = 0; $i < count($tags[0]); $i++) {
      $tagname = $tags[1][$i];
      
      $tpl_source_dir = 'templates/default/src/partials'; 
      $tpl_dest_dir = 'templates/default/partials'; 
      $subfilename = $tagname . '.php';
      if (!file_exists($tpl_source_dir . '/' . $subfilename)) {
        $code = $this->getCodeTemplate($tagname);
        $this->create_file($tpl_source_dir, $subfilename, $code);
      }
      $this->compile_template($tpl_source_dir, $tpl_dest_dir, $subfilename);
      $tagcode = $mainTemplate 
        ? "<?php require __DIR__ . '/partials/' . '$subfilename'; ?>"
        : "<?php require __DIR__ . '/' . '$subfilename'; ?>";
      $tpl = str_replace("{{".$tagname."}}", $tagcode, $tpl);
    }

    // crea il file
    $this->create_file($dest, $filename, $tpl);
  }
  
  private function populateTemplate($tpl, $data) {
    // populate simple tag with data
    foreach ($data as $k => $v) {
      // if a string, try the tag substitution
      if (gettype($v) == "string" || gettype($v) == "integer") {
        $tpl = str_replace("{{".$k."}}", $v, $tpl);
      }
    }
    return $tpl;
  }
  
  private function getDepsMembers($deps) {
    $html = '';
    foreach ($deps as $dep) {
      $html .= "  private \$$dep;\r\n";
    }
    return $html;
  }
  
  private function getDepsAssign($deps) {
    $html = '';
    foreach ($deps as $dep) {
      $html .= "    \$this->$dep = \$$dep;\r\n";
    }
    return $html;
  }
  
  private function getDepsList($deps) {
    return implode(", ", array_map(function($d) { return '$' . $d; }, $deps));
  }
  
  private function getModelsContent($route_config) {
    $html = "";
    
    $models = [];
    if (isset($route_config["models"])) {
      $models = array_merge($models, array_map(function($model) {
        $m = trim($model);
        return '    $' . $m . ' = $this->getdata_' . $m . 'Model($request, $args);';
      }, explode(",", $route_config["models"])));
    }
    $html = implode("\r\n", $models);
    
    return $html;
  }
  
  private function getModelsVars($route_config) {
    $html = "";
    
    $models = [];
    if (isset($route_config["models"])) {
      $models = array_merge($models, array_map(function($model) {
        $m = trim($model);
        return '        "' . $m . '" => $' . $m;
      }, explode(",", $route_config["models"])));
    }
    $html = implode(",\r\n", $models);
    
    return $html;
  }
  
  private function getViewmodelsContent($route_config) {
    $html = "";
    
    $models = [];
    if (isset($route_config["models"])) {
      $models = array_merge($models, array_map(function($model) {
        $m = trim($model);
        $model_class = ucfirst($m) . "Model";
        return <<<END_OF_CODE
  private function getdata_${m}Model(\$request, \$args) {
    /* === DEVELOPER BEGIN */
    
    // based on request, get data from the pure model.
    // \$var = \$request->getParsedBody()['var']; 
    // \$var = \$request->getQueryParams()['var']; 
    // return \$this->${model_class}->get(\$var);

    return \$this->${model_class}->get();
    
    /* === DEVELOPER END */
  }
END_OF_CODE;
      }, explode(",", $route_config["models"])));
    }
    $html = implode("\r\n\r\n", $models);
    
    return $html;
  }
  
  private function getCodeControllerPage($classname, $route_config) {
    $deps = $this->get_deps_controller($route_config);
        
    $deps_members = $this->getDepsMembers($deps);
    $deps_assign = $this->getDepsAssign($deps);
    $deps_list = $this->getDepsList($deps);
    $models_content = $this->getModelsContent($route_config);
    $models_vars = $this->getModelsVars($route_config);
    $viewmodels_content = $this->getViewmodelsContent($route_config);
    
    $code = $this->populateTemplate(
      $this->template("ControllerTemplate.php"),
      [
        "classname" => $classname,
        "templatename" => $route_config["template"],
        "deps_members" => $deps_members,
        "deps_assign" => $deps_assign,
        "deps_list" => $deps_list,
        "models_content" => $models_content,
        "models_vars" => $models_vars,
        "viewmodels_content" => $viewmodels_content
      ]
    );
    return $code;
  }
  
  private function getCodeService($classname, $service_config) {
    $deps = $this->get_deps_service($service_config);

    $deps_members = $this->getDepsMembers($deps);
    $deps_assign = $this->getDepsAssign($deps);
    $deps_list = $this->getDepsList($deps);

    $tpl_name = "ServiceTemplate.php";
    if (isset($service_config["type"]) && $service_config["type"] == "factory")
      $tpl_name = "ServiceTemplateFactory.php";

    // default services: [app] >>> AppService, [view] >>> ViewService
    if ($classname == "AppService")
      $tpl_name = "AppServiceTemplate.php";
    if ($classname == "ViewService")
      $tpl_name = "ViewServiceTemplate.php";
    
    $code = $this->populateTemplate(
      $this->template($tpl_name),
      [
        "classname" => $classname,
        "deps_members" => $deps_members,
        "deps_assign" => $deps_assign,
        "deps_list" => $deps_list
      ]
    );
    return $code;
  }
  
  private function getCodeModel($classname, $model_config) {
    $deps = $this->get_deps_model($model_config);
    
    $deps_members = $this->getDepsMembers($deps);
    $deps_assign = $this->getDepsAssign($deps);
    $deps_list = $this->getDepsList($deps);
    
    $code = $this->populateTemplate(
      $this->template("ModelTemplate.php"),
      [
        "classname" => $classname,
        "deps_members" => $deps_members,
        "deps_assign" => $deps_assign,
        "deps_list" => $deps_list
      ]
    );
    return $code;
  }
  
  private function getCodeMiddleware($classname, $middleware_config) {
    $deps = $this->get_deps_middleware($middleware_config);
    
    $deps_members = $this->getDepsMembers($deps);
    $deps_assign = $this->getDepsAssign($deps);
    $deps_list = $this->getDepsList($deps);
    
    $tpl_name = "MiddlewareTemplate.php";
    // default services: [app_init] >>> App_init
    if ($classname == "App_initMiddleware")
      $tpl_name = "App_initMiddlewareTemplate.php";
    
    $code = $this->populateTemplate(
      $this->template($tpl_name),
      [
        "classname" => $classname,
        "deps_members" => $deps_members,
        "deps_assign" => $deps_assign,
        "deps_list" => $deps_list
      ]
    );
    return $code;
  }
  
  private function getCodeTemplate($name) {   
    $code = $this->populateTemplate(
      $this->template("TemplateTemplate.php"),
      [
        "name" => $name
      ]
    );
    return $code;
  }
  
  private function getCodeControllerAction($classname, $route_config) {
    $deps = $this->get_deps_controller($route_config);
        
    $deps_members = $this->getDepsMembers($deps);
    $deps_assign = $this->getDepsAssign($deps);
    $deps_list = $this->getDepsList($deps);
    $models_content = $this->getModelsContent($route_config);
    
    $code = $this->populateTemplate(
      $this->template("ControllerAction.php"),
      [
        "classname" => $classname,
        "deps_members" => $deps_members,
        "deps_assign" => $deps_assign,
        "deps_list" => $deps_list,
        "models_content" => $models_content
      ]
    );
    return $code;
  }
  
  public function get_ini_section($section) {
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
        if ($next_pos == -1)
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