<?php
/*
 *  CONFIGURATION BEGIN
 */
 
ini_set('display_errors', 0);

// the autoload where to find the Wda class
$AUTOLOAD_PATH = __DIR__ . '/../vendor/autoload.php';
// the config.ini used to generate the application
$CONFIG_INI_PATH = __DIR__ . '/config.ini';
// the app root directory
$APP_DIRNAME = "www";
$APP_DIR = __DIR__ . '/' . $APP_DIRNAME;
// the WDA directory in vendor
$WDA_DIR = '../';
$APP_URL = str_replace("/developer_assistant.php", "", $_SERVER['REQUEST_URI'])
  . '/' . $APP_DIRNAME;

/*
 *  CONFIGURATION END
 *  do not touch anything below.
 * ============================================================================
 */

// azione di compilazione, richiamata dopo il salvataggio
if (isset($_GET["action"])) {
  if ($_GET["action"] == "compile") {
    shell_exec("php ./" . $WDA_DIR . "/generatecode.php " . $CONFIG_INI_PATH . " " . $APP_DIRNAME);
    die("compiled.");
  }
}

if (isset($_GET["f"])) {
  $ext = (new SplFileInfo($_GET["f"]))->getExtension();
  $acemode = $ext;
  if ($ext == "js") $acemode = "javascript";
  
  // intercetta il salvataggio
  if (isset($_POST["value"])) {
    $result = file_put_contents($_GET["f"], $_POST["value"]);
    echo json_encode(["result" => !($result === false)]);
    die();
  }
//
//
//
//
//
//
?>
<!doctype html>
<html>
<head>
  <title><?php $parts = explode("/", $_GET["f"]); echo end($parts); ?></title>
  <link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">
  <style>
    *{margin:0;padding:0;}
    body{font-family: 'Roboto', sans-serif;}
    .header{background-color:#444;color:#ddd;padding:10px;font-size:18pt;margin-bottom:20px;}
    #editor { 
        position: absolute;
        top: 50px;
        right: 0;
        bottom: 0;
        left: 0;
    }
    #saving{display:none;}
    #compiling{display:none;}
  </style>
</head>
<body>
  <div class="header">
    Web developer assistant |
    <button class="command_btn" onclick="save_and_compile();">Save + compile</button>
    <button class="command_btn" onclick="sample_header()">Header</button>
    <button class="command_btn" onclick="sample_footer()">Footer</button>
    <button class="command_btn" onclick="bs4_navbar()">BS4 navbar</button>
    <button class="command_btn" onclick="bs4_layout()">BS4 layout</button>
    <button class="command_btn" onclick="bs4_loginform()">BS4 login form</button>
    <span id="saving">saving...</span>
    <span id="compiling">compiling...</span>
  </div>

  <div id="editor"><?php echo htmlspecialchars(file_get_contents($_GET["f"]));?></div>

  <script src="<?php echo $WDA_DIR; ?>/js/jquery/jquery-3.4.1.min.js"></script>
  <script src="<?php echo $WDA_DIR; ?>/js/ace/ace.js" type="text/javascript" charset="utf-8"></script>
  <script>
      var editor = ace.edit("editor");
      editor.setTheme("ace/theme/monokai");
      editor.session.setMode("ace/mode/<?php echo $acemode; ?>");
      editor.setOptions({
        fontSize: "14pt",
        tabSize: 2,
        useSoftTabs: true
      });
      editor.commands.addCommand({
          name: 'save',
          bindKey: {win: "Ctrl-S", "mac": "Cmd-S"},
          exec: save_and_compile
      })
      
      function save_and_compile() {
        // saving...
        $('#saving').show();
        $.ajax({
          url: 'developer_assistant.php?f=' + encodeURIComponent('<?php echo str_replace("\\", "\\\\", $_GET["f"]); ?>'),
          type: 'post',
          dataType: 'json',
          data: {
            value: editor.session.getValue()
          },
          failure: function() {
            alert('failed');
          },
          success: function(json) {
            if (json.result) {
              // ...saved.
              $('#saving').hide();
              compile();
            } else {
              alert('WARNING: save failed.');
            }
          }
        });
      }
      
      function sample_header() {
        var html = '';
        html += '<!doctype html>\r\n';
        html += '<head>\r\n';
        html += '  <meta charset="utf-8">\r\n';
        html += '  <title></title>\r\n';
        html += '  <meta name="description" content="">\r\n';
        html += '  <meta name="viewport" content="width=device-width, initial-scale=1">\r\n';        
        html += '  <!-- bootstrap -' + '->\r\n';        
        html += '  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">\r\n';        
        html += '  <link rel="stylesheet" href="">\r\n';        
        html += '</head>\r\n';
        html += '<body>\r\n';
        html += '  <header>\r\n';
        html += '  </header>\r\n';
        html += '  <div class="content">\r\n';
        var cursorPosition = editor.getCursorPosition();
        editor.session.insert(cursorPosition, html);
      }
      
      function sample_footer() {
        var html = '';
        html += '  </div>\r\n';
        html += '  <footer>\r\n';
        html += '    Footer.\r\n';
        html += '  </footer>\r\n';
        html += '  <!-- jquery -' + '->\r\n';
        html += '  <scr' + 'ipt src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></scr' + 'ipt>\r\n';
        html += '  <!-- popper -' + '->\r\n';
        html += '  <scr' + 'ipt src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></scr' + 'ipt>\r\n';
        html += '  <!-- bootstrap -' + '->\r\n';
        html += '  <scr' + 'ipt src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></scr' + 'ipt>\r\n';
        html += '  <scr' + 'ipt src=""></scr' + 'ipt>\r\n';
        html += '</body>\r\n';
        html += '</html>\r\n';        
        var cursorPosition = editor.getCursorPosition();
        editor.session.insert(cursorPosition, html);
      }
      
      function bs4_navbar() {
        var html = '';
        
        html += '<nav class="navbar navbar-expand-sm bg-dark navbar-dark">\r\n';
        html += '  <a class="navbar-brand" href="#">Logo</a>\r\n';
        html += '  <ul class="navbar-nav mr-auto">\r\n';
        html += '   <li class="nav-item">\r\n';
        html += '      <a class="nav-link" href="#">Link 1</a>\r\n';
        html += '    </li>\r\n';
        html += '    <li class="nav-item">\r\n';
        html += '      <a class="nav-link" href="#">Link 2</a>\r\n';
        html += '    </li>\r\n';
        html += '    <li class="nav-item">\r\n';
        html += '      <a class="nav-link" href="#">Link 3</a>\r\n';
        html += '    </li>\r\n';
        html += '    <li class="nav-item">\r\n';
        html += '      <a class="nav-link disabled" href="#">Disabled</a>\r\n';
        html += '    </li>\r\n';
        html += '  </ul>\r\n';
        html += '  <form class="form-inline my-2 my-lg-0">\r\n';
        html += '    <input class="form-control mr-sm-2" type="text" placeholder="Search">\r\n';
        html += '    <button class="btn btn-success my-2 my-sm-0" type="button">Search</button>\r\n';
        html += '  </form>\r\n';
        html += '</nav>\r\n';
        
        var cursorPosition = editor.getCursorPosition();
        editor.session.insert(cursorPosition, html);
      }
      
      function bs4_loginform() {
      }
      
      function bs4_layout() {
      }
      
      function compile() {
        $('#compiling').show();
        $.ajax({
          url: 'developer_assistant.php?action=compile',
          success: function(text) {
            // ...compiled.
            $('#compiling').hide();
          }
        });
      }
  </script>
</body>
</html>

<?php  
} else {
//
//
//
//
//
//
//  
require $AUTOLOAD_PATH;

$wda = new Wda\Wda();
$ini = file_get_contents($CONFIG_INI_PATH);
$wda->loadConfigFromString($ini);
$config = $wda->get_ini_section("CONTROLLERS");
$config_models = $wda->get_ini_section("MODELS");
$config_services = $wda->get_ini_section("SERVICES");

function get_file_infos($dir, $file) {  
  if (is_file($dir . "/" . $file)) {
    $lines = file($dir . "/" . $file);
    $result = [];
    foreach ($lines as $l) {
      if (strpos($l, "@status") !== false) {
        $parts = explode("@status", $l);
        $result["status"] = trim(str_replace("@status", "", $parts[1]));
      }
      if (strpos($l, "@desc") !== false) {
        $parts = explode("@desc", $l);
        $result["desc"] = trim(str_replace("@desc", "", $parts[1]));
      }
    }
    if (!empty($result)) {
      $result["link"] = '?f=' . rawurlencode($dir . '/' . $file);
      $result["filename"] = $file;
      return $result;
    }
  }
  return false;
}
function html_item($item, $routes=[]) {
  $title = isset($item["desc"]) ? $item["desc"] : "";
  return '
    <li data-routes="' . implode(" ", $routes) . '" class="color-' . $item["status"] . '">
      <a target="_' . $item["filename"] . '" title="' . str_replace("\"", "&quot;", $title) . '" href="' . $item["link"] . '">' . $item["filename"] . '</a>
    </li>
  ';
}



