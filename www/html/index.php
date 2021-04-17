<?php

require '/var/www/VSW-GUI-CONFIG';

$mod_file_count = new FilesystemIterator("/home/steam/valheimserver/BepInEx/config", FilesystemIterator::SKIP_DOTS);

if ($mod_file_count > 0 && $cfg_editor == true) {
  require 'pheditor.php';
}

if (isset($_GET['start'])) {
  $info = exec('sudo systemctl start valheimserver.service');
  header("Location: $_SERVER[PHP_SELF]");
  exit;
}
if (isset($_GET['stop'])) {
  $info = exec('sudo systemctl stop valheimserver.service');
  header("Location: $_SERVER[PHP_SELF]");
  exit;
}
if (isset($_GET['restart'])) {
  $info = exec('sudo systemctl restart valheimserver.service');
  header("Location: $_SERVER[PHP_SELF]");
  exit;
}

          // Get the status of valheimserver.service
          $info = shell_exec('systemctl status --no-pager -l valheimserver.service');
          $plugin_config_files = shell_exec("ls /home/steam/valheimserver/BepInEx/config/");

          // Pull all the values of of the output of $info
          $startup_line = strstr($info, '-name');    
          $name = str_replace("-name ", "", substr($startup_line, 0, strpos($startup_line, "-port")));
          $port = strstr($info, '-port');
          $port = str_replace("-port ", "", substr($port, 0, strpos($port, "-world")));
          $world = strstr($info, '-world');
          $world = str_replace("-world ", "", substr($world, 0, strpos($world, "-password")));
          $world_perm = $world;
          $public = strstr($info, '-public');
          $public = str_replace("-public ", "", $public);
          switch ($public) {
            case 0:
              $public_status = "Not Public";
              $public_class = "warning";
              break;
            case 1:
              $public_status = "Public";
              $public_class = "success";
            default:
              $public_status = "Error fetching data";
              $public_class = "danger";
              break;
          };
          $active = strstr($info, 'Active:');
          $active = str_replace("Active: ", "", substr($active, 0, strpos($active, ";")));
          $needle = "(dead)";
          $pos = strpos($info, $needle);
          if ($pos > 0) {
            $alert_class = "danger";
            $world = "<span class='glyphicon glyphicon-remove red'></span>";
            $port = "<span class='glyphicon glyphicon-remove red'></span>";
            $public = "NONE";
            $name = "Valheim Service Not Running";
            $public_status = "<span class='glyphicon glyphicon-remove red'></span>";
            $public_class = "danger";
            $public_attr = "disabled";
            $no_download = '';
            $no_download_class = 'success';
            $url_copy = 'hidden';
            $start_attr = '';
          } else {
            $alert_class = "success";
            $public_attr = "";
            $no_download = "disabled data-toggle=\"tooltip\" data-placement=\"top\" title=\"Must Stop Server to Download\"";
            $no_download_class = "danger";
            $url_copy = '';
            $start_attr = 'disabled';
          }

if (isset($_GET['download_db'])) {
  $command = exec('sudo cp -R /home/steam/.config/unity3d/IronGate/Valheim/worlds/* /var/www/html/download/');
  $dir    = '/var/www/html/download/';
  $files = scandir($dir);
  foreach ($files as $key => $value) {
    $ext  = (new SplFileInfo($value))->getExtension();
    if ($ext == 'db' ) {
      header('location: /download/'.$value);
      exit;
    }
  }
  trigger_error('No .db file found, check permissions and try again.');
  exit;
}

if (isset($_GET['download_fwl'])) {
  $command = exec('sudo cp -R /home/steam/.config/unity3d/IronGate/Valheim/worlds/* /var/www/html/download/');
  $dir    = '/var/www/html/download/';
  $files = scandir($dir);
  foreach ($files as $key => $value) {
    $ext  = (new SplFileInfo($value))->getExtension();
    if ($ext == 'fwl' ) {
      header('location: /download/'.$value);
      exit;
    }
  }
  trigger_error('No .db file found, check permissions and try again.');
  exit;
}

session_start();

// Hide mods accordion panel for users logged in
if (isset($_SESSION['login']) && $_SESSION['login'] == $hash) {
  $mods_accordion = 'collapse';
  $server_accordion = 'collapse in';
} else {
  $mods_accordion = 'collapse in';
  $server_accordion = 'collapse';
}

// ********** USER LOGOUT  ********** //
if(isset($_GET['logout'])) {
  unset($_SESSION['login']);
  header("Location: $_SERVER[PHP_SELF]");
  exit;
}

// ********** Form has been submitted ********** //
      if (isset($_POST['submit'])) {
        if ($_POST['username'] == $username && $_POST['password'] == $password){
          // If username and password correct, log in
          $_SESSION["login"] = $hash;
          header("Location: $_SERVER[PHP_SELF]");    
        } else {      
          // Display error on bad login
          display_login_form();
          echo '<div class="alert alert-danger">Incorrect login information.</div>';
          exit;
        }
      }

