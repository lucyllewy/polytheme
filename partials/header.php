<?php

function polytheme_set_title($title) {
  global $polytheme_page_title;
  if ($title) $polytheme_page_title = $title;
}

function polytheme_get_title() {
  global $polytheme_page_title;
  if ($polytheme_page_title) $title = __($polytheme_page_title);
  else if (is_home() || is_front_page()) $title = __(get_bloginfo('name'));
  else if (is_single() || is_page()) $title = __(single_post_title('', false));
  else if (is_search()) {
    $s = $_GET['s'];
    if (empty($s)) $title = __('Search results');
    else $title = sprintf(__('Search results for %s'), wp_specialchars($s));
  } else if (is_404()) $title = __("Not found");
  else $title = wp_title('', false);

  return apply_filters('polytheme_page_title', $title);
}