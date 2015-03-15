<?php

if (!defined('LAYOUT_DEBUG'))
  define('LAYOUT_DEBUG', false);

function layout($layout, $main_part = false, $removed = false, $sidebar = false, $correct_preview = true) {
  if (DUBLIN_CORE) $dublin_core = true;
  global $polytheme__main_part, $polytheme_sidebar, $polytheme__correct_preview, $post;
  if ($main_part) $polytheme__main_part = $main_part;
  if ($sidebar) $polytheme_sidebar = $sidebar;
  $polytheme__correct_preview = $correct_preview;

  if (LAYOUT_DEBUG) do_action('log', 'Layout post %s: layout = %s, main = %s', $post->ID, $layout, $main_part);
  if (isset($post->post_type) && $post->post_type == 'revision') {
    do_action('log', 'Swapping out post data: %s -> %s', $post->ID, $post->post_parent);
    $parent = get_post($post->post_parent);
    if (!empty($parent)) {
      $parent->post_content = $post->post_content;
      $post = $parent;
    }
  }
  setup_postdata($post);
  polytheme_add_body_class('layout-'.$layout);
  polytheme_add_body_data('layout', $layout);
  if (!empty($main_part))
    polytheme_add_body_data('main', $main_part);
  else
    polytheme_add_body_data('main', 'main');
  if (!empty($sidebar))
    polytheme_add_body_data('sidebar', $sidebar);
  get_template_part('layouts/layout', $layout);
}

function polytheme_sidebar($default = null) {
  global $polytheme_sidebar;
  if (!empty($polytheme_sidebar))
    return $polytheme_sidebar;
  return $default;
}

function main($part = false, $removed = false) {
  global $post, $meta, $polytheme__main_part, $polytheme__correct_preview;
  if (!$part) $part = $polytheme__main_part;

  if (LAYOUT_DEBUG) do_action('log', 'Main post %s', $post->ID, $part);
  echo '<div id="content" class="content" role="main">';
  setup_postdata($post);
  if ($polytheme__correct_preview) polytheme_correct_preview();
  get_template_part('main/main', $part);
  if ($polytheme__correct_preview) polytheme_restore_post();
  echo '<div class="clear empty"></div>';
  echo '</div>';
}


function polytheme_save_post() {
  global $post, $_polytheme_saved_post;
  if (!is_search()) {
    //do_action('log', 'Save post', $post->ID);
    //polytheme_correct_preview();
    $_polytheme_saved_post = $post;
  }
}

function polytheme_restore_post() {
  global $post, $_polytheme_saved_post;
  $post = apply_filters('polytheme_restore_post', $_polytheme_saved_post);
  if ($post) {
    //do_action('log', 'Restore post', $post->ID);
    setup_postdata($post);
  }
}

function polytheme_get_saved_post() {
  global $post, $_polytheme_saved_post;
  if (empty($_polytheme_saved_post))
    return $post;
  return $_polytheme_saved_post;
}

function polytheme_correct_preview() {
  if (is_search()) return;
  if (!isset($_REQUEST['preview']) || $_REQUEST['preview'] != "true") return;

  $preview_id = (int) $_REQUEST['preview_id'];
  $preview_nonce = $_REQUEST['preview_none'];

  $previews = get_posts(array(
    'post_parent' => $preview_id,
    'post_type' => 'revision',
    'post_status' => 'inherit',
    'orderby' => 'modified',
    'order' => DESC,
    'posts_per_page' => 1,
  ));
  if (empty($previews)) return;

  $preview = $previews[0];
  //do_action('log', 'PREVIEW', $preview);

  global $post, $_polytheme_saved_preview;
  $_polytheme_saved_preview = $preview;
  $post->post_content = $preview->post_content;
  $post->post_title = $preview->post_title;
  $post->post_author = $preview->post_author;
  setup_postdata($post);
}

function polytheme_disengage_preview() {
  global $post, $_polytheme_saved_post;
  if (!empty($_polytheme_saved_post)) {
    $post = $_polytheme_saved_post;
    setup_postdata($post);
  }
}

function polytheme_restore_preview() {
  global $post, $_polytheme_saved_preview;
  if (!empty($_polytheme_saved_preview)) {
    $post = $_polytheme_saved_preview;
    setup_postdata($post);
  }
}