?>
<html>
  <head>
    <!-- JQuery and Bootstrap libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/css/iziToast.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.7/themes/default/style.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.43.0/codemirror.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.43.0/addon/lint/lint.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.43.0/addon/dialog/dialog.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
    <!-- Some Custom CSS -->
    <style type="text/css">
      * {

      }
      .white {
        color: white;
      }
      .list-group {
        max-width: 30vw;
      }
      h1,
    h1 a,
    h1 a:hover {
      margin: 0;
      padding: 0;
      color: #444;
      cursor: default;
      text-decoration: none;
    }

    #files {
      padding: 20px 10px;
      margin-bottom: 10px;
    }
    .CodeMirror.cm-s-default.CodeMirror-wrap, #files {
      max-height: 80vh;
    }
    #files>div {
      overflow: auto;
    }

    #path {
      margin-left: 10px;
    }

    .dropdown-item.close {
      font-size: 1em !important;
      font-weight: normal;
      opacity: 1;
    }

    #loading {
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      z-index: 9;
      display: none;
      position: absolute;
      background: rgba(0, 0, 0, 0.5);
    }

    .lds-ring {
      margin: 0 auto;
      position: relative;
      width: 64px;
      height: 64px;
      top: 45%;
    }

    .lds-ring div {
      box-sizing: border-box;
      display: block;
      position: absolute;
      width: 51px;
      height: 51px;
      margin: 6px;
      border: 6px solid #fff;
      border-radius: 50%;
      animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
      border-color: #fff transparent transparent transparent;
    }

    .lds-ring div:nth-child(1) {
      animation-delay: -0.45s;
    }

    .lds-ring div:nth-child(2) {
      animation-delay: -0.3s;
    }

    .lds-ring div:nth-child(3) {
      animation-delay: -0.15s;
    }

    @keyframes lds-ring {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }

    .dropdown-menu {
      min-width: 12rem;
    }

    #terminal {
      padding: 5px 10px;
    }

    #terminal .toggle {
      cursor: pointer;
    }

    #terminal pre {
      background: black;
      color: #ccc;
      padding: 5px 10px 10px 10px;
      border-radius: 5px 5px 0 0;
      margin: 5px 0 0 0;
      height: 200px;
      overflow-y: auto;
    }

    #terminal input.command {
      width: 100%;
      background: #333;
      color: #fff;
      border: 0;
      border-radius: 0 0 5px 5px;
      margin-bottom: 5px;
      padding: 5px;
    }

    #terminal .btn {
      padding: .5rem .4rem;
      font-size: .875rem;
      line-height: .5;
      border-radius: .2rem;
    }

    #terminal #prompt:fullscreen pre {
      margin: 0;
      border-radius: 0;
    }

    #terminal #prompt:fullscreen input.command {
      border-radius: 0;
    }

    #terminal span.toggle i::before {
      content: "\f107";
    }

    #terminal span.toggle.collapsed i::before {
      content: "\f105";
    }

    #terminal span.command {
      color: #eee;
    }
    
    html {
      font-size: 2vw;
    }

    @include media-breakpoint-up(sm) {
      html {
        font-size: 1.5vw;
      }
    }

    @include media-breakpoint-up(md) {
      html {
        font-size: 2vw;
      }
    }

    @include media-breakpoint-up(lg) {
      html {
        font-size: 3vw;
      }
    }
    .panel-title {
      font-size: 3vw;
    }
    .panel-title a {
      text-decoration: none;
    }
    .panel-title a:hover {
      color:white;
    }
    #copyButton {
      position: absolute;
      height: 100%;
      min-width: 5vw;
    }
    #copyTarget {
      padding-left: 6.5vw;
    }
    .thumbnail img {
      max-height: 22vh;
    }
    .thumbnail {
      font-size: 1.5vw;
      height: 96%;
    }
    .thumbnail .caption {
      font-size: 2vw;
    }
    .login input.button {
      height: 100%;
    }
    /* Bootstrap Accordion Visual Fix */
    .collapse.in {
      display: block !important;
    }
    /* Bootstrap panel fix because that 1px offset bothers the hell out of me */
    .panel-heading {
      position: relative;
      width: calc(100% + 2px);
      left: -1px;
      top: -1px;
    }
    .panel-primary>.panel-heading+.panel-collapse>.panel-body {
      border-top: none;
    }
    #editor_form {
      margin:0;
    }
    .btn-warning {
      color: white;
    }
    #loading-background {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      overflow: hidden;
      z-index: 998;
      background-color: rgba(0, 0, 0, 0.6);
    }
    #loading-body {
      position: fixed;
      top: 25vh;
      left: 25vw;
      width: 50vw;
      overflow: hidden;
      z-index: 999;
      padding: 4vw;
    }
    .spinner-grow {
      vertical-align: middle;
    }
    .red {
      color: red;
      vertical-align: middle;
    }
    .jstree-default .jstree-anchor {
      font-size: 1.5vw;
    }
    .CodeMirror-scroll * {
      font-size: 1.2vw;
    }
    .wrapper {
      max-width: 1200px;
      margin:auto;
    }

    @media only screen and (min-width: 1201px) {
      html {
      font-size: 24px;
      }

      @include media-breakpoint-up(sm) {
        html {
          font-size: 24px;
        }
      }

      @include media-breakpoint-up(md) {
        html {
          font-size: 28px;
        }
      }

      @include media-breakpoint-up(lg) {
        html {
          font-size: 32px;
        }
      }
      .panel-title {
        font-size: 36px;
      }
      .thumbnail .caption {
        font-size: 24px;
      }
      .thumbnail, .jstree-default .jstree-anchor {
        font-size: 18px;
      }
      .CodeMirror-scroll * {
        font-size: 14px;
      }
    }

    </style>
  <?php
  if ($mod_file_count > 0 && $cfg_editor == true) {
  ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.7/jstree.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.43.0/codemirror.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.43.0/mode/javascript/javascript.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.43.0/mode/css/css.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.43.0/mode/php/php.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.43.0/mode/xml/xml.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.43.0/mode/htmlmixed/htmlmixed.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.43.0/mode/markdown/markdown.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.43.0/mode/clike/clike.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jshint/2.10.2/jshint.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jsonlint/1.6.0/jsonlint.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.43.0/addon/lint/lint.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.43.0/addon/lint/javascript-lint.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.43.0/addon/lint/json-lint.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.43.0/addon/lint/css-lint.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.43.0/addon/search/search.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.43.0/addon/search/searchcursor.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.43.0/addon/search/jump-to-line.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.43.0/addon/dialog/dialog.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/js-sha512/0.8.0/sha512.min.js"></script>
  <script type="text/javascript">
    var editor,
      modes = {
        "js": "javascript",
        "json": "javascript",
        "md": "text/x-markdown"
      },
      last_keyup_press = false,
      last_keyup_double = false,
      terminal_history = 1;

    function alertBox(title, message, color) {
      iziToast.show({
        title: title,
        message: message,
        color: color,
        position: "bottomRight",
        transitionIn: "fadeInUp",
        transitionOut: "fadeOutRight",
      });
    }

    function reloadFiles(hash) {
      $.post("<?= $_SERVER['PHP_SELF'] ?>", {
        action: "reload"
      }, function(data) {
        $("#files > div").jstree("destroy");
        $("#files > div").html(data.data);
        $("#files > div").jstree();
        $("#files > div a:first").click();
        $("#path").html("");

        window.location.hash = hash || "/";

        if (hash) {
          $("#files a[data-file=\"" + hash + "\"], #files a[data-dir=\"" + hash + "\"]").click();
        }
      });
    }

    function setCookie(name, value, timeout) {
      if (timeout) {
        var date = new Date();
        date.setTime(date.getTime() + (timeout * 1000));
        timeout = "; expires=" + date.toUTCString();
      } else {
        timeout = "";
      }

      document.cookie = name + "=" + encodeURIComponent(value) + timeout + "; path=/";
    }

    function getCookie(name) {
      var cookies = document.cookie.split(';');

      for (var i = 0; i < cookies.length; i++) {
        if (cookies[i].trim().indexOf(name + "=") == 0) {
          return decodeURIComponent(cookies[i].trim().substring(name.length + 1).trim());
        }
      }

      return false;
    }

    $(function() {

      // Copy IP:Port Button Code
      "use strict";
      function copyToClipboard(elem) {
        var target = elem;

        // select the content
        var currentFocus = document.activeElement;

        target.focus();
        target.setSelectionRange(0, target.value.length);

        // copy the selection
        var succeed;

        try {
          succeed = document.execCommand("copy");
        } catch (e) {
          console.warn(e);

          succeed = false;
        }

        // Restore original focus
        if (currentFocus && typeof currentFocus.focus === "function") {
          currentFocus.focus();
        }

        if (succeed) {
          $(".copied").animate({ top: -25, opacity: 0 }, 700, function() {
            $(this).css({ top: 0, opacity: 1 });
          });
        }

        return succeed;
      }

      $("#copyButton, #copyTarget").on("click", function() {
        copyToClipboard(document.getElementById("copyTarget"));
      });
      // End IP:Port Button Code

      // Start Text editor Code
      editor = CodeMirror.fromTextArea($("#editor")[0], {
        <?php if (empty(EDITOR_THEME) === false) : ?>
          theme: "<?= EDITOR_THEME ?>",
        <?php endif; ?>
        lineNumbers: true,
        mode: "application/x-httpd-php",
        indentUnit: 4,
        indentWithTabs: true,
        lineWrapping: true,
        gutters: ["CodeMirror-lint-markers"],
        lint: true
      });

      $("#files > div").jstree({
        state: {
          key: "pheditor"
        },
        plugins: ["state"]
      });

      $("#files").on("dblclick", "a[data-file]", function(event) {
        event.preventDefault();
        <?php

        $base_dir = str_replace($_SERVER['DOCUMENT_ROOT'], '', str_replace(DS, '/', MAIN_DIR));

        if (substr($base_dir, 0, 1) !== '/') {
          $base_dir = '/' . $base_dir;
        }

        ?>
        window.open("<?= $base_dir ?>" + $(this).attr("data-file"));
      });

      $("a.change-password").click(function() {
        var password = prompt("Please enter new password:");

        if (password != null && password.length > 0) {
          $.post("<?= $_SERVER['PHP_SELF'] ?>", {
            action: "password",
            password: password
          }, function(data) {
            alertBox(data.error ? "Error" : "Success", data.message, data.error ? "red" : "green");
          });
        }
      });

      $(".dropdown .new-file").click(function() {
        var path = $("#path").html();

        if (path.length > 0) {
          var name = prompt("Please enter file name:", "new-file.php"),
            end = path.substring(path.length - 1),
            file = "";

          if (name != null && name.length > 0) {
            if (end == "/") {
              file = path + name;
            } else {
              file = path.substring(0, path.lastIndexOf("/") + 1) + name;
            }

            $.post("<?= $_SERVER['PHP_SELF'] ?>", {
              action: "save",
              file: file,
              data: ""
            }, function(data) {
              alertBox(data.error ? "Error" : "Success", data.message, data.error ? "red" : "green");

              if (data.error == false) {
                reloadFiles();
              }
            });
          }
        } else {
          alertBox("Warning", "Please select a file or directory", "yellow");
        }
      });

      $(".dropdown .new-dir").click(function() {
        var path = $("#path").html();

        if (path.length > 0) {
          var name = prompt("Please enter directory name:", "new-dir"),
            end = path.substring(path.length - 1),
            dir = "";

          if (name != null && name.length > 0) {
            if (end == "/") {
              dir = path + name;
            } else {
              dir = path.substring(0, path.lastIndexOf("/") + 1) + name;
            }

            $.post("<?= $_SERVER['PHP_SELF'] ?>", {
              action: "make-dir",
              dir: dir
            }, function(data) {
              alertBox(data.error ? "Error" : "Success", data.message, data.error ? "red" : "green");

              if (data.error == false) {
                reloadFiles();
              }
            });
          }
        } else {
          alertBox("Warning", "Please select a file or directory", "yellow");
        }
      });

      $(".dropdown .save").click(function() {
        var path = $("#path").html(),
          data = editor.getValue();

        if (path.length > 0) {
          $("#digest").val(sha512(data));

          $.post("<?= $_SERVER['PHP_SELF'] ?>", {
            action: "save",
            file: path,
            data: data
          }, function(data) {
            alertBox(data.error ? "Error" : "Success", data.message, data.error ? "red" : "green");
          });
        } else {
          alertBox("Warning", "Please select a file", "yellow");
        }
      });

      $(".dropdown .close").click(function() {
        editor.setValue("");
        $("#files > div a:first").click();
        $(".dropdown").find(".save, .delete, .rename, .reopen, .close").addClass("disabled");
      });

      $(".dropdown .delete").click(function() {
        var path = $("#path").html();

        if (path.length > 0) {
          if (confirm("Are you sure to delete this file?")) {
            $.post("<?= $_SERVER['PHP_SELF'] ?>", {
              action: "delete",
              path: path
            }, function(data) {
              alertBox(data.error ? "Error" : "Success", data.message, data.error ? "red" : "green");

              if (data.error == false) {
                reloadFiles();
              }
            });
          }
        } else {
          alertBox("Warning", "Please select a file or directory", "yellow");
        }
      });

      $(".dropdown .rename").click(function() {
        var path = $("#path").html(),
          split = path.split("/"),
          file = split[split.length - 1],
          dir = split[split.length - 2],
          new_file_name;

        if (path.length > 0) {
          if (file.length > 0) {
            new_file_name = file;
          } else if (dir.length > 0) {
            new_file_name = dir;
          } else {
            new_file_name = "new-file";
          }

          var name = prompt("Please enter new name:", new_file_name);

          if (name != null && name.length > 0) {
            $.post("<?= $_SERVER['PHP_SELF'] ?>", {
              action: "rename",
              path: path,
              name: name
            }, function(data) {
              alertBox(data.error ? "Error" : "Success", data.message, data.error ? "red" : "green");

              if (data.error == false) {
                reloadFiles(path.substring(0, path.lastIndexOf("/")) + "/" + name);
              }
            });
          }
        } else {
          alertBox("Warning", "Please select a file or directory", "yellow");
        }
      });

      $(".dropdown .reopen").click(function() {
        var path = $("#path").html();

        if (path.length > 0) {
          $(window).trigger("hashchange");
        }
      });

      $(window).resize(function() {
        if (window.innerWidth >= 720) {
          var terminalHeight = $("#terminal").length > 0 ? $("#terminal").height() : 0,
            height = window.innerHeight - $(".CodeMirror")[0].getBoundingClientRect().top - terminalHeight - 30;

          $("#files, .CodeMirror").css({
            "height": height + "px"
          });
        } else {
          $("#files > div, .CodeMirror").css({
            "height": ""
          });
        }

        if (document.fullscreen) {
          $("#prompt pre").height($(window).height() - $("#prompt input.command").height() - 20);
        }
      });

      $(window).resize();

      $(document).bind("keyup", function(event) {
        if ((event.ctrlKey || event.metaKey) && event.shiftKey) {
          if (event.keyCode == 78) {
            $(".dropdown .new-file").click();
            event.preventDefault();

            return false;
          } else if (event.keyCode == 83) {
            $(".dropdown .save").click();
            event.preventDefault();

            return false;
          } else if (event.keyCode == 76) {
            $("#terminal .toggle").click();
            event.preventDefault();

            return false;
          }
        }
      });

      $(document).bind("keyup", function(event) {
        if (event.keyCode == 27) {
          if (last_keyup_press == true) {
            last_keyup_double = true;

            $("#fileMenu").click();
            $("body").focus();
          } else {
            last_keyup_press = true;

            setTimeout(function() {
              if (last_keyup_double === false) {
                if (document.activeElement.tagName.toLowerCase() == "textarea") {
                  if ($("#terminal #prompt").hasClass("show")) {
                    $("#terminal .command").focus();
                  } else {
                    $(".jstree-clicked").focus();
                  }
                } else if (document.activeElement.tagName.toLowerCase() == "input") {
                  $(".jstree-clicked").focus();
                } else {
                  editor.focus();
                }
              }

              last_keyup_press = false;
              last_keyup_double = false;
            }, 250);
          }
        }
      });

      $(window).on("hashchange", function() {
        var hash = window.location.hash.substring(1),
          data = editor.getValue();

        if (hash.length > 0) {
          if ($("#digest").val().length < 1 || $("#digest").val() == sha512(data)) {
            if (hash.substring(hash.length - 1) == "/") {
              var dir = $("a[data-dir='" + hash + "']");

              if (dir.length > 0) {
                editor.setValue("");
                $("#digest").val("");
                $("#path").html(hash);
                $(".dropdown").find(".save, .reopen, .close").addClass("disabled");
                $(".dropdown").find(".delete, .rename").removeClass("disabled");
              }
            } else {
              var file = $("a[data-file='" + hash + "']");

              if (file.length > 0) {
                $("#loading").fadeIn(250);

                $.post("<?= $_SERVER['PHP_SELF'] ?>", {
                  action: "open",
                  file: encodeURIComponent(hash)
                }, function(data) {
                  if (data.error == true) {
                    alertBox("Error", data.message, "red");

                    return false;
                  }

                  editor.setValue(data.data);
                  editor.setOption("mode", "application/x-httpd-php");

                  $("#digest").val(sha512(data.data));

                  if (hash.lastIndexOf(".") > 0) {
                    var extension = hash.substring(hash.lastIndexOf(".") + 1);

                    if (modes[extension]) {
                      editor.setOption("mode", modes[extension]);
                    }
                  }

                  $("#editor").attr("data-file", hash);
                  $("#path").html(hash).hide().fadeIn(250);
                  $(".dropdown").find(".save, .delete, .rename, .reopen, .close").removeClass("disabled");

                  $("#loading").fadeOut(250);
                });
              }
            }
          } else if (confirm("Discard changes?")) {
            $("#digest").val("");

            $(window).trigger("hashchange");
          }
        }
      });

      if (window.location.hash.length < 1) {
        window.location.hash = "/";
      } else {
        $(window).trigger("hashchange");
      }

      $("#files").on("click", ".jstree-anchor", function() {
        location.href = $(this).attr("href");
      });

      $(document).ajaxError(function(event, request, settings) {
        var message = "An error occurred with this request.";

        if (request.responseText.length > 0) {
          message = request.responseText;
        }

        if (confirm(message + " Do you want to reload the page?")) {
          location.reload();
        }

        $("#loading").fadeOut(250);
      });

      $(window).keydown(function(event) {
        if ($("#fileMenu[aria-expanded='true']").length > 0) {
          var code = event.keyCode;

          if (code == 78) {
            $(".new-file").click();
          } else if (code == 83) {
            $(".save").click();
          } else if (code == 68) {
            $(".delete").click();
          } else if (code == 82) {
            $(".rename").click();
          } else if (code == 79) {
            $(".reopen").click();
          } else if (code == 67) {
            $(".close").click();
          } else if (code == 85) {
            $(".upload-file").click();
          }
        }
      });

      $(".dropdown .upload-file").click(function() {
        $("#uploadFileModal").modal("show");
        $("#uploadFileModal input").focus();
      });

      $("#uploadFileModal button").click(function() {
        var form = $(this).closest("form"),
          formdata = false;

        form.find("input[name=destination]").val(window.location.hash.substring(1));

        if (window.FormData) {
          formdata = new FormData(form[0]);
        }

        $.ajax({
          url: "<?= $_SERVER['PHP_SELF'] ?>",
          data: formdata ? formdata : form.serialize(),
          cache: false,
          contentType: false,
          processData: false,
          type: "POST",
          success: function(data, textStatus, jqXHR) {
            alertBox(data.error ? "Error" : "Success", data.message, data.error ? "red" : "green");

            if (data.error == false) {
              reloadFiles();
            }
          }
        });
      });

      var terminal_dir = "";

      $("#terminal .command").keydown(function(event) {
        if (event.keyCode == 13) {
          if ($(this).val().length > 0) {
            var _this = $(this)
            _val = _this.val();

            if (_val.toLowerCase() == "clear") {
              $("#terminal pre").html("");
              _this.val("").focus();

              return true;
            }

            _this.prop("disabled", true);
            $("#terminal pre").append("<span class=\"command\">&gt; " + _val + "</span>\n");
            $("#terminal pre").animate({
              scrollTop: $("#terminal pre").prop("scrollHeight")
            });

            var terminal_commands = $.parseJSON(getCookie("terminal_commands"));

            if (terminal_commands === false) {
              terminal_commands = [];
            }

            terminal_commands.push(_val);

            if (terminal_commands.length > 50) {
              terminal_commands = terminal_commands.slice(1);
            }

            setCookie("terminal_commands", JSON.stringify(terminal_commands));

            $.post("<?= $_SERVER['PHP_SELF'] ?>", {
              action: "terminal",
              command: _val,
              dir: terminal_dir
            }, function(data) {
              if (data.error) {
                $("#terminal pre").append(data.message);
              } else {
                if (data.dir != null) {
                  terminal_dir = data.dir;
                }

                if (data.result == null) {
                  data.result = "Command not found\n";
                }

                $("#terminal pre").append(data.result);
              }

              $("#terminal pre").stop().animate({
                scrollTop: $("#terminal pre").prop("scrollHeight")
              });
              _this.val("").prop("disabled", false).focus();
            });
          } else {
            $("#terminal pre").append("\n");
            $("#terminal pre").stop().animate({
              scrollTop: $("#terminal pre").prop("scrollHeight")
            });
          }
        } else if (event.keyCode == 38) {
          var terminal_commands = $.parseJSON(getCookie("terminal_commands"));

          if (terminal_commands && terminal_commands[terminal_commands.length - terminal_history]) {
            $(this).val(terminal_commands[terminal_commands.length - terminal_history]);

            terminal_history += 1;
          }
        } else if (event.keyCode == 40) {
          if (terminal_history > 1) {
            var terminal_commands = $.parseJSON(getCookie("terminal_commands"));

            if (terminal_commands && terminal_commands[terminal_commands.length - terminal_history + 2]) {
              $(this).val(terminal_commands[terminal_commands.length - terminal_history + 2]);

              terminal_history -= 1;
            }
          }
        }
      });

      $("#terminal .toggle").click(function() {
        if ($(this).attr("aria-expanded") != "true") {
          $("#terminal .command").focus();
        }
      });

      $('#prompt').on('show.bs.collapse', function() {
        $("#terminal").find(".clear, .copy, .fullscreen").css({
          "display": "block",
          "opacity": "0",
          "margin-right": "-30px"
        }).animate({
          "opacity": "1",
          "margin-right": "0px"
        }, 250);

        if (window.innerWidth >= 720) {
          var height = window.innerHeight - $(".CodeMirror")[0].getBoundingClientRect().top - $("#terminal #prompt").height() - 55;

          $("#files, .CodeMirror").animate({
            "height": height + "px"
          }, 250);
        } else {
          $("#files > div, .CodeMirror").animate({
            "height": ""
          }, 250);
        }

        setCookie("terminal", "1", 86400);
      }).on('hide.bs.collapse', function() {
        $("#terminal").find(".clear, .copy, .fullscreen").fadeOut();

        if (window.innerWidth >= 720) {
          var height = window.innerHeight - $(".CodeMirror")[0].getBoundingClientRect().top - $("#terminal span").height() - 35;

          $("#files, .CodeMirror").animate({
            "height": height + "px"
          }, 250);
        } else {
          $("#files > div, .CodeMirror").animate({
            "height": ""
          }, 250);
        }

        setCookie("terminal", "0", 86400);
      }).on('shown.bs.collapse', function() {
        $("#terminal .command").focus();
      });

      $("#terminal button.clear").click(function() {
        $("#terminal pre").html("");
        $("#terminal .command").val("").focus();
      });

      $("#terminal button.copy").click(function() {
        $("#terminal").append($("<textarea>").html($("#terminal pre").html()));

        element = $("#terminal textarea")[0];
        element.select();
        element.setSelectionRange(0, 99999);
        document.execCommand("copy");

        $("#terminal textarea").remove();
      });

      if (getCookie("terminal") == "1") {
        $("#terminal .toggle").click();
      }

      $("#terminal .fullscreen").click(function() {
        var element = $("#terminal #prompt")[0];

        if (element.requestFullscreen) {
          element.requestFullscreen();

          setTimeout(function() {
            $("#prompt pre").height($(window).height() - $("#prompt input.command").height() - 20);
            $("#prompt input.command").focus();
          }, 500);
        }
      });

      $(window).on("fullscreenchange", function() {
        if (document.fullscreenElement == null) {
          $("#terminal #prompt pre").css("height", "");
          $(window).resize();
        }
      });

      $(".server-function").click(function() {
        $("#loading-background").removeClass("hidden");
      });

    });
  // End Text editor code
  </script>
  <?php 
  // End If //
  };?>
