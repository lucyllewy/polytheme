<?php

global $_polytheme_is_result, $_polytheme_result_linger;
$_polytheme_is_result = 0;
$_polytheme_result_linger = false;

function is_result() {
  global $_polytheme_is_result, $_polytheme_result_linger;
  //do_action('log', 'is_result', $_polytheme_is_result, $_polytheme_result_linger);
  return $_polytheme_is_result || $_polytheme_result_linger;
}

function the_result($result, $reset = true, $type = false) {
  global $post, $_polytheme_is_result, $_polytheme_result_linger;
  $base_post = $post;
  $post = $result;
  setup_postdata($post);
  $_polytheme_is_result++;
  
  if (!$type) $type = $result->post_type;
  $format = get_post_format();
  if ($format) $type = $format;
  get_template_part('results/result', $type);
  $_polytheme_is_result--;
  $_polytheme_result_linger = true;
  
  if ($reset) {
    $post = $base_post;
    setup_postdata($post);
    $_polytheme_result_linger = false;
  }
}

function the_results($results = null, $reset = true) {
  global $post, $_polytheme_is_result, $_polytheme_result_linger, $_polytheme_result_number;
  $base_post = $post;
  $_polytheme_result_number = 0;

  // if (is_null($results) && is_callable('polytheme_fs_instance') && is_faceted_search()) {
  //   $fs = polytheme_fs_instance();
  //   if (!empty($fs))
  //     $results = $fs->get_posts();
  // }
  
  if (is_null($results)) {
    while(have_posts()) {
      the_post();
      $_polytheme_is_result++;
      $_polytheme_result_number++;
      
      $type = $post->post_type;
      $format = get_post_format();
      if ($format) $type = $format;
      get_template_part('results/result', $type);
      $_polytheme_is_result--;
      $_polytheme_result_linger = true;
    }
  } else { 
    // do_action('log', 'DEBUG %s results', count($results), '!ID', $results);
    foreach ($results as $post) {
      setup_postdata($post);
      $_polytheme_is_result++;
      $_polytheme_result_number++;
      
      $type = $post->post_type;
      $format = get_post_format();
      if ($format) $type = $format;
      get_template_part('results/result', $type);
      $_polytheme_is_result--;
      $_polytheme_result_linger = false;
    }
  }
  
  if ($reset) {
    $post = $base_post;
    setup_postdata($post);
    $_polytheme_result_linger = false;
  }
}

//  write the results from a WP_Query with pagination
function the_query_results($query_args, $args = array()) {
  $args = wp_parse_args($args, array(
    'page_nav_before' => true,
    'page_nav_after' => true,
    'posts_per_page' => false,
    'reset' => true,
    'base' => '%_%',
    'prev_text' => 'Prev',
    'next_text' => 'Next',
    'fix_sticky' => false,
  ));

  $posts_per_page = (int) $args['posts_per_page'];
  if (!$posts_per_page) $posts_per_page = (int) get_option('posts_per_page');
  if (!$posts_per_page || $posts_per_page < 1) $posts_per_page = 10;
  $current_page = max(1, (int) $_REQUEST['page']);
  
  $query_args = wp_parse_args(array(
    'posts_per_page' => $posts_per_page,
    'paged' => $current_page,
  ), wp_parse_args($query_args, array(
    'post_status' => 'publish',
    'orderby' => 'post_date_gmt',
    'order' => DESC,
  )));

  $sticky_ids = get_option('sticky_posts');
  if ($args['fix_sticky'] && !$query_args['ignore_sticky_posts'] && !empty($sticky_ids)) {
    //do_action('log', 'Sticky post IDs', $sticky_ids);

    unset($query_args['paged']);
    unset($query_args['offset']);
    $query_args['posts_per_page'] = $posts_per_page * $current_page;
    $query_args['ignore_sticky_posts'] = true;

    $sticky_args = $query_args;
    $sticky_args['post__in'] = $sticky_ids;
    //do_action('log', 'Sticky args', $sticky_args);
    $sticky_query = new WP_Query($sticky_args);
    $sticky_posts = $sticky_query->get_posts();
    $sticky_total = $sticky_query->found_posts;
    
    $nonsticky_args = $query_args;
    $nonsticky_args['post__not_in'] = $sticky_ids;
    //do_action('log', 'Nonsticky args', $nonsticky_args);
    $nonsticky_query = new WP_Query($nonsticky_args);
    $nonsticky_posts = $nonsticky_query->get_posts();
    $nonsticky_total = $nonsticky_query->found_posts;
    
    $total = $sticky_total + $nonsticky_total;
    $pagesize = $args['posts_per_page'];
    if (empty($pagesize) || $pagesize == 0) $pagesize = 10;
    $total_pages = (int) ceil((float) $total / (float) $pagesize);
    //do_action('log', 'Total posts: %s sticky + %s nonsticky = %s total (%s pages)', $sticky_total, $nonsticky_total, $total, $total_pages);
    $posts = array_values(array_merge($sticky_posts, $nonsticky_posts));
    
    $offset = $args['posts_per_page'] * ($current_page - 1);
    $posts = array_slice($posts, $offset, $posts_per_page);
  } else {
    $query = new WP_Query($query_args);
    $posts = $query->get_posts();
    $total_pages = $query->max_num_pages;
  }
  
  
  if ($total_pages > 1 && ($args['page_nav_before'] || $args['page_nav_after'])) {
    $format = strpos($args['base'], '?') ? '&page=%#%' : '?page=%#%';
    do_action('log', 'Pagination base', $args['base']);
    $page_nav = "<p class='page_nav'>".paginate_links(array(
      'base' => $args['base'],
      'format' => $format,
      'current' => $current_page,
      'total' => $total_pages,
      'prev_text' => $args['prev_text'],
      'next_text' => $args['next_text'],
    ))."</p>";
  }

  if ($args['page_nav_before']) echo $page_nav;
  the_results($posts, $args['reset']);
  if ($args['page_nav_after']) echo $page_nav;
}
