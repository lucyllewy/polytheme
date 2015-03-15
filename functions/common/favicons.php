<?php

function polytheme_write_favicon_sizes($html_rel, $file_basename, $image_sizes = array('')) {
  foreach ($image_sizes as $size) {
    $attribute = '';
    if (!empty($size)) {
      $size = "${size}x${size}";
      $attribute = "sizes='".esc_attr($size)."'";
      $append = "-$size";
    } else {
      $append = '';
    }

    $icon = get_theme_image("$file_basename$append.png");
    $icon = apply_filters("$file_basename$append", $icon);
    if (!empty($icon))
      echo "<link rel='".esc_attr($html_rel)."' $attribute href='".esc_attr($icon)."' />\n";
  }
}

function polytheme_write_favicon() {
  $subfolder = apply_filters('favicon-subfolder', '');
  if (!empty($subfolder) && !preg_match('|/$|', $subfolder)) $subfolder .= '/';

  $favicon = get_theme_image("${subfolder}favicon.png");
  if (empty($favicon))
    $favicon = get_theme_image("${subfolder}favicon.ico");
  $favicon = apply_filters('favicon', $favicon);
  if (!empty($favicon))
    echo "<link rel='shortcut icon' href='".esc_attr($favicon)."' />\n";

  polytheme_write_favicon_sizes('apple-touch-icon-precomposed', "${subfolder}apple-touch-icon-precomposed");
  polytheme_write_favicon_sizes('apple-touch-icon', "${subfolder}apple-touch-icon", array('57', '60', '72', '76', '114', '120', '144', '152'));
  polytheme_write_favicon_sizes('icon', "${subfolder}favicon", array('16', '32', '96', '160', '196'));

  $ms = get_theme_image("${subfolder}ms-tile-icon.png");
  $ms = apply_filters('ms-tile-icon', $ms);
  if (!empty($ms))
    echo "<meta name='msapplication-TileImage' content='".esc_attr($ms)."' />\n";

  $bc = get_theme_image("${subfolder}browserconfig.xml");
  $bc = apply_filters('browserconfig.xml', $bc);
  if (!empty($bc))
    echo "<meta name='msapplication-config' content='".esc_attr($bc)."' />\n";

  global $polytheme_ms_tile_colour;
  if (empty($polytheme_ms_tile_colour)) $polytheme_ms_tile_colour = '#fede00';
  echo "<meta name='msapplication-TileColor' content='$polytheme_ms_tile_colour' />\n";
}