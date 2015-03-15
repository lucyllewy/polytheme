<?php

add_action('admin_init', 'polytheme_plugin_check');

function polytheme_plugin_check() {
  if ( ! current_user_can( 'install_plugins' ) ) return;
  
  $required = file_get_lines('conf/plugins.conf');  
  $plugins = get_plugins();
  //do_action('log', 'Plugins', $plugins);
  
  global $polytheme__missing_plugins;
  $polytheme__missing_plugins = array();
  
  foreach ($required as $code) {
    foreach ($plugins as $file => $plugin) {
      if (substr($file, 0, strlen($code)) == $code) {
        if (!is_plugin_active($file)) {
          $polytheme__missing_plugins[$file] = $plugin;
        }
        break;
      }
    }
    $polytheme__missing_plugins[$code] = null;
  }

  if (!empty($polytheme__missing_plugins)) {
    add_thickbox();
    add_action('admin_notices', 'polytheme_plugin_notice');
  }
}

function polytheme_plugin_notice() {
  global $polytheme__missing_plugins;

  foreach ($polytheme__missing_plugins as $file => $plugin) {
    if (empty($plugin)) {
      /*
      ?><div class='updated fade'><p>The plugin <b><?php echo $file; ?></b>
        is required for this theme to function correctly.
        <a href='<?php
        echo admin_url('plugin-install.php?tab=plugin-information&plugin='.$file.'&TB_iframe=true&width=640&height=517');
        ?>' class="thickbox onclick">Install now</a>.</p>
      </div><?php
      */
    } else {
      $nonce= wp_create_nonce('activate-plugin_'.$file);
      ?><div class='updated error'>
        <p>The plugin <b><?php echo $plugin['Name']; ?></b>
        is required for this theme to function correctly. &nbsp; 
        <a class='button' href='<?php
        echo admin_url('plugins.php?action=activate&plugin='.$file.'&plugin_status=all&_wpnonce='.$nonce);
        ?>'>Activate now</a></p>
      </div><?php      
    }
  }
}
