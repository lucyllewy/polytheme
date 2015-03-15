<?php

function polytheme_correct_ssl($text) {
  $base_url = site_url('/');
  $http_url = str_replace('https:', 'http:', $base_url);
  $https_url = str_replace('http:', 'https:', $base_url);

  if (is_ssl()) {
    $text = str_replace($http_url, $https_url, $text);
  } else {
    $text = str_replace($https_url, $http_url, $text);
  }
  return $text;
}

add_filter('the_content', 'polytheme_correct_ssl', 1);

add_filter('script_loader_src', 'polytheme_correct_ssl');
add_filter('style_loader_src', 'polytheme_correct_ssl');

add_filter('wp_get_nav_menu_items', 'polytheme_nav_menu_items_ssl', 10, 3);
function polytheme_nav_menu_items_ssl($items, $menu, $args) {
  $newitems = array();
  foreach ($items as $item) {
    if (is_object($item) && is_string($item->url)) {
      $item->url = polytheme_correct_ssl($item->url);
    }
    $newitems[] = $item;
  }
  return $newitems;
}

add_filter('post_thumbnail_html', 'polytheme_correct_ssl');

add_filter('widget_title', 'polytheme_correct_ssl');
add_filter('widget_text', 'polytheme_correct_ssl');