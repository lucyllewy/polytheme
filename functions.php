<?php
function glob_paths($folder, $load = false, $filter = null, $ext = ".php", $format = "file") {
  $paths = array();
  $pre = get_template_directory()."/$folder/";
  $cpre = get_stylesheet_directory()."/$folder/";

  if (FUNCTIONS_DEBUG)
    do_action('log', 'Globbing', "{$cpre}*{$ext}", "{$pre}*{$ext}");

  if (is_dir($cpre)) {
    foreach (glob("{$cpre}*{$ext}") as $filename) {
      $filename = substr($filename, strlen($cpre));
      $paths[] = $filename;
    }
  }
  if (is_dir($pre)) {
    foreach (glob("{$pre}*{$ext}") as $filename) {
      $filename = substr($filename, strlen($pre));
      if (!in_array($filename, $paths))
        $paths[] = $filename;
    }
  }

  $paths = array_unique($paths);
  if (is_callable($filter))
    $paths = array_filter($paths, $filter);

  $out = array();
  foreach ($paths as $path) {
    $filename = locate_template(array("$folder/$path"), $load, true);
    if (FUNCTIONS_DEBUG) do_action('log', 'Glob: %s = %s', "$folder/$path", $filename);
    switch($format) {
      case "path": $out[$path] = $path; break;
      case "url":
        preg_match("|(/wp-content/themes/.*)$|", $filename, $match);
        $out[$path] = site_url($match[1]);
        break;
      case "file": default: $out[$path] = $filename; break;
    }
  }
  return $out;
}

glob_paths("functions/common", true);
glob_paths("functions", true);

// Include everything in the 'partials/' folder (unless this is admin)
if (!is_admin() && trim($_SERVER['SCRIPT_NAME'], " /") != "wp-login.php") {
  glob_paths("partials", true);
}

// Include everything in the 'widgets/' folder and register widgets
add_action('widgets_init', 'polytheme_init_widgets', 20);
function polytheme_init_widgets () {
  try {
    $names = glob_paths("widgets", true);
    do_action('bang_widgets_init');
    foreach ($names as $name) {
      $name = substr($name, 0, strlen($name) - strlen(".php"));
      $name = substr($name, strrpos($name, "/")+1);
      if (class_exists($name))
        register_widget($name);
    }

    // Unregister everything in the 'conf/unregister-widgets.conf' file
    $unregister_widgets_conf = get_stylesheet_directory().'/conf/unregister-widgets.conf';
    if (file_exists($unregister_widgets_conf))
    $unregister_widgets = file($unregister_widgets_conf, FILE_IGNORE_NEW_LINES);
    if (isset($unregister_widgets) && is_array($unregister_widgets))
      foreach ($unregister_widgets as $name)
        unregister_widget($name);
  } catch (Exception $e) {
    do_action('log', 'Exception while loading widgets', $e);
  }
}

add_action('widgets_admin_page', 'polytheme_widgets_admin_init', 100);
function polytheme_widgets_admin_init() {
  global $wp_registered_sidebars;
  unset($wp_registered_sidebars['wp_inactive_widgets']);
}

//  load fonts
add_action('init', 'polytheme_load_fonts');
function polytheme_load_fonts() {
  if (is_admin()) return;

  // register available fonts
  global $polytheme_fonts;
  $polytheme_fonts = array();
  foreach (glob_paths("fonts", false, null, "", "url") as $filename) {
    $fontname = substr($filename, strrpos($filename, '/') + 1);
    $filename = "$filename/stylesheet.css";
    $polytheme_fonts[$fontname] = $filename;
  }
  $polytheme_fonts = apply_filters('polytheme_fonts', $polytheme_fonts);

  // request fonts from config file
  if (file_exists(get_stylesheet_directory().'/conf/fonts.conf')) {
    $fonts_conf = file(get_stylesheet_directory().'/conf/fonts.conf', FILE_IGNORE_NEW_LINES);
    if (!empty($fonts_conf)) {
      foreach ($fonts_conf as $fontname) {
        polytheme_enqueue_font($fontname);
      }
    }
  }
}

function polytheme_enqueue_font($fontname) {
  global $polytheme_fonts;
  if (isset($polytheme_fonts[$fontname])) {
    $filename = $polytheme_fonts[$fontname];
    wp_enqueue_style("font-$fontname", $filename, false, false, $media = 'all');
  }
}