</head>
        <body style="background-color: #222; padding: 2vw;">
          <div class="wrapper">
          <div id="loading-background" class="hidden">
            <div id="loading-body" class="panel panel-default">
              <div class="spinner-grow text-primary" role="status">
                <span class="sr-only">Loading...</span>
              </div>
              Server Executing Command, Please wait.
            </div>
          </div>


              <div class="row alert alert-<?php echo $alert_class; ?>" role="alert">
                <div class="col-8 h6"><span class="glyphicon glyphicon-hdd" aria-hidden="true"></span> <?php echo $active; ?></div>
                <div class="col-4 <?php echo $url_copy;?>">
                      <button id="copyButton" title="Click to copy" class="btn input-group-addon btn-<?php echo $alert_class;?>" <?php echo $alert_attr;?>><span class="glyphicon glyphicon-copy"></span></button>
                      <input type="text" id="copyTarget" class="form-control" value="<?php echo $realIP . ':' . $port;?>">
                </div>
              </div>
        <?php
        if ($mod_file_count > 0 && $show_mods == true) {
        ?>
          <div class="row">
            <div class="col-12">
              <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                <div class="panel panel-primary">
                  <div class="panel-heading" role="tab" id="headingOne">
                    <h4 class="panel-title">
                      <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                        Installed Mods
                      </a>
                    </h4>
                  </div>
                  <div id="collapseOne" class="panel-collapse <?php echo $mods_accordion; ?>" role="tabpanel" aria-labelledby="headingOne">
                    <div class="panel-body">
                      <div class="row">
                      <?php

                        $files = scandir('/home/steam/valheimserver/BepInEx/config');
                        foreach($files as $file) {
                          $new_str = "";
                          $full_file_name = "/home/steam/valheimserver/BepInEx/config/" . $file;
                          $lines_array = file($full_file_name);
                          $search_string = "nexusID";

                          foreach($lines_array as $line) {
                              if(strpos($line, $search_string) !== false) {
                                  list(, $new_str) = explode(" = ", $line);
                                  $new_str = str_replace(array("\r", "\n"), '', $new_str);
                              }
                          }

                          if (!empty($new_str)) {
                            $url = "https://www.nexusmods.com/valheim/mods/" . $new_str;
                            $fp = file_get_contents($url);
                            $res = preg_match("/<title>(.*)<\/title>/siU", $fp, $title_matches);
                            $res2 = preg_match("/<meta name=\"description\" content=\"(.*)\"/siU", $fp, $description_matches);
                            $res3 = preg_match("/<meta property=\"og:image\" content=\"(.*)\"/siU", $fp, $image_matches);
                            $title = preg_replace('/\s+/', ' ', $title_matches[1]);
                            $title = trim($title);
                            $title = str_replace("at Valheim Nexus - Mods and community", "", $title);
                            $description = preg_replace('/\s+/', ' ', $description_matches[1]);
                            $image = preg_replace('/\s+/', ' ', $image_matches[1]);

                          echo "<div class='col-md-4'><div class='thumbnail'><a target='_blank' href='" . $url . "'><img src='" . $image . "'><div class='caption'>" . $title .  "</a></div>" . $description . "</div></div>";

                          }
                        }
                        foreach ($manual_add_displayed_mods as $key => $value) {
                            $url = "https://www.nexusmods.com/valheim/mods/" . $value;
                            $fp = file_get_contents($url);
                            $res = preg_match("/<title>(.*)<\/title>/siU", $fp, $title_matches);
                            $res2 = preg_match("/<meta name=\"description\" content=\"(.*)\"/siU", $fp, $description_matches);
                            $res3 = preg_match("/<meta property=\"og:image\" content=\"(.*)\"/siU", $fp, $image_matches);
                            $title = preg_replace('/\s+/', ' ', $title_matches[1]);
                            $title = trim($title);
                            $title = str_replace("at Valheim Nexus - Mods and community", "", $title);
                            $description = preg_replace('/\s+/', ' ', $description_matches[1]);
                            $image = preg_replace('/\s+/', ' ', $image_matches[1]);

                          echo "<div class='col-md-4'><div class='thumbnail'><a target='_blank' href='" . $url . "'><img src='" . $image . "'><div class='caption'>" . $title .  "</a></div>" . $description . "</div></div>";
                        }
                      ?>
                    </div>
                    </div>
                  </div>
                </div>

        <?php
        };

        if (isset($_SESSION['login']) && $_SESSION['login'] == $hash) {
        // *************************************** //
        // ********** Logged In Content ********** //
        // *************************************** //
        ?>
      <div class="panel panel-primary">
        <div class="panel-heading" role="tab" id="headingTwo">
          <h4 class="panel-title">
            <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
              <?php echo $name; ?>
            </a>
          </h4>
        </div>
        <div id="collapseTwo" class="panel-collapse <?php echo $server_accordion;?>" role="tabpanel" aria-labelledby="headingTwo">
          <div class="panel-body">
            <label class="label label-info">Port</label> <?php echo $port; ?>
            <label class="label label-info">World</label> <?php echo $world; ?>
            <label class="label label-info">Public</label> <?php echo $public_status; ?><br><br>
            <button class="btn btn-danger server-function" onclick="location.href='index.php?stop=true';" <?php echo $public_attr;?>>Stop</button> 
            <button class="btn btn-success server-function" onclick="location.href='index.php?start=true';" <?php echo $start_attr;?>>Start</button> 
            <button class="btn btn-warning server-function" onclick="location.href='index.php?restart=true';" <?php echo $public_attr;?>>Restart</button> 
            <button class="btn btn-<?php echo $no_download_class;?>" <?php echo $no_download; ?> onclick="location.href='index.php?download_db=true';">Download DB</button> 
            <button class="btn btn-<?php echo $no_download_class;?>" onclick="location.href='index.php?download_fwl=true';" <?php echo $no_download; ?>>Download FWL</button> <a class="btn btn-primary" href="?logout=true">Logout</a>
          </div>
        </div>
      </div>

      <?php
      if ($mod_file_count > 0 && $cfg_editor == true) {
      ?>


      <div class="panel panel-primary">
        <div class="panel-heading" role="tab" id="headingThree">
          <h4 class="panel-title">
            <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
              Mod CFG Editor ( <?php echo $cfg_editor; ?>)
            </a>
          </h4>
        </div>
        <div id="collapseThree" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingThree">
          <div class="panel-body">
            <div class="col-md-9">
                <div class="float-left">
                  <div class="dropdown float-left">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="fileMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">File</button>
                    <div class="dropdown-menu" aria-labelledby="fileMenu">
                      <?php if (in_array('newfile', $permissions) || in_array('editfile', $permissions)) { ?>
                        <a class="dropdown-item save disabled" href="javascript:void(0);">Save <span class="float-right text-secondary">S</span></a>
                      <?php } ?>
                      <a class="dropdown-item close disabled" href="javascript:void(0);">Close <span class="float-right text-secondary">C</span></a>
                    </div>
                  </div>
                  <span id="path" class="btn float-left"></span>
                </div>
              </div>
            </div>

            <div class="row px-3">
              <div class="col-lg-3 col-md-3 col-sm-12 col-12">
                <div id="files" class="card">
                  <div class="card-block"><?= files(MAIN_DIR) ?></div>
                </div>
              </div>

              <div class="col-lg-9 col-md-9 col-sm-12 col-12">
                <div class="card">
                  <div class="card-block">
                    <div id="loading">
                      <div class="lds-ring">
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                      </div>
                    </div>
                    <textarea id="editor" data-file="" class="form-control"></textarea>
                    <input id="digest" type="hidden" readonly>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <form method="post" id="editor_form">
            <input name="action" type="hidden" value="upload-file">
            <input name="destination" type="hidden" value="">

            <div class="modal" id="uploadFileModal">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h4 class="modal-title">Upload File</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                  </div>
                  <div class="modal-body">
                    <div>
                      <input name="uploadfile[]" type="file" value="" multiple>
                    </div>
                    <?php

                    if (function_exists('ini_get')) {
                      $sizes = [
                        ini_get('post_max_size'),
                        ini_get('upload_max_filesize')
                      ];

                      $max_size = max($sizes);

                      echo '<small class="text-muted">Maximum file size: ' . $max_size . '</small>';
                    }

                    ?>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-dismiss="modal">Upload</button>
                  </div>
                </div>
              </div>
            </div>
          </form>
          </div>
        </div>
      </div>              
      <?php
      }; ?>

          <!-- close accordion -->
          </div>
          </div>
          </div>
        <!-- Close wrapper -->
        </div>
        </body>

        </html>
      </div>
      <?php
      }
  // ********** Login Form  ********** //
  else {
    echo '</div></div></div>';
    display_login_form();
  }

  function display_login_form() { ?>
    <form action="<?php echo $self; ?>" method='post'>
    <div class="row login">
          <div class="col-5"><input type="text" name="username" id="username" class="form-control"></div>
          <div class="col-5"><input type="password" name="password" id="password" class="form-control"></div>
          <div class="col-2"><input class="btn btn-success" type="submit" name="submit" value="submit"></div>
        </form>
    </div>
  <?php } ?>
  </body>
</html>
