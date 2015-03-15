<?php

add_filter('nav_menu_css_class', 'polytheme_menu_css_class', 10, 3);
function polytheme_menu_css_class($classes, $item, $args) {
  global $polytheme_menu_items;
  if (!isset($polytheme_menu_items)) $polytheme_menu_items = array();  
  
  // identify a slug for this item
  $slug = null;
  if (!empty($item->slug)) $slug = $item->slug;
  else {
    $post = get_post($item->object_id);
    if (!empty($post)) {
      if (!empty($post->post_name)) $slug = $post->post_name;
      else $slug = $post->ID;
    }
  }
  
  // prefix with the container ID
  if (!empty($args->container_id))
    $slug = $args->container_id.'-'.$slug;
  $slug = 'menu-item-'.$slug;
  $classes[] = $slug;
  
  // number the slugs to avoid duplication
  $num = 1;
  $id = $slug.'-'.$num;
  while (in_array($id, $polytheme_menu_items))
    $id = $slug.'-'.$num++;
  $polytheme_menu_items[] = $id;
  $classes[] = $id;
  return $classes;
}


add_filter('nav_menu_css_class', 'polytheme_ancestor_css_class', 10, 3);
function polytheme_ancestor_css_class($classes, $item, $args) {
  $p = polytheme_get_saved_post();
  if (!empty($p)) {
    $current_page_url = trim(get_permalink($p->ID), '/');
    if (!$current_page_url) {
      $http = "http://";
      $host = $_SERVER['HTTP_HOST'];
      $uri = $_SERVER['REQUEST_URI'];
      $current_page_url = trim("$http$host$uri", '/');
    }
  }

  $item_url = trim($item->url, '/');
  if (!$item_url)
    $item_url = trim(get_permalink($item->object_id), '/');
  if (substr($item_url, 0, 4) != "http")
    $item_url = trim(get_home_url(null, $item_url), '/');

  $home_url = trim(get_home_url(), '/');
  //do_action('log', 'Comparing home URL', $home_url, $item_url);
  if ($item_url == $home_url)
    return $classes;

  //do_action('log', 'Comparing current page ancestor', $item_url, $current_page_url);
  if (isset($current_page_url) && substr($current_page_url, 0, strlen($item_url)) == $item_url)
    $classes[] = "current-page-ancestor";
  return $classes;
}
