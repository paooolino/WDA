<?php
ini_set('display_errors', 0);

if (isset($_GET["f"])) {
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
  <style>
    #editor { 
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
    }
  </style>
</head>
<body>
  <div id="editor"><?php echo htmlspecialchars(file_get_contents($_GET["f"]));?></div>

  <script src="js/jquery/jquery-3.4.1.min.js"></script>
  <script src="js/ace/ace.js" type="text/javascript" charset="utf-8"></script>
  <script>
      var editor = ace.edit("editor");
      editor.setTheme("ace/theme/monokai");
      editor.session.setMode("ace/mode/php");
      editor.setOptions({
        fontSize: "14pt",
        tabSize: 2,
        useSoftTabs: true
      });
      editor.commands.addCommand({
          name: 'save',
          bindKey: {win: "Ctrl-S", "mac": "Cmd-S"},
          exec: function(editor) {
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

                } else {
                  alert('WARNING: save failed.');
                }
              }
            });
          }
      })
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
require __DIR__ . '/vendor/autoload.php';

$wda = new Wda\Wda();
$ini = file_get_contents(__DIR__ . '/../config/config.ini');
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
$controllers = array_filter(array_map(function($item) {
  return get_file_infos(__DIR__ . '/../app/src/Controller', $item);
}, scandir(__DIR__ . '/../app/src/Controller')));

$templates = array_filter(array_map(function($item) {
  return get_file_infos(__DIR__ . '/../templates/default/src', $item);
}, scandir(__DIR__ . '/../templates/default/src')));
$subtemplates = array_filter(array_map(function($item) {
  return get_file_infos(__DIR__ . '/../templates/default/src/partials', $item);
}, scandir(__DIR__ . '/../templates/default/src/partials')));

$models = array_filter(array_map(function($item) {
  return get_file_infos(__DIR__ . '/../app/src/Model', $item);
}, scandir(__DIR__ . '/../app/src/Model')));

$services = array_filter(array_map(function($item) {
  return get_file_infos(__DIR__ . '/../app/src', $item);
}, scandir(__DIR__ . '/../app/src')));

$csss = array_filter(array_map(function($item) {
  return get_file_infos(__DIR__ . '/../templates/default/css', $item);
}, scandir(__DIR__ . '/../templates/default/css')));

$jss = array_filter(array_map(function($item) {
  return get_file_infos(__DIR__ . '/../templates/default/js', $item);
}, scandir(__DIR__ . '/../templates/default/js')));

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
  <style>
  *{margin:0;padding:0;}
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
  <table>
    <thead>
      <tr>
        <th>Routes</th>
        <th>Controllers/Actions</th>
        <th>Models</th>
        <th>Templates</th>
        <th>Services</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td id="routes_col" valign="top">
          <ul>
          <?php
          foreach ($config as $route_name => $route_config) {
            ?><li class="<?php echo $route_name; ?>"><?php echo $route_name; ?></li><?php
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
              $file = __DIR__ . '/../app/src/Controller' . $filename;
              
              $fileinfos = get_file_infos(__DIR__ . '/../app/src/Controller', $filename);
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