<?php

//add_action('wp_head', 'polytheme_scripts');
add_action('admin_enqueue_scripts', 'polytheme_admin_scripts');
function polytheme_admin_scripts() {
  glob_conditional_scripts('ie8', 'lt IE 9');
  glob_conditional_scripts('ie7', 'lt IE 8');
  glob_conditional_scripts('ie6', 'lt IE 7');
}

add_action('wp_enqueue_scripts', 'polytheme_scripts', 9);
function polytheme_scripts() {
  // parse the dependencies
  $depconf = file_get_lines('conf/scripts.conf', true);
  $dependencies = array();
  foreach($depconf as $line) {
    if (empty($line)) continue;
    list($code, $deps) = array_map('trim', explode(':', $line));
    $deps = array_map('trim', explode(',', $deps));
    $dependencies[$code] = $deps;
  }
  if (!isset($dependencies['all'])) 
    $dependencies['all'] = array();
  //do_action('log', 'Script dependencies', $dependencies);

  glob_conditional_scripts('ie8', 'lt IE 9');
  glob_conditional_scripts('ie7', 'lt IE 8');
  glob_conditional_scripts('ie6', 'lt IE 7');

  $theme_scripts = glob_paths("scripts", false, null, ".js", "url");
  if (is_admin())
    $theme_scripts = array_diff($theme_scripts, array('jquery'));

  // adjust the dependencies of pre-registered scripts
  global $wp_scripts;
  foreach ($wp_scripts->registered as &$script) {
    if (in_array($script->handle, $theme_scripts)) continue;
    if (isset($dependencies[$script->handle]))
      $script->deps = array_merge($script->deps, $dependencies[$script->handle]);
  }
  //do_action('log', 'Resulting scripts', '!handle,deps', $wp_scripts->registered);

  // register any theme scripts
  foreach($theme_scripts as $path => $url) {
    $matches = array();
    preg_match("!([^/]+?)(-[0-9.]+)?([._-]min(ified)?)?.js$!", $path, $matches);
    $code = $matches[1];

    $depends = isset($dependencies[$code]) ? $dependencies[$code] : $dependencies['all'];
    $depends = array_values(array_diff($depends, array($code)));
    //$depends = $code == 'jquery' ? array() : array('jquery');
    $depends = apply_filters('polytheme_script_depends', $depends, $code);
    $depends = array_unique($depends);

    // do_action('log', 'Enqueue script', $code, $url, $depends);
    if ($code == 'jquery') wp_deregister_script($code);
    wp_register_script($code, $url, $depends, NULL, true);
    wp_enqueue_script($code);
  }
}

function glob_conditional_scripts($folder, $condition) {
  $scripts = glob_paths("scripts/$folder", false, null, ".js", "url");
  // do_action('log', 'Conditional %s scripts', $folder, $scripts);
  if (!empty($scripts)) {
    echo "<!--[if $condition]>\n";
    foreach ($scripts as $path => $url)
      echo "  <script type='text/javascript' src='$url'></script>\n";
    echo "<![endif]-->\n";
  }
}


add_filter( 'script_loader_src', 'polytheme_remove_script_version', 100, 1 );
add_filter( 'style_loader_src', 'polytheme_remove_script_version', 100, 1 );

function polytheme_remove_script_version($src) {
  return add_query_arg(array('ver' => false), $src);
}