// carica le files infos
$controllers = array_filter(array_map(function($item) use($APP_DIR) {
  return get_file_infos($APP_DIR . '/app/src/Controller', $item);
}, scandir($APP_DIR . '/app/src/Controller')));

$templates = array_filter(array_map(function($item) use($APP_DIR) {
  return get_file_infos($APP_DIR . '/templates/default/src', $item);
}, scandir($APP_DIR . '/templates/default/src')));
$subtemplates = array_filter(array_map(function($item) use($APP_DIR) {
  return get_file_infos($APP_DIR . '/templates/default/src/partials', $item);
}, scandir($APP_DIR . '/templates/default/src/partials')));

$models = array_filter(array_map(function($item) use($APP_DIR) {
  return get_file_infos($APP_DIR . '/app/src/Model', $item);
}, scandir($APP_DIR . '/app/src/Model')));

$services = array_filter(array_map(function($item) use($APP_DIR) {
  return get_file_infos($APP_DIR . '/app/src', $item);
}, scandir($APP_DIR . '/app/src')));

$csss = array_filter(array_map(function($item) use($APP_DIR) {
  return get_file_infos($APP_DIR . '/templates/default/css', $item);
}, scandir($APP_DIR . '/templates/default/css')));

$jss = array_filter(array_map(function($item) use($APP_DIR) {
  return get_file_infos($APP_DIR . '/templates/default/js', $item);
}, scandir($APP_DIR . '/templates/default/js')));

$middlewares = array_filter(array_map(function($item) use($APP_DIR) {
  return get_file_infos($APP_DIR . '/app/src/Middleware', $item);
}, scandir($APP_DIR . '/app/src/Middleware')));

$config_link = '?f=' . rawurlencode($CONFIG_INI_PATH);        
          
// per ogni model, template elenca le route che li usano
$models_routes = [];
$templates_routes = [];
foreach ($config as $route_name => $route_config) {
  if (isset($route_config["models"])) {
    $parts = array_map(function($item) {
      $classname = ucfirst(strtolower(trim($item))) . 'Model';
      $filename = $classname . '.php';
      return $filename;
    }, explode(",", $route_config["models"]));
    foreach ($parts as $m) {
      if (!isset($models_routes[$m]))
        $models_routes[$m] = [];
      $models_routes[$m][] = $route_name;
    }
  }
  if (isset($route_config["template"])) {
    $t = $route_config["template"] . ".php";
    if (!isset($templates_routes[$t]))
      $templates_routes[$t] = [];
    $templates_routes[$t][] = $route_name;
  }
}
?>
<!doctype html>
<html>
<head>
  <link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">
  <style>
  *{margin:0;padding:0;}
  body{font-family: 'Roboto', sans-serif;}
  .header{background-color:#444;color:#ddd;padding:10px;font-size:18pt;margin-bottom:20px;}
  ul, li{list-style-type:none;}
  li{padding:8px;border-bottom:1px solid #ddd;}
  li.color-0{background-color:red;}
  li.color-1{background-color:#fa0;}
  li.color-2{background-color:yellow;}
  li.color-3{background-color:#af0;}
  li.color-4{background-color:#5f0;}
  li.color-5{background-color:green;}
  li.evidence{background-color:yellow;}
  </style>
</head>
<body>
  <div class="header">
    Web developer assistant
  </div>
  <table>
    <thead>
      <tr>
        <th>Routes</th>
        <th>Controllers/Actions</th>
        <th>Models</th>
        <th>Templates</th>
        <th>Services</th>
        <th>Middlewares</th>
        <th>Config</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td id="routes_col" valign="top">
          <ul>
          <?php
          foreach ($config as $route_name => $route_config) {
            if (isset($route_config["template"])) {
              ?>
              <li class="<?php echo $route_name; ?>">
                <a target="_blank" href="<?php echo $APP_URL . $route_config["path"]; ?>"><?php echo $route_name; ?></a>
              </li>
              <?php
            } else {
              ?>
              <li class="<?php echo $route_name; ?>">
                <?php echo $route_name; ?>
              </li>
              <?php
            }
          }
          ?>
          </ul>
        </td>
        <td valign="top">
          <ul>
            <?php 
            // per ogni route c'è esattamente 1 controller
            foreach ($config as $route_name => $route_config) {
              $classname = ucfirst(strtolower($route_name)) . 'Controller';
              $filename = $classname . '.php';
              $file = $APP_DIR . '/app/src/Controller' . $filename;
              
              $fileinfos = get_file_infos($APP_DIR . '/app/src/Controller', $filename);
              if ($fileinfos) {
                echo html_item($fileinfos);
              } else {
                ?><li><?php echo $filename; ?></li><?php
              }
            }
            ?>
          </ul>
        </td>
        <td id="models_col" valign="top">
          <ul>
            <?php 
            foreach ($models as $item) { 
              // quali routes usano questo model?
              $routes = $models_routes[$item["filename"]];
              echo html_item($item, $routes);
            }
            ?>
          </ul>
        </td>
        <td id="templates_col" valign="top">
          <ul>
            <?php 
            foreach ($templates as $item) { 
              $routes = $templates_routes[$item["filename"]];
              echo html_item($item, $routes);
            }
            ?>
            <li>--- subtemplates:</li>
            <?php 
            foreach ($subtemplates as $item) { 
              echo html_item($item);
            }
            ?>
            <li>--- css's:</li>
            <?php 
            foreach ($csss as $item) { 
              echo html_item($item);
            }
            ?>
            <li>--- js's:</li>
            <?php 
            foreach ($jss as $item) { 
              echo html_item($item);
            }
            ?>
          </ul>
        </td>
        <td valign="top">
          <ul>
            <?php 
            foreach ($services as $item) { 
              echo html_item($item);
            }
            ?>
          </ul>
        </td>
        <td id="middlewares_col" valign="top">
          <ul>
            <?php 
            foreach ($middlewares as $item) { 
              echo html_item($item);
            }
            ?>
          </ul>
        </td>
        <td valign="top">
          <ul>
            <a target="_config.ini" href="<?php echo $config_link; ?>" title="">config</a>
          </ul>
        </td>
      </tr>
    </tbody>
  </table>
  
  <script
    src="https://code.jquery.com/jquery-3.4.1.slim.min.js"
    integrity="sha256-pasqAKBDmFT4eHoN2ndd6lN370kFiGUFyTiUHWhU7k8="
    crossorigin="anonymous"></script>
  <script>
    $('#models_col li').on('mouseenter', function() {
      $('#routes_col li').removeClass("evidence");
      if ($(this).data('routes')) {
        var routes = $(this).data('routes').split(" ");
        for (var i = 0; i < routes.length; i++) {
          console.log(routes[i], $('#' + routes[i])[0]);
          $('#routes_col li.' + routes[i]).addClass("evidence");
        }
      }
    });
    $('#templates_col li').on('mouseenter', function() {
      $('#routes_col li').removeClass("evidence");
      if ($(this).data('routes')) {
        var routes = $(this).data('routes').split(" ");
        for (var i = 0; i < routes.length; i++) {
          $('#routes_col li.' + routes[i]).addClass("evidence");
        }
      }
    });
    $('#models_col li').on('mouseleave', function() {
      $('#routes_col li').removeClass("evidence");
    });
    $('#templates_col li').on('mouseleave', function() {
      $('#routes_col li').removeClass("evidence");
    });
  </script>
</body>
</html>
<?php } ?>